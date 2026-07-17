<?php
/**
 * GitHub release updater.
 *
 * Uses the WordPress 5.8+ Update URI integration so that this plugin
 * never appears in wp.org update checks: the plugin header's
 * `Update URI: https://github.com/…` value triggers the
 * `update_plugins_github.com` filter, which we hook here to surface
 * release metadata from the GitHub API.
 *
 * As a belt-and-suspenders, we also strip the plugin from the body of
 * the outbound wp.org update-check request, so it cannot accidentally
 * collide with a wp.org plugin of the same slug.
 *
 * @package Inkline_Connect_AI_Assistant
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'ICAIA_Updater' ) ) :

class ICAIA_Updater {

	/** @var string Absolute path to the main plugin file. */
	private $file;

	/** @var string Plugin basename, e.g. plugin-dir/plugin.php */
	private $basename;

	/** @var string Plugin slug (directory name). */
	private $slug;

	/** @var string GitHub repo, e.g. "inkline-media/inkline-connect-ai-assistant". */
	private $repo;

	/** @var string Transient key for the cached release payload. */
	private $cache_key;

	public function __construct( $file, $repo ) {
		$this->file      = $file;
		$this->basename  = plugin_basename( $file );
		$this->slug      = dirname( $this->basename );
		$this->repo      = $repo;
		$this->cache_key = 'icaia_release_' . md5( $repo );

		// WordPress 5.8+ Update URI: WP calls this filter for plugins
		// whose Update URI host matches "github.com".
		add_filter( 'update_plugins_github.com', array( $this, 'check_for_update' ), 10, 4 );

		// Strip this plugin from any outbound wp.org update-check
		// request so we never get a wp.org response for it.
		add_filter( 'http_request_args', array( $this, 'strip_from_wporg_check' ), 10, 2 );

		// "View details" popup in the Plugins list.
		add_filter( 'plugins_api', array( $this, 'plugins_api' ), 10, 3 );

		// Allow a clean reinstall after the package is extracted —
		// GitHub zips unpack to a hashed folder name, which we rename
		// to the plugin slug so WP recognises the in-place update.
		add_filter( 'upgrader_source_selection', array( $this, 'rename_github_folder' ), 10, 4 );
	}

	/**
	 * Filter target for the Update URI integration.
	 *
	 * @param array|false $update      Existing update info (false if none).
	 * @param array       $plugin_data Plugin headers (Name, Version, ...).
	 * @param string      $plugin_file Plugin basename WP is checking.
	 * @param string[]    $locales     Active locales (unused).
	 *
	 * @return array|false Update array if a newer release exists.
	 */
	public function check_for_update( $update, $plugin_data, $plugin_file, $locales ) {
		if ( $plugin_file !== $this->basename ) {
			return $update;
		}

		// "Check Again" on Dashboard → Updates loads update-core.php
		// with ?force-check=1. When that flag is set, drop our own
		// release cache so the click actually hits GitHub instead of
		// returning a stale entry from the 6h transient.
		if ( $this->is_force_check() ) {
			delete_site_transient( $this->cache_key );
		}

		$release = $this->get_release();
		if ( ! $release ) {
			return $update;
		}
		$built = $this->build_update_object( $plugin_data, $release );
		return $built ? $built : $update;
	}

	/**
	 * Translate a GitHub release payload into the array shape WP wants.
	 * Returns false if the latest release isn't newer than the installed version.
	 */
	private function build_update_object( $plugin_data, $release ) {
		$latest  = isset( $release['tag_name'] ) ? ltrim( $release['tag_name'], 'v' ) : '';
		$current = isset( $plugin_data['Version'] ) ? $plugin_data['Version'] : ICAIA_VERSION;
		if ( '' === $latest || version_compare( $latest, $current, '<=' ) ) {
			return false;
		}

		// Prefer a release asset matching the slug, else fall back to
		// the zipball — both work for in-place upgrades.
		$package = isset( $release['zipball_url'] ) ? $release['zipball_url'] : '';
		if ( ! empty( $release['assets'] ) && is_array( $release['assets'] ) ) {
			foreach ( $release['assets'] as $asset ) {
				if ( ! empty( $asset['browser_download_url'] ) && false !== stripos( (string) $asset['name'], '.zip' ) ) {
					$package = $asset['browser_download_url'];
					break;
				}
			}
		}

		return array(
			'slug'         => $this->slug,
			'plugin'       => $this->basename,
			'new_version'  => $latest,
			'url'          => isset( $release['html_url'] ) ? $release['html_url'] : "https://github.com/{$this->repo}",
			'package'      => $package,
			'icons'        => array(),
			'banners'      => array(),
			'tested'       => get_bloginfo( 'version' ),
			'requires_php' => '7.4',
		);
	}

	/**
	 * Detect a user-initiated "force" update check.
	 *
	 * The "Check Again" button on Dashboard → Updates redirects to
	 * update-core.php with ?force-check=1, which core also uses to
	 * shorten its own update-check timeout. We re-use the same signal
	 * to invalidate the cached release payload.
	 */
	private function is_force_check() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return ! empty( $_GET['force-check'] );
	}

	/**
	 * Fetch the latest release from GitHub, caching success for 6h and
	 * failures for 1h so a transient API outage doesn't slow every load.
	 */
	private function get_release() {
		$cached = get_site_transient( $this->cache_key );
		if ( is_array( $cached ) && empty( $cached['error'] ) ) {
			return $cached;
		}

		$response = wp_remote_get(
			"https://api.github.com/repos/{$this->repo}/releases/latest",
			array(
				'timeout' => 10,
				'headers' => array(
					'Accept'     => 'application/vnd.github+json',
					'User-Agent' => 'WordPress/' . get_bloginfo( 'version' ),
				),
			)
		);

		if ( is_wp_error( $response ) || 200 !== (int) wp_remote_retrieve_response_code( $response ) ) {
			set_site_transient( $this->cache_key, array( 'error' => true ), HOUR_IN_SECONDS );
			return null;
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( ! is_array( $body ) || empty( $body['tag_name'] ) ) {
			set_site_transient( $this->cache_key, array( 'error' => true ), HOUR_IN_SECONDS );
			return null;
		}

		set_site_transient( $this->cache_key, $body, 6 * HOUR_IN_SECONDS );
		return $body;
	}

	/**
	 * Remove this plugin from outbound wp.org update-check payloads, so
	 * wp.org never gets the chance to claim it.
	 */
	public function strip_from_wporg_check( $args, $url ) {
		if ( false === strpos( (string) $url, '//api.wordpress.org/plugins/update-check' ) ) {
			return $args;
		}
		if ( empty( $args['body']['plugins'] ) ) {
			return $args;
		}
		$plugins = json_decode( (string) $args['body']['plugins'], true );
		if ( ! is_array( $plugins ) ) {
			return $args;
		}
		if ( isset( $plugins['plugins'][ $this->basename ] ) ) {
			unset( $plugins['plugins'][ $this->basename ] );
		}
		if ( isset( $plugins['active'] ) && is_array( $plugins['active'] ) ) {
			$plugins['active'] = array_values( array_diff( $plugins['active'], array( $this->basename ) ) );
		}
		$args['body']['plugins'] = wp_json_encode( $plugins );
		return $args;
	}

	/**
	 * "View version details" popup in the Plugins list.
	 */
	public function plugins_api( $result, $action, $args ) {
		if ( 'plugin_information' !== $action ) {
			return $result;
		}
		if ( empty( $args->slug ) || $args->slug !== $this->slug ) {
			return $result;
		}
		if ( $this->is_force_check() ) {
			delete_site_transient( $this->cache_key );
		}
		$release = $this->get_release();
		if ( ! $release ) {
			return $result;
		}
		$info                 = new stdClass();
		$info->name           = 'AI Website Assistant for Inkline Connect';
		$info->slug           = $this->slug;
		$info->version        = ltrim( $release['tag_name'], 'v' );
		$info->author         = '<a href="https://inkline.ca">Inkline Media</a>';
		$info->homepage       = "https://github.com/{$this->repo}";
		$info->download_link  = isset( $release['zipball_url'] ) ? $release['zipball_url'] : '';
		$info->requires       = '5.8';
		$info->requires_php   = '7.4';
		$info->last_updated   = isset( $release['published_at'] ) ? $release['published_at'] : '';
		$info->sections       = array(
			'description' => 'Drops an Inkline Connect–powered AI assistant into your WordPress site: a docked bar, an in-page widget (shortcode + Elementor), and matching styling for the connected chat widget.',
			'changelog'   => isset( $release['body'] ) ? wpautop( esc_html( $release['body'] ) ) : 'See <a href="https://github.com/' . esc_html( $this->repo ) . '/releases">releases on GitHub</a>.',
		);
		return $info;
	}

	/**
	 * GitHub zips extract to a hashed folder name (e.g.
	 * "inkline-media-inkline-connect-…-abc1234"). WP needs the folder to
	 * match the installed plugin slug for the in-place upgrade to take.
	 */
	public function rename_github_folder( $source, $remote_source, $upgrader, $hook_extra = array() ) {
		if ( ! is_string( $source ) ) {
			return $source;
		}
		// Only act on this plugin's own upgrade. Fresh installs of other
		// plugins/themes fire this filter with no 'plugin' key, so a
		// missing key must mean "not ours" — never rename those.
		$plugin = isset( $hook_extra['plugin'] ) ? $hook_extra['plugin'] : '';
		if ( $plugin !== $this->basename ) {
			return $source;
		}
		$basename = trailingslashit( $source );
		$desired  = trailingslashit( dirname( $source ) ) . $this->slug;
		if ( $basename === trailingslashit( $desired ) ) {
			return $source;
		}
		global $wp_filesystem;
		if ( $wp_filesystem && $wp_filesystem->move( $source, $desired, true ) ) {
			return trailingslashit( $desired );
		}
		return $source;
	}
}

endif;

