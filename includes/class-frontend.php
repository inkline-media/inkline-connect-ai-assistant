<?php
/**
 * Frontend: asset enqueue, shortcode, dock render, chat-widget embed.
 *
 * Nothing on the front end activates until an admin pastes the
 * Inkline Connect chat-widget embed code into the settings page.
 *
 * @package Inkline_Connect_AI_Assistant
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'ICAIA_Frontend' ) ) :

class ICAIA_Frontend {

	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue' ) );
		add_action( 'wp_footer', array( $this, 'render_dock' ), 20 );
		add_action( 'wp_footer', array( $this, 'render_chat_embed' ), 21 );
		add_shortcode( 'inkline_ai_assistant', array( $this, 'shortcode' ) );
	}

	/**
	 * Whether the plugin should render anything on the front end.
	 * Requires both the master toggle and the chat-widget embed.
	 */
	public function is_active() {
		if ( ! ICAIA_Settings::get( 'enabled', 1 ) ) {
			return false;
		}
		$embed = (string) ICAIA_Settings::get( 'chat_embed', '' );
		return '' !== trim( $embed );
	}

	public function enqueue() {
		if ( ! $this->is_active() ) {
			return;
		}
		$s = ICAIA_Settings::all();

		$font = ICAIA_Settings::resolve_font();
		if ( '' !== $font['gfont_url'] ) {
			wp_enqueue_style( 'icaia-gfont', $font['gfont_url'], array(), null );
		}

		wp_enqueue_style(
			'icaia-frontend',
			ICAIA_URL . 'assets/css/frontend.css',
			array(),
			ICAIA_VERSION
		);

		wp_enqueue_script(
			'icaia-frontend',
			ICAIA_URL . 'assets/js/frontend.js',
			array(),
			ICAIA_VERSION,
			true
		);

		$suggestions = $this->parse_suggestions( (string) $s['suggestions'] );

		$brand_raw = trim( (string) $s['brand_color'] );
		$brand     = $brand_raw ? $this->safe_color( $brand_raw ) : '';

		$chat_header_raw   = trim( (string) ( $s['chat_header_color'] ?? '' ) );
		$chat_header       = $chat_header_raw ? sanitize_hex_color( $chat_header_raw ) : '';
		$chat_received_raw = trim( (string) ( $s['chat_received_color'] ?? '' ) );
		$chat_received     = $chat_received_raw ? sanitize_hex_color( $chat_received_raw ) : '';

		wp_localize_script(
			'icaia-frontend',
			'ICAIA',
			array(
				// Empty string signals JS not to push brand tokens into
				// the chat widget's shadow DOM, so the widget keeps the
				// colors configured in Inkline Connect.
				'brand'         => $brand,
				// Optional chat-widget surface overrides. Empty = use the
				// cream default whenever any other token gets pushed.
				'chatHeader'    => $chat_header ? (string) $chat_header : '',
				'chatReceived'  => $chat_received ? (string) $chat_received : '',
				'fontStack'     => $font['family'],
				'suggestions'   => array_values( $suggestions ),
				'dock'          => ! empty( $s['dock_enabled'] ),
			)
		);

		// Always push the font stack. Only push the brand color when
		// one is set, so it can override the CSS-default; otherwise the
		// CSS default kicks in for our own components and the chat
		// widget keeps its Inkline Connect-configured colors. The
		// variables are scoped to our component roots so they never
		// leak into the host site's CSS.
		$inline = sprintf(
			'.iaa-assist,.iaa-dock{--iaa-font:%s;}',
			esc_attr( $font['family'] )
		);
		if ( '' !== $brand ) {
			$inline .= sprintf(
				'.iaa-assist,.iaa-dock{--iaa-brand:%1$s;--iaa-brand-hover:%1$s;}',
				esc_attr( $brand )
			);
		}
		wp_add_inline_style( 'icaia-frontend', $inline );
	}

	/**
	 * Parse the textarea-saved suggestions into a clean array.
	 */
	private function parse_suggestions( $raw ) {
		$lines = preg_split( "/\r\n|\r|\n/", $raw );
		if ( ! is_array( $lines ) ) {
			return array();
		}
		$out = array();
		foreach ( $lines as $line ) {
			$line = trim( $line );
			if ( '' !== $line ) {
				$out[] = $line;
			}
		}
		return $out;
	}

	private function safe_color( $hex ) {
		$hex = sanitize_hex_color( $hex );
		return $hex ? $hex : '#0057B8';
	}

	/**
	 * [inkline_ai_assistant] — drops the in-page widget anywhere.
	 */
	public function shortcode( $atts ) {
		if ( ! $this->is_active() ) {
			return '';
		}
		$a = shortcode_atts(
			array(
				'eyebrow'  => __( 'Ask the assistant', 'inkline-connect-ai-assistant' ),
				'heading'  => __( 'Tell us what you’re trying to solve.', 'inkline-connect-ai-assistant' ),
				'sub'      => __( 'Describe your goal in plain language and the assistant points you to the right place.', 'inkline-connect-ai-assistant' ),
				'variant'  => 'card',
				'align'    => 'left',
			),
			$atts,
			'inkline_ai_assistant'
		);

		$a['variant'] = in_array( $a['variant'], array( 'card', 'bare' ), true ) ? $a['variant'] : 'card';
		$a['align']   = in_array( $a['align'], array( 'left', 'center' ), true ) ? $a['align'] : 'left';

		ob_start();
		$icaia_attrs = $a;
		include ICAIA_DIR . 'templates/inline-widget.php';
		return ob_get_clean();
	}

	public function render_dock() {
		if ( ! $this->is_active() ) {
			return;
		}
		if ( ! ICAIA_Settings::get( 'dock_enabled', 1 ) ) {
			return;
		}
		include ICAIA_DIR . 'templates/dock.php';
	}

	/**
	 * Inject the admin-pasted chat-widget embed code into the footer.
	 *
	 * The content is admin-trusted (only `manage_options` users can
	 * save it). It's intentionally output raw so the embedded loader
	 * script and custom <chat-widget> element work as-is.
	 */
	public function render_chat_embed() {
		if ( ! $this->is_active() ) {
			return;
		}
		echo "\n<!-- Inkline Connect chat widget (via AI Website Assistant plugin) -->\n";
		echo ICAIA_Settings::get( 'chat_embed', '' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo "\n";
	}
}

endif;

