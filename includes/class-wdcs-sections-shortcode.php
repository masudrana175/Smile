<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WDCS_Sections_Shortcode {

	public function __construct() {
		add_shortcode( 'wdcs_sections', array( $this, 'render' ) );
	}

	public function render( $atts ) {
		if ( ! class_exists( '\Elementor\Plugin' ) ) {
			return '';
		}

		ob_start();
		echo \Elementor\Plugin::$instance->frontend->get_builder_content_for_display( 7965 );
		echo \Elementor\Plugin::$instance->frontend->get_builder_content_for_display( 7963 );
		return ob_get_clean();
	}
}
