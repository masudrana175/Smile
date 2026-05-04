<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WDCS_Doctors_Shortcode {

	public function __construct() {
		add_shortcode( 'wdcs_doctors', array( $this, 'render' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue' ) );
	}

	public function enqueue() {
		wp_register_style(
			'slick-css',
			'https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.css',
			array(),
			'1.8.1'
		);
		wp_register_style(
			'slick-theme-css',
			'https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick-theme.min.css',
			array( 'slick-css' ),
			'1.8.1'
		);
		wp_register_script(
			'slick-js',
			'https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js',
			array( 'jquery' ),
			'1.8.1',
			true
		);
		wp_register_style(
			'wdcs-doctors',
			WDCS_PLUGIN_URL . 'assets/css/wdcs-doctors.css',
			array( 'slick-css', 'slick-theme-css' ),
			WDCS_VERSION
		);
		wp_register_script(
			'wdcs-doctors',
			WDCS_PLUGIN_URL . 'assets/js/wdcs-doctors.js',
			array( 'jquery', 'slick-js' ),
			WDCS_VERSION,
			true
		);
	}

	/**
	 * Resolves a meta value that may be an attachment ID or a direct URL.
	 */
	private function resolve_image( $meta ) {
		if ( empty( $meta ) ) {
			return '';
		}
		if ( is_numeric( $meta ) ) {
			return (string) wp_get_attachment_url( intval( $meta ) );
		}
		return esc_url_raw( $meta );
	}

	public function render( $atts ) {
		$atts = shortcode_atts( array(
			'posts_per_page' => -1,
			'orderby'        => 'menu_order',
			'order'          => 'ASC',
		), $atts, 'wdcs_doctors' );

		$query = new WP_Query( array(
			'post_type'      => 'doctors',
			'posts_per_page' => intval( $atts['posts_per_page'] ),
			'orderby'        => sanitize_key( $atts['orderby'] ),
			'order'          => sanitize_key( $atts['order'] ),
			'post_status'    => 'publish',
		) );

		if ( ! $query->have_posts() ) {
			return '';
		}

		wp_enqueue_style( 'wdcs-doctors' );
		wp_enqueue_script( 'wdcs-doctors' );

		$doctors = array();
		while ( $query->have_posts() ) {
			$query->the_post();
			$post_id   = get_the_ID();
			$doctors[] = array(
				'title'       => get_the_title(),
				// Tab thumbnail label — falls back to post title if empty.
				'tab_title'   => get_post_meta( $post_id, 'dr_slide_thumbnail_title', true ),
				// Small photo shown in the tab row.
				'tab_image'   => $this->resolve_image( get_post_meta( $post_id, 'dr_featured_image', true ) ),
				// Large photo shown inside the teal slide card.
				'slide_image' => $this->resolve_image( get_post_meta( $post_id, 'dr_slide_thumbnail', true ) ),
				// Bio text inside the slide card.
				'content'     => get_post_meta( $post_id, 'dc_slide_text', true ),
				'link'        => get_permalink(),
			);
		}
		wp_reset_postdata();

		ob_start();
		?>
		<div class="wdcs-doctors-section">

			<!-- Thumbnail tabs -->
			<div class="wdcs-doctors-tabs">
				<?php foreach ( $doctors as $index => $doctor ) : ?>
				<?php $tab_label = ! empty( $doctor['tab_title'] ) ? $doctor['tab_title'] : $doctor['title']; ?>
				<button
					class="wdcs-doctor-tab<?php echo 0 === $index ? ' is-active' : ''; ?>"
					data-index="<?php echo esc_attr( $index ); ?>"
					aria-label="<?php echo esc_attr( $tab_label ); ?>"
				>
					<?php if ( $doctor['tab_image'] ) : ?>
					<div class="wdcs-tab-img-wrap">
						<img
							src="<?php echo esc_url( $doctor['tab_image'] ); ?>"
							alt="<?php echo esc_attr( $tab_label ); ?>"
							loading="lazy"
						>
					</div>
					<?php else : ?>
					<div class="wdcs-tab-img-wrap wdcs-tab-img-placeholder"></div>
					<?php endif; ?>
					<span class="wdcs-tab-name"><?php echo esc_html( strtoupper( $tab_label ) ); ?></span>
				</button>
				<?php endforeach; ?>
			</div>

			<!-- Slider -->
			<div class="wdcs-slider-wrap">
				<div class="wdcs-doctors-slider">
					<?php foreach ( $doctors as $doctor ) : ?>
					<div class="wdcs-doctor-slide">
						<div class="wdcs-slide-inner">
							<div class="wdcs-slide-content">
								<h2 class="wdcs-slide-heading">
									<?php echo esc_html( 'Meet ' . $doctor['title'] ); ?>
								</h2>
								<div class="wdcs-slide-text">
									<?php echo wp_kses_post( $doctor['content'] ); ?>
								</div>
								<?php if ( $doctor['link'] ) : ?>
								<a href="<?php echo esc_url( $doctor['link'] ); ?>" class="wdcs-learn-more">
									LEARN MORE
								</a>
								<?php endif; ?>
							</div>
							<?php if ( $doctor['slide_image'] ) : ?>
							<div class="wdcs-slide-image">
								<img
									src="<?php echo esc_url( $doctor['slide_image'] ); ?>"
									alt="<?php echo esc_attr( $doctor['title'] ); ?>"
									loading="lazy"
								>
							</div>
							<?php endif; ?>
						</div>
						<p class="wdcs-swipe-hint" aria-hidden="true">&#8592; Swipe left for next doctor</p>
					</div>
					<?php endforeach; ?>
				</div>
			</div>

		</div>
		<?php
		return ob_get_clean();
	}
}
