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
					<?php $icaia_svg_class = 'iaa-assist__eyebrow-glyph'; include ICAIA_DIR . 'templates/icons/sparkles-solid.php'; ?>
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
			<?php $icaia_svg_class = 'iaa-assist__icon-svg'; include ICAIA_DIR . 'templates/icons/sparkles-solid.php'; ?>
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
