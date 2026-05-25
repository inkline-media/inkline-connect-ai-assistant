<?php
/**
 * In-page assistant template.
 *
 * Rendered by [inkline_ai_assistant] and the Elementor widget. Expects
 * an $icaia_attrs array with eyebrow, heading, sub, variant, align.
 *
 * The DOM mirrors the dock so the same JS, CSS variables, and chat
 * bridge wire it up.
 *
 * @package Inkline_Connect_AI_Assistant
 */

if ( ! defined( 'ABSPATH' ) || ! isset( $icaia_attrs ) ) {
	return;
}

$variant = isset( $icaia_attrs['variant'] ) ? $icaia_attrs['variant'] : 'card';
$align   = isset( $icaia_attrs['align'] ) ? $icaia_attrs['align'] : 'left';
$eyebrow = isset( $icaia_attrs['eyebrow'] ) ? (string) $icaia_attrs['eyebrow'] : '';
$heading = isset( $icaia_attrs['heading'] ) ? (string) $icaia_attrs['heading'] : '';
$sub     = isset( $icaia_attrs['sub'] ) ? (string) $icaia_attrs['sub'] : '';

$root_classes  = array( 'iaa-assist' );
$root_classes[] = 'iaa-assist--' . $variant;
if ( 'center' === $align ) {
	$root_classes[] = 'iaa-assist--center';
}
?>
<div class="<?php echo esc_attr( implode( ' ', $root_classes ) ); ?>" data-iaa-assist>
	<?php if ( '' !== $eyebrow || '' !== $heading || '' !== $sub ) : ?>
		<div class="iaa-assist__head">
			<?php if ( '' !== $eyebrow ) : ?>
				<span class="iaa-assist__eyebrow">
					<span class="iaa-assist__eyebrow-glyph" aria-hidden="true">&#9728;</span>
					<?php echo esc_html( $eyebrow ); ?>
				</span>
			<?php endif; ?>
			<?php if ( '' !== $heading ) : ?>
				<h2 class="iaa-assist__title"><?php echo esc_html( $heading ); ?></h2>
			<?php endif; ?>
			<?php if ( '' !== $sub ) : ?>
				<p class="iaa-assist__sub"><?php echo esc_html( $sub ); ?></p>
			<?php endif; ?>
		</div>
	<?php endif; ?>

	<form class="iaa-assist__bar" data-iaa-assist-bar>
		<span class="iaa-assist__icon" aria-hidden="true">
			<svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor"><path d="M12 2l1.7 5.3L19 9l-5.3 1.7L12 16l-1.7-5.3L5 9l5.3-1.7L12 2zm6 11l.9 2.6L21.5 16.5l-2.6.9L18 20l-.9-2.6L14.5 16.5l2.6-.9L18 13zM6 13l.7 1.9L8.6 15.6 6.7 16.3 6 18.3l-.7-1.9L3.4 15.6 5.3 14.9 6 13z"/></svg>
		</span>
		<input
			type="text"
			class="iaa-assist__input"
			placeholder="<?php esc_attr_e( 'Ask about anything…', 'inkline-connect-ai-assistant' ); ?>"
			aria-label="<?php esc_attr_e( 'Ask the assistant', 'inkline-connect-ai-assistant' ); ?>"
			autocomplete="off"
		/>
		<button type="submit" class="iaa-assist__send" aria-label="<?php esc_attr_e( 'Send', 'inkline-connect-ai-assistant' ); ?>">
			<svg viewBox="0 0 16 16" width="16" height="16" aria-hidden="true">
				<path d="M3 8h9M8.5 3.5L13 8l-4.5 4.5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
			</svg>
		</button>
	</form>

	<div class="iaa-assist__suggest">
		<span class="iaa-assist__suggest-label"><?php esc_html_e( 'Try', 'inkline-connect-ai-assistant' ); ?></span>
		<ul class="iaa-assist__chips" role="list"></ul>
	</div>
</div>
