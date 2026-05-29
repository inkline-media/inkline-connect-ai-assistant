<?php
/**
 * Settings page for the AI Website Assistant.
 *
 * Lives under Settings → AI Website Assistant. Stores everything in a
 * single `icaia_settings` option, so frontend + updater code reads from
 * one place via ICAIA_Settings::get().
 *
 * @package Inkline_Connect_AI_Assistant
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ICAIA_Settings {

	const SLUG = 'inkline-connect-ai-assistant';

	public function __construct() {
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_menu', array( $this, 'register_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
	}

	/**
	 * Read a single setting with a default.
	 */
	public static function get( $key, $default = null ) {
		$all = wp_parse_args( get_option( ICAIA_OPTION, array() ), self::defaults() );
		return isset( $all[ $key ] ) ? $all[ $key ] : $default;
	}

	/**
	 * Read all settings, merged with defaults.
	 */
	public static function all() {
		return wp_parse_args( get_option( ICAIA_OPTION, array() ), self::defaults() );
	}

	/**
	 * Shipped defaults. Brand color and Inter typography reflect Inkline's
	 * own brand — admins can override either in the settings page.
	 */
	public static function defaults() {
		return array(
			'enabled'      => 1,
			'chat_embed'   => '',
			'brand_color'  => '',
			'font_stack'   => "'Inter', 'Helvetica Neue', Helvetica, Arial, sans-serif",
			'load_inter'   => 1,
			'dock_enabled' => 1,
			'suggestions' => "How do I cut our martech costs?\nHow do I get our team AI-ready?\nWhat does an AI readiness assessment cover?\nCan you audit my Marketo instance?\nWhich service fits a product launch?\nHow do I prove marketing ROI to the board?\nI need to rebuild our revenue engine.\nHelp me untangle our martech stack.",
		);
	}

	public function register_settings() {
		register_setting(
			self::SLUG,
			ICAIA_OPTION,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this, 'sanitize' ),
				'default'           => self::defaults(),
			)
		);
	}

	/**
	 * Sanitize the entire options payload. Defaults fill in any missing
	 * keys so older saves stay valid as new fields are added.
	 */
	public function sanitize( $input ) {
		$defaults = self::defaults();
		$out      = $defaults;

		if ( isset( $input['chat_embed'] ) ) {
			// The chat embed contains <script> and custom elements; admins
			// with `manage_options` are trusted to paste raw markup here.
			$out['chat_embed'] = trim( (string) wp_unslash( $input['chat_embed'] ) );
		}

		if ( isset( $input['brand_color'] ) ) {
			$raw   = trim( (string) $input['brand_color'] );
			$color = '' === $raw ? '' : sanitize_hex_color( $raw );
			// Empty string is a valid value — it means "do not override".
			$out['brand_color'] = null === $color ? '' : (string) $color;
		}

		$out['enabled'] = ! empty( $input['enabled'] ) ? 1 : 0;

		if ( isset( $input['font_stack'] ) ) {
			$out['font_stack'] = sanitize_text_field( $input['font_stack'] );
		}

		$out['load_inter']   = ! empty( $input['load_inter'] ) ? 1 : 0;
		$out['dock_enabled'] = ! empty( $input['dock_enabled'] ) ? 1 : 0;

		if ( isset( $input['suggestions'] ) ) {
			$out['suggestions'] = sanitize_textarea_field( $input['suggestions'] );
		}

		return $out;
	}

	public function register_menu() {
		add_options_page(
			__( 'AI Website Assistant', 'inkline-connect-ai-assistant' ),
			__( 'AI Website Assistant', 'inkline-connect-ai-assistant' ),
			'manage_options',
			self::SLUG,
			array( $this, 'render_page' )
		);
	}

	public function enqueue_admin_assets( $hook ) {
		if ( 'settings_page_' . self::SLUG !== $hook ) {
			return;
		}
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script(
			'icaia-admin-settings',
			ICAIA_URL . 'assets/js/admin-settings.js',
			array( 'jquery', 'wp-color-picker' ),
			ICAIA_VERSION,
			true
		);
	}

	public function render_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$s              = self::all();
		$has_embed      = '' !== trim( (string) $s['chat_embed'] );
		$enabled        = ! empty( $s['enabled'] );
		$active         = $has_embed && $enabled;
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'AI Website Assistant', 'inkline-connect-ai-assistant' ); ?></h1>
			<p class="description" style="max-width:48rem">
				<?php esc_html_e( 'This plugin drops an Inkline Connect–powered AI assistant into your site: a docked bar that follows visitors as they scroll, an in-page widget (shortcode and Elementor), and matching styling for the connected chat widget. Paste your Inkline Connect chat-widget embed code below to activate it.', 'inkline-connect-ai-assistant' ); ?>
			</p>

			<?php if ( ! $enabled ) : ?>
				<div class="notice notice-info inline" style="margin:16px 0">
					<p><?php esc_html_e( 'The assistant is turned off. The dock, in-page widget, and chat widget are all hidden on the front end. Toggle it on below to bring everything back.', 'inkline-connect-ai-assistant' ); ?></p>
				</div>
			<?php elseif ( ! $has_embed ) : ?>
				<div class="notice notice-warning inline" style="margin:16px 0">
					<p><?php esc_html_e( 'The assistant is not active yet. Paste your Inkline Connect chat-widget embed code below and save to turn it on.', 'inkline-connect-ai-assistant' ); ?></p>
				</div>
			<?php else : ?>
				<div class="notice notice-success inline" style="margin:16px 0">
					<p><?php esc_html_e( 'The assistant is active. The docked bar appears site-wide, and you can drop the in-page widget anywhere using the shortcode or Elementor.', 'inkline-connect-ai-assistant' ); ?></p>
				</div>
			<?php endif; ?>

			<form method="post" action="options.php">
				<?php settings_fields( self::SLUG ); ?>

				<h2 class="title"><?php esc_html_e( 'Master switch', 'inkline-connect-ai-assistant' ); ?></h2>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><?php esc_html_e( 'Assistant', 'inkline-connect-ai-assistant' ); ?></th>
						<td>
							<label>
								<input
									type="checkbox"
									name="<?php echo esc_attr( ICAIA_OPTION ); ?>[enabled]"
									value="1"
									<?php checked( ! empty( $s['enabled'] ) ); ?>
								/>
								<?php esc_html_e( 'Show the assistant on the front end', 'inkline-connect-ai-assistant' ); ?>
							</label>
							<p class="description"><?php esc_html_e( 'When off, the dock, every in-page widget instance (shortcode and Elementor), and the chat-widget embed are all suppressed. The shortcode renders nothing — no markup, no space. Your settings stay saved; flip this back on to bring everything back.', 'inkline-connect-ai-assistant' ); ?></p>
						</td>
					</tr>
				</table>

				<h2 class="title"><?php esc_html_e( 'Inkline Connect', 'inkline-connect-ai-assistant' ); ?></h2>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row">
							<label for="icaia-chat-embed"><?php esc_html_e( 'Chat-widget embed code', 'inkline-connect-ai-assistant' ); ?></label>
						</th>
						<td>
							<textarea
								id="icaia-chat-embed"
								name="<?php echo esc_attr( ICAIA_OPTION ); ?>[chat_embed]"
								rows="8"
								cols="80"
								class="large-text code"
								spellcheck="false"
								placeholder="<?php esc_attr_e( "Paste the full <chat-widget …> tag and the LeadConnector loader <script>…</script> here.", 'inkline-connect-ai-assistant' ); ?>"
							><?php echo esc_textarea( $s['chat_embed'] ); ?></textarea>
							<p class="description">
								<?php esc_html_e( 'Paste the full Inkline Connect chat-widget embed snippet (the chat-widget element plus the loader script). Nothing renders on the front end until this is filled in.', 'inkline-connect-ai-assistant' ); ?>
							</p>
						</td>
					</tr>
				</table>

				<h2 class="title"><?php esc_html_e( 'Branding', 'inkline-connect-ai-assistant' ); ?></h2>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row">
							<label for="icaia-brand-color"><?php esc_html_e( 'Brand color', 'inkline-connect-ai-assistant' ); ?></label>
						</th>
						<td>
							<input
								id="icaia-brand-color"
								type="text"
								name="<?php echo esc_attr( ICAIA_OPTION ); ?>[brand_color]"
								value="<?php echo esc_attr( $s['brand_color'] ); ?>"
								class="icaia-color-field"
								data-default-color=""
							/>
							<p class="description"><?php esc_html_e( 'Optional. When set, applied to the send button, focus ring, and accent details across the in-page widget, the dock, and the chat widget. Leave blank to leave the chat widget on the colors you configured in Inkline Connect (the in-page widget and dock then use a neutral default).', 'inkline-connect-ai-assistant' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="icaia-font-stack"><?php esc_html_e( 'Font family', 'inkline-connect-ai-assistant' ); ?></label>
						</th>
						<td>
							<input
								id="icaia-font-stack"
								type="text"
								name="<?php echo esc_attr( ICAIA_OPTION ); ?>[font_stack]"
								value="<?php echo esc_attr( $s['font_stack'] ); ?>"
								class="regular-text"
							/>
							<p>
								<label>
									<input
										type="checkbox"
										name="<?php echo esc_attr( ICAIA_OPTION ); ?>[load_inter]"
										value="1"
										<?php checked( ! empty( $s['load_inter'] ) ); ?>
									/>
									<?php esc_html_e( 'Load Inter from Google Fonts', 'inkline-connect-ai-assistant' ); ?>
								</label>
							</p>
							<p class="description"><?php esc_html_e( 'CSS font-family value used by both the in-page widget and the dock. Leave Inter selected for the Inkline default; uncheck Google Fonts if your theme already loads it (or you prefer a system stack).', 'inkline-connect-ai-assistant' ); ?></p>
						</td>
					</tr>
				</table>

				<h2 class="title"><?php esc_html_e( 'Behaviour', 'inkline-connect-ai-assistant' ); ?></h2>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><?php esc_html_e( 'Docked assistant', 'inkline-connect-ai-assistant' ); ?></th>
						<td>
							<label>
								<input
									type="checkbox"
									name="<?php echo esc_attr( ICAIA_OPTION ); ?>[dock_enabled]"
									value="1"
									<?php checked( ! empty( $s['dock_enabled'] ) ); ?>
								/>
								<?php esc_html_e( 'Show the site-wide docked bar', 'inkline-connect-ai-assistant' ); ?>
							</label>
							<p class="description"><?php esc_html_e( 'Fixed bottom-of-viewport bar that follows visitors as they scroll. Hides automatically on pages where the in-page widget is currently in view. Visitors can dismiss it; opening the chat widget brings it back.', 'inkline-connect-ai-assistant' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="icaia-suggestions"><?php esc_html_e( 'Starter prompts', 'inkline-connect-ai-assistant' ); ?></label>
						</th>
						<td>
							<textarea
								id="icaia-suggestions"
								name="<?php echo esc_attr( ICAIA_OPTION ); ?>[suggestions]"
								rows="8"
								cols="80"
								class="large-text"
							><?php echo esc_textarea( $s['suggestions'] ); ?></textarea>
							<p class="description"><?php esc_html_e( 'One prompt per line. Used as the rotating "Try" chips and the animated placeholder. Keep them in your visitor\'s voice — questions or first-person statements work best.', 'inkline-connect-ai-assistant' ); ?></p>
						</td>
					</tr>
				</table>

				<?php submit_button(); ?>
			</form>

			<hr />

			<h2><?php esc_html_e( 'Drop the in-page widget into a page', 'inkline-connect-ai-assistant' ); ?></h2>
			<p><?php esc_html_e( 'Use the shortcode anywhere:', 'inkline-connect-ai-assistant' ); ?></p>
			<p><code>[inkline_ai_assistant]</code></p>
			<p><?php esc_html_e( 'With options:', 'inkline-connect-ai-assistant' ); ?></p>
			<p><code>[inkline_ai_assistant eyebrow="Ask Inkline" heading="Tell us what you’re trying to solve." sub="Plain language is fine." variant="card" align="center"]</code></p>
			<p>
				<strong><?php esc_html_e( 'variant', 'inkline-connect-ai-assistant' ); ?></strong>: <code>card</code>
				<?php esc_html_e( '(default, white surface) or', 'inkline-connect-ai-assistant' ); ?> <code>bare</code>
				<?php esc_html_e( '(no surface — for use inside a hero band).', 'inkline-connect-ai-assistant' ); ?><br />
				<strong><?php esc_html_e( 'align', 'inkline-connect-ai-assistant' ); ?></strong>: <code>left</code>
				<?php esc_html_e( '(default) or', 'inkline-connect-ai-assistant' ); ?> <code>center</code>.
			</p>
			<p><?php esc_html_e( 'Elementor users can drag the "AI Assistant" widget from the General category.', 'inkline-connect-ai-assistant' ); ?></p>
		</div>
		<?php
	}
}
