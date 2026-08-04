<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class WDCS_CB_Shortcode {

	public function __construct() {
		add_shortcode( 'wdcs_cb_sections', array( $this, 'render' ) );
	}

	public function render( $atts ) {
		$post_id = get_the_ID();
		if ( ! $post_id ) {
			return '';
		}
		return WDCS_CB_Renderer::render( $post_id );
	}
}
