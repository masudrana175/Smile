<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class WDCS_CB_Admin_UI extends WDCS_CB_Admin_UI_Base {

	public static function render( $post ) {
		wp_nonce_field( 'wdcs_cb_save', 'wdcs_cb_nonce' );

		$raw      = get_post_meta( $post->ID, '_wdcs_cb_sections', true );
		$sections = ( is_array( $raw ) && ! empty( $raw ) ) ? $raw : array();

		$json = wp_json_encode( $sections, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
		?>
		<input type="hidden" id="wdcs-cb-data" name="wdcs_cb_data" value="<?php echo esc_attr( $json ); ?>">

		<p class="description" style="margin:8px 0 10px;font-size:12px;color:#666;">
			Use the <strong>Select section(s)</strong> panel (right sidebar) to add content sections here.
		</p>

		<div id="wdcs-cb-sections-list">
			<?php foreach ( $sections as $section ) : ?>
				<?php echo self::section_html( $section ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<?php endforeach; ?>
		</div>
		<?php
	}

}
