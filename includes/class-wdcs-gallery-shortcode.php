<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WDCS_Gallery_Shortcode {

	public function __construct() {
		add_shortcode( 'wdcs_gallery', array( $this, 'render' ) );
	}

	public function render( $atts ) {
		$atts = shortcode_atts( array(
			'post_id' => 0,
		), $atts, 'wdcs_gallery' );

		$post_id = $atts['post_id'] ? intval( $atts['post_id'] ) : get_queried_object_id();

		if ( ! $post_id ) {
			return '';
		}

		$raw = get_post_meta( $post_id, 'st_gallery_images', true );

		// Normalise: unserialise, split CSV, or use array as-is.
		if ( is_string( $raw ) ) {
			$try = maybe_unserialize( $raw );
			$raw = is_array( $try ) ? $try : array_filter( array_map( 'trim', explode( ',', $raw ) ) );
		}

		if ( empty( $raw ) || ! is_array( $raw ) ) {
			return '';
		}

		// Resolve each entry to a URL.
		$images = array();
		foreach ( $raw as $entry ) {
			if ( is_array( $entry ) ) {
				// JetEngine gallery: { id, url } or similar.
				$url = isset( $entry['url'] ) ? esc_url_raw( $entry['url'] ) : '';
				if ( empty( $url ) && isset( $entry['id'] ) ) {
					$url = (string) wp_get_attachment_url( intval( $entry['id'] ) );
				}
			} elseif ( is_numeric( $entry ) ) {
				$url = (string) wp_get_attachment_url( intval( $entry ) );
			} else {
				$url = esc_url_raw( $entry );
			}
			if ( $url ) {
				$images[] = $url;
			}
		}

		if ( empty( $images ) ) {
			return '';
		}

		// Group into before/after pairs.
		$pairs = array_chunk( $images, 2 );

		wp_enqueue_style( 'wdcs-smile' );
		wp_enqueue_script( 'wdcs-smile' );

		ob_start();
		?>
		<div class="wdcs-gallery-section">
			<div class="wdcs-gallery-slider">
				<?php foreach ( $pairs as $pair ) : ?>
				<div class="wdcs-gallery-slide">
					<div class="wdcs-gallery-pair">
						<div class="wdcs-gallery-item">
							<img src="<?php echo esc_url( $pair[0] ); ?>" alt="Before" loading="lazy">
							<span class="wdcs-gallery-label">BEFORE</span>
						</div>
						<?php if ( ! empty( $pair[1] ) ) : ?>
						<div class="wdcs-gallery-item">
							<img src="<?php echo esc_url( $pair[1] ); ?>" alt="After" loading="lazy">
							<span class="wdcs-gallery-label">AFTER</span>
						</div>
						<?php endif; ?>
					</div>
				</div>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}
}
