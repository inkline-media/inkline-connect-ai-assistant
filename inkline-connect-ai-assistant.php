<?php
/**
 * Plugin Name:       AI Website Assistant for Inkline Connect
 * Plugin URI:        https://github.com/inkline-media/inkline-connect-ai-assistant
 * Description:       Drops an Inkline Connect-powered AI assistant into your site: an in-page widget (shortcode + Elementor), a site-wide docked bar, and matching styling for the connected chat widget. Activates once you paste your Inkline Connect chat-widget embed code in the settings.
 * Version:           0.1.7
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Author:            Inkline Media
 * Author URI:        https://inkline.ca
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       inkline-connect-ai-assistant
 * Domain Path:       /languages
 * Update URI:        https://github.com/inkline-media/inkline-connect-ai-assistant
 *
 * @package Inkline_Connect_AI_Assistant
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'ICAIA_VERSION', '0.1.7' );
define( 'ICAIA_FILE', __FILE__ );
define( 'ICAIA_DIR', plugin_dir_path( __FILE__ ) );
define( 'ICAIA_URL', plugin_dir_url( __FILE__ ) );
define( 'ICAIA_OPTION', 'icaia_settings' );
define( 'ICAIA_REPO', 'inkline-media/inkline-connect-ai-assistant' );

require_once ICAIA_DIR . 'includes/class-settings.php';
require_once ICAIA_DIR . 'includes/class-frontend.php';
require_once ICAIA_DIR . 'includes/class-updater.php';

/**
 * Boot the plugin.
 */
add_action(
	'plugins_loaded',
	static function () {
		load_plugin_textdomain( 'inkline-connect-ai-assistant', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
		new ICAIA_Settings();
		new ICAIA_Frontend();
		new ICAIA_Updater( __FILE__, ICAIA_REPO );
	}
);

/**
 * Register the Elementor widget — only when Elementor is loaded. The
 * Elementor team renamed the registration action in v3.5; this targets
 * the modern one and silently no-ops on older versions.
 */
add_action(
	'elementor/widgets/register',
	static function ( $widgets_manager ) {
		require_once ICAIA_DIR . 'includes/class-elementor-widget.php';
		if ( class_exists( 'ICAIA_Elementor_Widget' ) ) {
			$widgets_manager->register( new ICAIA_Elementor_Widget() );
		}
	}
);
