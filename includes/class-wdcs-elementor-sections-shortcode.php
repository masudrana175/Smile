<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WDCS_Elementor_Sections_Shortcode {

	/**
	 * Maps order meta keys to their saved Elementor section IDs.
	 */
	private $sections = array(
		'section_order_2'           => 7965,
		'section_order_seinformaion' => 7963,
	);

	public function __construct() {
		add_shortcode( 'wdcs_elementor_sections', array( $this, 'render' ) );
	}

	public function render( $atts ) {
		$atts = shortcode_atts( array(
			'post_id' => 0,
		), $atts, 'wdcs_elementor_sections' );

		$post_id = $atts['post_id'] ? intval( $atts['post_id'] ) : get_queried_object_id();

		if ( ! $post_id ) {
			return '';
		}

		if ( ! class_exists( '\Elementor\Plugin' ) ) {
			return '';
		}

		// Build list of sections with their order values from post meta.
		$ordered = array();
		foreach ( $this->sections as $meta_key => $section_id ) {
			$order = get_post_meta( $post_id, $meta_key, true );
			if ( $order === '' || $order === false ) {
				continue;
			}
			$ordered[] = array(
				'order'      => intval( $order ),
				'section_id' => intval( $section_id ),
			);
		}

		if ( empty( $ordered ) ) {
			return '';
		}

		usort( $ordered, function( $a, $b ) {
			return $a['order'] - $b['order'];
		} );

		ob_start();
		foreach ( $ordered as $item ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo \Elementor\Plugin::$instance->frontend->get_builder_content_for_display( $item['section_id'] );
		}
		return ob_get_clean();
	}
}
