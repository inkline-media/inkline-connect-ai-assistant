<?php
/**
 * Docked assistant template — rendered once in the footer site-wide.
 *
 * @package Inkline_Connect_AI_Assistant
 */

if ( ! defined( 'ABSPATH' ) ) {
	return;
}
?>
<div class="iaa-dock" data-iaa-dock inert>
	<div class="iaa-dock__shell">
		<form class="iaa-dock__bar iaa-assist__bar" data-iaa-assist-bar>
			<span class="iaa-dock__icon" aria-hidden="true">
				<?php $icaia_svg_class = 'iaa-dock__icon-svg'; include ICAIA_DIR . 'templates/icons/sparkles-solid.php'; ?>
			</span>
			<input
				type="text"
				class="iaa-dock__input iaa-assist__input"
				placeholder="<?php esc_attr_e( 'Ask the assistant about anything…', 'inkline-connect-ai-assistant' ); ?>"
				aria-label="<?php esc_attr_e( 'Ask the assistant', 'inkline-connect-ai-assistant' ); ?>"
				autocomplete="off"
			/>
			<button type="submit" class="iaa-dock__send" aria-label="<?php esc_attr_e( 'Send', 'inkline-connect-ai-assistant' ); ?>">
				<svg class="iaa-dock__send-icon iaa-dock__send-icon--send" viewBox="0 0 16 16" width="16" height="16" aria-hidden="true">
					<path d="M3 8h9M8.5 3.5L13 8l-4.5 4.5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
				</svg>
				<svg class="iaa-dock__send-icon iaa-dock__send-icon--close" viewBox="0 0 16 16" width="16" height="16" aria-hidden="true">
					<path d="M4 4l8 8M12 4l-8 8" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" />
				</svg>
			</button>
		</form>
		<button
			type="button"
			class="iaa-dock__dismiss"
			data-iaa-dock-dismiss
			aria-label="<?php esc_attr_e( 'Dismiss the assistant', 'inkline-connect-ai-assistant' ); ?>"
			title="<?php esc_attr_e( 'Hide the assistant', 'inkline-connect-ai-assistant' ); ?>"
		>
			<svg viewBox="0 0 16 16" width="11" height="11" aria-hidden="true">
				<path d="M4 4l8 8M12 4l-8 8" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" />
			</svg>
		</button>
	</div>
</div>
