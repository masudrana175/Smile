<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WDCS_Doctors_List_Shortcode {

	public function __construct() {
		add_shortcode( 'wdcs_doctors_list', array( $this, 'render' ) );
	}

	public function render( $atts ) {
		$atts = shortcode_atts( array(
			'posts_per_page' => -1,
			'orderby'        => 'menu_order',
			'order'          => 'ASC',
		), $atts, 'wdcs_doctors_list' );

		$query = new WP_Query( array(
			'post_type'      => 'doctors',
			'posts_per_page' => intval( $atts['posts_per_page'] ),
			'orderby'        => sanitize_key( $atts['orderby'] ),
			'order'          => 'DESC' === strtoupper( $atts['order'] ) ? 'DESC' : 'ASC',
			'post_status'    => 'publish',
		) );

		if ( ! $query->have_posts() ) {
			return '';
		}

		$doctors = array();
		while ( $query->have_posts() ) {
			$query->the_post();
			$post_id   = get_the_ID();
			$image_url = $this->resolve_image( get_post_meta( $post_id, 'dr_slide_thumbnail', true ) );

			$doctors[] = array(
				'title'       => get_the_title(),
				'text'        => get_post_meta( $post_id, 'dc_slide_text', true ),
				'script_name' => get_post_meta( $post_id, 'dr_slide_thumbnail_title', true ),
				'image'       => $image_url,
				'url'         => get_permalink(),
			);
		}
		wp_reset_postdata();

		wp_enqueue_style( 'wdcs-great-vibes' );
		wp_enqueue_style( 'wdcs-smile' );

		ob_start();
		?>
		<div class="wdcs-doctors-list-section">
			<?php foreach ( $doctors as $index => $doctor ) : ?>
			<div class="wdcs-doctor-row<?php echo ( $index % 2 !== 0 ) ? ' is-reversed' : ''; ?>">
				<div class="wdcs-doctor-row-inner">
					<div class="wdcs-doctor-row-content">
						<?php if ( $doctor['title'] ) : ?>
						<h2 class="wdcs-doctor-row-heading">
							Meet <?php echo esc_html( $doctor['title'] ); ?>
						</h2>
						<?php endif; ?>
						<?php if ( $doctor['text'] ) : ?>
						<div class="wdcs-doctor-row-text">
							<?php echo wp_kses_post( $doctor['text'] ); ?>
						</div>
						<?php endif; ?>
						<div class="wdcs-doctor-row-footer">
							<?php if ( $doctor['url'] ) : ?>
							<a href="<?php echo esc_url( $doctor['url'] ); ?>" class="wdcs-doctor-row-more">
								LEARN MORE
							</a>
							<?php endif; ?>
							<?php if ( $doctor['script_name'] ) : ?>
							<span class="wdcs-doctor-row-script">
								<?php echo esc_html( $doctor['script_name'] ); ?>
							</span>
							<?php endif; ?>
						</div>
					</div>
					<?php if ( $doctor['image'] ) : ?>
					<div class="wdcs-doctor-row-image">
						<img src="<?php echo esc_url( $doctor['image'] ); ?>"
							alt="<?php echo esc_attr( $doctor['title'] ); ?>"
							loading="lazy">
					</div>
					<?php endif; ?>
				</div>
			</div>
			<?php endforeach; ?>
		</div>
		<?php
		return ob_get_clean();
	}

	private function resolve_image( $meta ) {
		if ( empty( $meta ) ) {
			return '';
		}
		if ( is_array( $meta ) ) {
			if ( isset( $meta['url'] ) ) {
				return esc_url_raw( $meta['url'] );
			}
			if ( isset( $meta['id'] ) ) {
				return (string) wp_get_attachment_url( intval( $meta['id'] ) );
			}
			return '';
		}
		if ( is_numeric( $meta ) ) {
			return (string) wp_get_attachment_url( intval( $meta ) );
		}
		return esc_url_raw( $meta );
	}
}
