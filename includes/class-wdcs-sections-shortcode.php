<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WDCS_Sections_Shortcode {

	public function __construct() {
		add_shortcode( 'wdcs_sections', array( $this, 'render' ) );
	}

	public function render( $atts ) {
		$post_id = get_the_ID();
		if ( ! $post_id ) {
			return '';
		}

		$active = get_post_meta( $post_id, '_wdcs_active_sections', true );
		if ( ! is_array( $active ) || empty( $active ) ) {
			return '';
		}

		$all_sections = get_option( 'wdcs_sections_settings', array() );
		if ( ! is_array( $all_sections ) ) {
			$all_sections = array();
		}

		ob_start();
		foreach ( $active as $raw ) {
			$slug = is_string( $raw ) ? $raw : ( isset( $raw['slug'] ) ? (string) $raw['slug'] : '' );

			// Individual custom builder section.
			if ( strpos( $slug, 'wdcs_cb_' ) === 0 ) {
				$section_id = substr( $slug, 8 );
				echo WDCS_CB_Renderer::render_by_section_id( $post_id, $section_id );
				continue;
			}

			// Elementor template sections.
			if ( ! isset( $all_sections[ $slug ] ) ) {
				continue;
			}
			if ( ! class_exists( '\Elementor\Plugin' ) ) {
				continue;
			}

			$elementor_id = (int) ( isset( $all_sections[ $slug ]['elementor_id'] ) ? $all_sections[ $slug ]['elementor_id'] : 0 );
			if ( $elementor_id <= 0 ) {
				continue;
			}

			echo \Elementor\Plugin::$instance->frontend->get_builder_content_for_display( $elementor_id );
		}
		return ob_get_clean();
	}
}
