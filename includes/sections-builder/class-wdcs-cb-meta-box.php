<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class WDCS_CB_Meta_Box {

	const META_KEY = '_wdcs_cb_sections';

	public function __construct() {
		add_action( 'add_meta_boxes',        array( $this, 'register' ) );
		add_action( 'save_post',             array( $this, 'save' ), 10, 2 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
	}

	public function register() {
		$post_types = array( 'page', 'post', 'patient-services' );
		foreach ( $post_types as $pt ) {
			add_meta_box(
				'wdcs-cb-meta-box',
				'Content Builder',
				array( $this, 'render_meta_box' ),
				$pt,
				'normal',
				'high'
			);
		}
	}

	public function render_meta_box( $post ) {
		WDCS_CB_Admin_UI::render( $post );
	}

	public function save( $post_id, $post ) {
		if ( ! isset( $_POST['wdcs_cb_nonce'] ) ||
		     ! wp_verify_nonce( sanitize_key( $_POST['wdcs_cb_nonce'] ), 'wdcs_cb_save' ) ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		$json = isset( $_POST['wdcs_cb_data'] ) ? wp_unslash( $_POST['wdcs_cb_data'] ) : '[]';
		$raw  = json_decode( $json, true );
		if ( ! is_array( $raw ) ) {
			$raw = array();
		}

		$sanitized = WDCS_CB_Save::sanitize_sections( $raw );
		update_post_meta( $post_id, self::META_KEY, $sanitized );
	}

	public function enqueue( $hook ) {
		if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
			return;
		}

		wp_enqueue_media();
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_style(
			'wdcs-cb-builder',
			WDCS_PLUGIN_URL . 'assets/css/wdcs-sections-builder.css',
			array(),
			WDCS_VERSION
		);
		wp_enqueue_script(
			'wdcs-cb-builder',
			WDCS_PLUGIN_URL . 'assets/js/wdcs-sections-builder.js',
			array( 'jquery', 'jquery-ui-sortable', 'wp-color-picker' ),
			WDCS_VERSION,
			true
		);

		wp_localize_script( 'wdcs-cb-builder', 'wdcsCBTemplates', array(
			'section' => WDCS_CB_Admin_UI::section_html( array( 'id' => '__SECID__' ) ),
			'blocks'  => array(
				'text'   => WDCS_CB_Admin_UI::block_html( array( 'id' => '__BLKID__', 'type' => 'text' ) ),
				'image'  => WDCS_CB_Admin_UI::block_html( array( 'id' => '__BLKID__', 'type' => 'image' ) ),
				'button' => WDCS_CB_Admin_UI::block_html( array( 'id' => '__BLKID__', 'type' => 'button' ) ),
			),
		) );
	}
}
