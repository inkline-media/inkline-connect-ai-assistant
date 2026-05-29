<?php
/**
 * Elementor widget — drop the in-page assistant into any page or
 * template the same way as the shortcode, with editable content fields.
 *
 * Registered conditionally when Elementor is present.
 *
 * @package Inkline_Connect_AI_Assistant
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( '\\Elementor\\Widget_Base' ) ) {
	return;
}

if ( ! class_exists( 'ICAIA_Elementor_Widget' ) ) :

class ICAIA_Elementor_Widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'icaia_inline_widget';
	}

	public function get_title() {
		return __( 'AI Assistant', 'inkline-connect-ai-assistant' );
	}

	public function get_icon() {
		return 'eicon-commenting-o';
	}

	public function get_categories() {
		return array( 'general' );
	}

	public function get_keywords() {
		return array( 'ai', 'assistant', 'chat', 'inkline', 'inkline connect' );
	}

	protected function register_controls() {
		$this->start_controls_section(
			'icaia_content',
			array(
				'label' => __( 'Content', 'inkline-connect-ai-assistant' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'eyebrow',
			array(
				'label'   => __( 'Eyebrow', 'inkline-connect-ai-assistant' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => __( 'Ask the assistant', 'inkline-connect-ai-assistant' ),
			)
		);

		$this->add_control(
			'heading',
			array(
				'label'   => __( 'Heading', 'inkline-connect-ai-assistant' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => __( 'Tell us what you’re trying to solve.', 'inkline-connect-ai-assistant' ),
			)
		);

		$this->add_control(
			'sub',
			array(
				'label'   => __( 'Sub', 'inkline-connect-ai-assistant' ),
				'type'    => \Elementor\Controls_Manager::TEXTAREA,
				'default' => __( 'Describe your goal in plain language and the assistant points you to the right place.', 'inkline-connect-ai-assistant' ),
			)
		);

		$this->add_control(
			'variant',
			array(
				'label'   => __( 'Variant', 'inkline-connect-ai-assistant' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'card',
				'options' => array(
					'card' => __( 'Card (white surface)', 'inkline-connect-ai-assistant' ),
					'bare' => __( 'Bare (no surface)', 'inkline-connect-ai-assistant' ),
				),
			)
		);

		$this->add_control(
			'align',
			array(
				'label'   => __( 'Alignment', 'inkline-connect-ai-assistant' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'left',
				'options' => array(
					'left'   => __( 'Left', 'inkline-connect-ai-assistant' ),
					'center' => __( 'Center', 'inkline-connect-ai-assistant' ),
				),
			)
		);

		$this->end_controls_section();
	}

	protected function render() {
		$a = $this->get_settings_for_display();
		$shortcode = sprintf(
			'[inkline_ai_assistant eyebrow="%1$s" heading="%2$s" sub="%3$s" variant="%4$s" align="%5$s"]',
			esc_attr( $a['eyebrow'] ?? '' ),
			esc_attr( $a['heading'] ?? '' ),
			esc_attr( $a['sub'] ?? '' ),
			esc_attr( $a['variant'] ?? 'card' ),
			esc_attr( $a['align'] ?? 'left' )
		);
		echo do_shortcode( $shortcode ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}

endif;

