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
			$post_id        = get_the_ID();
			$image_meta     = get_post_meta( $post_id, 'dr_featured_image', true );
			$image_url      = '';
			if ( ! empty( $image_meta ) ) {
				// Support both attachment ID and direct URL.
				if ( is_numeric( $image_meta ) ) {
					$image_url = wp_get_attachment_url( intval( $image_meta ) );
				} else {
					$image_url = esc_url( $image_meta );
				}
			}
			$doctors[] = array(
				'title'   => get_the_title(),
				'content' => get_post_meta( $post_id, 'doctore_content', true ),
				'image'   => $image_url,
				'link'    => get_permalink(),
			);
		}
		wp_reset_postdata();

		ob_start();
		?>
		<div class="wdcs-doctors-section">

			<!-- Thumbnail tabs -->
			<div class="wdcs-doctors-tabs">
				<?php foreach ( $doctors as $index => $doctor ) : ?>
				<button
					class="wdcs-doctor-tab<?php echo 0 === $index ? ' is-active' : ''; ?>"
					data-index="<?php echo esc_attr( $index ); ?>"
					aria-label="<?php echo esc_attr( $doctor['title'] ); ?>"
				>
					<?php if ( $doctor['image'] ) : ?>
					<div class="wdcs-tab-img-wrap">
						<img
							src="<?php echo esc_url( $doctor['image'] ); ?>"
							alt="<?php echo esc_attr( $doctor['title'] ); ?>"
							loading="lazy"
						>
					</div>
					<?php else : ?>
					<div class="wdcs-tab-img-wrap wdcs-tab-img-placeholder"></div>
					<?php endif; ?>
					<span class="wdcs-tab-name"><?php echo esc_html( strtoupper( $doctor['title'] ) ); ?></span>
				</button>
				<?php endforeach; ?>
			</div>

			<!-- Slider -->
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
						<?php if ( $doctor['image'] ) : ?>
						<div class="wdcs-slide-image">
							<img
								src="<?php echo esc_url( $doctor['image'] ); ?>"
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
		<?php
		return ob_get_clean();
	}
}
