<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WDCS_Looking_Shortcode {

	public function __construct() {
		add_shortcode( 'wdcs_looking_for', array( $this, 'render' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue' ) );
	}

	public function enqueue() {
		wp_register_style(
			'slick-css',
			'https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.css',
			array(),
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
			'wdcs-looking',
			WDCS_PLUGIN_URL . 'assets/css/wdcs-looking.css',
			array( 'slick-css' ),
			WDCS_VERSION
		);
		wp_register_script(
			'wdcs-looking',
			WDCS_PLUGIN_URL . 'assets/js/wdcs-looking.js',
			array( 'jquery', 'slick-js' ),
			WDCS_VERSION,
			true
		);
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

		// JetEngine may return a serialised string on the first unserialise pass.
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
					// JetEngine image field sometimes returns ['id'=>..,'url'=>..]
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

		wp_enqueue_style( 'wdcs-looking' );
		wp_enqueue_script( 'wdcs-looking' );

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
