<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WDCS_Sections_Shortcode {

	/**
	 * Maps order meta key → Elementor template ID.
	 * The numeric value stored in each meta key determines render order.
	 */
	private static $section_map = array(
		'section_order_2'            => 7965,
		'section_order_seinformaion' => 7963,
	);

	public function __construct() {
		add_shortcode( 'wdcs_sections', array( $this, 'render' ) );
	}

	public function render( $atts ) {
		if ( ! class_exists( '\Elementor\Plugin' ) ) {
			return '';
		}

		$post_id = get_the_ID();
		if ( ! $post_id ) {
			return '';
		}

		// Build list of [ order_value => elementor_id ] and sort ascending.
		$ordered = array();
		foreach ( self::$section_map as $meta_key => $elementor_id ) {
			$value = get_post_meta( $post_id, $meta_key, true );
			if ( '' !== $value && false !== $value ) {
				$ordered[ (int) $value ] = $elementor_id;
			} else {
				// No order value set — append at the end using a high key.
				$ordered[ 9999 + $elementor_id ] = $elementor_id;
			}
		}

		ksort( $ordered );

		ob_start();
		foreach ( $ordered as $elementor_id ) {
			echo \Elementor\Plugin::$instance->frontend->get_builder_content_for_display( $elementor_id );
		}
		return ob_get_clean();
	}
}
