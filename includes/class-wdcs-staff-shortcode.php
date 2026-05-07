<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WDCS_Staff_Shortcode {

	public function __construct() {
		add_shortcode( 'wdcs_staff', array( $this, 'render' ) );
	}

	public function render( $atts ) {
		$atts = shortcode_atts( array(
			'post_id' => 0,
		), $atts, 'wdcs_staff' );

		$post_id = $atts['post_id'] ? intval( $atts['post_id'] ) : get_queried_object_id();

		if ( ! $post_id ) {
			return '';
		}

		$raw = get_post_meta( $post_id, 'our_staffs_members', true );

		if ( is_string( $raw ) ) {
			$raw = maybe_unserialize( $raw );
		}

		if ( empty( $raw ) || ! is_array( $raw ) ) {
			return '';
		}

		$members = array();
		foreach ( $raw as $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}

			$image_meta = isset( $row['our_staff_thumbnail'] ) ? $row['our_staff_thumbnail'] : '';
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

			$members[] = array(
				'image'       => $image_url,
				'name'        => isset( $row['our_staff_name'] )        ? $row['our_staff_name']        : '',
				'designation' => isset( $row['our_staff_designation'] ) ? $row['our_staff_designation'] : '',
				'description' => isset( $row['our_staff_description'] ) ? $row['our_staff_description'] : '',
			);
		}

		if ( empty( $members ) ) {
			return '';
		}

		wp_enqueue_style( 'wdcs-smile' );

		ob_start();
		?>
		<div class="wdcs-staff-section">
			<div class="wdcs-staff-grid">
				<?php foreach ( $members as $member ) : ?>
				<div class="wdcs-staff-card">
					<?php if ( $member['image'] ) : ?>
					<div class="wdcs-staff-image">
						<img src="<?php echo esc_url( $member['image'] ); ?>"
							alt="<?php echo esc_attr( $member['name'] ); ?>"
							loading="lazy">
					</div>
					<?php endif; ?>
					<div class="wdcs-staff-body">
						<?php if ( $member['name'] ) : ?>
						<h3 class="wdcs-staff-name">
							<?php echo esc_html( $member['name'] ); ?>
						</h3>
						<?php endif; ?>
						<?php if ( $member['designation'] ) : ?>
						<span class="wdcs-staff-designation">
							<?php echo esc_html( $member['designation'] ); ?>
						</span>
						<?php endif; ?>
						<?php if ( $member['description'] ) : ?>
						<div class="wdcs-staff-description">
							<?php echo wp_kses_post( $member['description'] ); ?>
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
