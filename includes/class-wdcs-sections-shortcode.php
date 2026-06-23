<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WDCS_Sections_Shortcode {

	public function __construct() {
		add_shortcode( 'wdcs_sections', array( $this, 'render' ) );
	}

	public function render( $atts ) {
		$atts = shortcode_atts( array(
			'post_id' => 0,
		), $atts, 'wdcs_sections' );

		$post_id = $atts['post_id'] ? intval( $atts['post_id'] ) : get_queried_object_id();

		if ( ! $post_id ) {
			return '';
		}

		// Get sections enabled for this post.
		$active = get_post_meta( $post_id, WDCS_Admin_Sections::META_KEY, true );
		if ( empty( $active ) || ! is_array( $active ) ) {
			return '';
		}

		// Get all section definitions from the options page.
		$all_sections = WDCS_Admin_Sections::get_all_sections();

		// Build list of sections to render, with their order value.
		$to_render = array();
		foreach ( $active as $slug ) {
			if ( ! isset( $all_sections[ $slug ] ) ) {
				continue;
			}
			$section      = $all_sections[ $slug ];
			$elementor_id = ! empty( $section['elementor_id'] ) ? intval( $section['elementor_id'] ) : 0;

			if ( ! $elementor_id ) {
				continue; // Skip sections with no Elementor template assigned.
			}

			// Read order value from the post's own meta.
			$order = 0;
			if ( ! empty( $section['order_meta_key'] ) ) {
				$val   = get_post_meta( $post_id, sanitize_key( $section['order_meta_key'] ), true );
				$order = ( $val !== '' && $val !== false ) ? intval( $val ) : 0;
			}

			$to_render[] = array(
				'elementor_id' => $elementor_id,
				'order'        => $order,
				'label'        => $section['label'],
			);
		}

		if ( empty( $to_render ) ) {
			return '';
		}

		// Sort ascending by order value.
		usort( $to_render, function ( $a, $b ) {
			return $a['order'] - $b['order'];
		} );

		// Elementor must be active.
		if ( ! class_exists( '\Elementor\Plugin' ) ) {
			return '';
		}

		ob_start();
		foreach ( $to_render as $item ) {
			echo \Elementor\Plugin::$instance->frontend->get_builder_content_for_display( $item['elementor_id'] );
		}
		return ob_get_clean();
	}
}
