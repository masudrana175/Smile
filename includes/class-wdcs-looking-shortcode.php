<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WDCS_Looking_Shortcode {

	public function __construct() {
		add_shortcode( 'wdcs_looking_for', array( $this, 'render' ) );
	}

	public function render( $atts ) {
		$atts = shortcode_atts( array(
			'post_id' => 0,
		), $atts, 'wdcs_looking_for' );

		$post_id = $atts['post_id'] ? intval( $atts['post_id'] ) : get_queried_object_id();

		if ( ! $post_id ) {
			return '';
		}

		$raw = get_post_meta( $post_id, 'what_are_you_looking_for', true );

		if ( is_string( $raw ) ) {
			$raw = maybe_unserialize( $raw );
		}

		if ( empty( $raw ) || ! is_array( $raw ) ) {
			return '';
		}

		$items = array();
		foreach ( $raw as $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}
			$image_meta = isset( $row['waylf_image'] ) ? $row['waylf_image'] : '';
			$image_url  = '';
			if ( ! empty( $image_meta ) ) {
				if ( is_array( $image_meta ) ) {
					$image_url = isset( $image_meta['url'] ) ? esc_url_raw( $image_meta['url'] ) : '';
					if ( empty( $image_url ) && isset( $image_meta['id'] ) ) {
						$image_url = (string) wp_get_attachment_url( intval( $image_meta['id'] ) );
					}
				} elseif ( is_numeric( $image_meta ) ) {
					$image_url = (string) wp_get_attachment_url( intval( $image_meta ) );
				} else {
					$image_url = esc_url_raw( $image_meta );
				}
			}
			$items[] = array(
				'image' => $image_url,
				'title' => isset( $row['waylf_title'] ) ? $row['waylf_title'] : '',
				'text'  => isset( $row['waylf_text'] )  ? $row['waylf_text']  : '',
				'url'   => isset( $row['waylf_learn_url'] ) ? $row['waylf_learn_url'] : '',
			);
		}

		if ( empty( $items ) ) {
			return '';
		}

		wp_enqueue_style( 'wdcs-smile' );
		wp_enqueue_script( 'wdcs-smile' );

		ob_start();
		?>
		<div class="wdcs-looking-section">
			<div class="wdcs-looking-slider">
				<?php foreach ( $items as $item ) : ?>
				<div class="wdcs-looking-slide">
					<div class="wdcs-looking-card">
						<?php if ( $item['image'] ) : ?>
						<div class="wdcs-looking-icon">
							<img
								src="<?php echo esc_url( $item['image'] ); ?>"
								alt="<?php echo esc_attr( $item['title'] ); ?>"
								loading="lazy"
							>
						</div>
						<?php endif; ?>
						<?php if ( $item['title'] ) : ?>
						<h3 class="wdcs-looking-title">
							<?php echo esc_html( $item['title'] ); ?>
						</h3>
						<?php endif; ?>
						<?php if ( $item['text'] ) : ?>
						<p class="wdcs-looking-text">
							<?php echo wp_kses_post( $item['text'] ); ?>
						</p>
						<?php endif; ?>
						<?php if ( $item['url'] ) : ?>
						<a href="<?php echo esc_url( $item['url'] ); ?>" class="wdcs-looking-more">
							LEARN MORE
						</a>
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
