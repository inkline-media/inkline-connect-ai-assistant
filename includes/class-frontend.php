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

class ICAIA_Frontend {

	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue' ) );
		add_action( 'wp_footer', array( $this, 'render_dock' ), 20 );
		add_action( 'wp_footer', array( $this, 'render_chat_embed' ), 21 );
		add_shortcode( 'inkline_ai_assistant', array( $this, 'shortcode' ) );
	}

	/**
	 * Whether the plugin should render anything on the front end.
	 */
	public function is_active() {
		$embed = (string) ICAIA_Settings::get( 'chat_embed', '' );
		return '' !== trim( $embed );
	}

	public function enqueue() {
		if ( ! $this->is_active() ) {
			return;
		}
		$s = ICAIA_Settings::all();

		if ( ! empty( $s['load_inter'] ) ) {
			wp_enqueue_style(
				'icaia-inter',
				'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap',
				array(),
				null
			);
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

		wp_localize_script(
			'icaia-frontend',
			'ICAIA',
			array(
				'brand'       => $s['brand_color'] ? $s['brand_color'] : '#0057B8',
				'fontStack'   => $s['font_stack'],
				'suggestions' => array_values( $suggestions ),
				'dock'        => ! empty( $s['dock_enabled'] ),
			)
		);

		// Brand color + font stack flow into both the page styles and
		// the (shadow-DOM) chat widget through CSS custom properties.
		$brand = $this->safe_color( (string) $s['brand_color'] );
		$font  = $s['font_stack'];
		$css   = sprintf(
			':root{--iaa-brand:%1$s;--iaa-brand-hover:%1$s;--iaa-font:%2$s;}',
			esc_attr( $brand ),
			esc_attr( $font )
		);
		wp_add_inline_style( 'icaia-frontend', $css );
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
