<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WDCS_Services_Shortcode {

	public function __construct() {
		add_shortcode( 'wdcs_services', array( $this, 'render' ) );
	}

	public function render( $atts ) {
		$atts = shortcode_atts( array(
			'post_id' => 0,
		), $atts, 'wdcs_services' );

		$post_id = $atts['post_id'] ? intval( $atts['post_id'] ) : get_queried_object_id();

		if ( ! $post_id ) {
			return '';
		}

		$raw = get_post_meta( $post_id, 'dentistry_services_in_lv', true );

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
			$image_meta = isset( $row['dsilv_icon'] ) ? $row['dsilv_icon'] : '';
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
				'icon'     => $image_url,
				'title'    => isset( $row['dsilv_title'] )            ? $row['dsilv_title']            : '',
				'subtitle' => isset( $row['dsilv_sub_title'] )        ? $row['dsilv_sub_title']        : '',
				'text'     => isset( $row['dsilv_shortdescription'] ) ? $row['dsilv_shortdescription'] : '',
				'url'      => isset( $row['dsilv_learn_url'] )        ? $row['dsilv_learn_url']        : '',
			);
		}

		if ( empty( $items ) ) {
			return '';
		}

		wp_enqueue_style( 'wdcs-smile' );

		ob_start();
		?>
		<div class="wdcs-services-section">
			<div class="wdcs-services-grid">
				<?php foreach ( $items as $item ) : ?>
				<div class="wdcs-services-card">
					<div class="wdcs-services-header">
						<?php if ( $item['icon'] ) : ?>
						<div class="wdcs-services-icon">
							<img
								src="<?php echo esc_url( $item['icon'] ); ?>"
								alt="<?php echo esc_attr( $item['title'] ); ?>"
								loading="lazy"
							>
						</div>
						<?php endif; ?>
						<div class="wdcs-services-titles">
							<?php if ( $item['title'] ) : ?>
							<h3 class="wdcs-services-title">
								<?php echo esc_html( $item['title'] ); ?>
							</h3>
							<?php endif; ?>
							<?php if ( $item['subtitle'] ) : ?>
							<span class="wdcs-services-subtitle">
								<?php echo esc_html( $item['subtitle'] ); ?>
							</span>
							<?php endif; ?>
						</div>
					</div>
					<?php if ( $item['text'] ) : ?>
					<p class="wdcs-services-text">
						<?php echo wp_kses_post( $item['text'] ); ?>
					</p>
					<?php endif; ?>
					<?php if ( $item['url'] ) : ?>
					<a href="<?php echo esc_url( $item['url'] ); ?>" class="wdcs-services-more">
						LEARN MORE
					</a>
					<?php endif; ?>
				</div>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}
}
