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
				<svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M12 2l1.7 5.3L19 9l-5.3 1.7L12 16l-1.7-5.3L5 9l5.3-1.7L12 2zm6 11l.9 2.6L21.5 16.5l-2.6.9L18 20l-.9-2.6L14.5 16.5l2.6-.9L18 13zM6 13l.7 1.9L8.6 15.6 6.7 16.3 6 18.3l-.7-1.9L3.4 15.6 5.3 14.9 6 13z"/></svg>
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
