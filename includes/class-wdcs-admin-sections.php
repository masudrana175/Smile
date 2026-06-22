<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WDCS_Admin_Sections {

	const META_KEY    = '_wdcs_active_sections';
	const NONCE_ACTION = 'wdcs_save_sections';
	const NONCE_FIELD  = 'wdcs_sections_nonce';

	/**
	 * Registry of all shortcode sections.
	 * Fill in 'jetengine_id' with the HTML id of the JetEngine meta box
	 * div on the edit screen (inspect with browser DevTools).
	 */
	private function get_sections() {
		return apply_filters( 'wdcs_sections_registry', array(
			'doctors_carousel' => array(
				'label'        => 'Doctors Carousel',
				'shortcode'    => '[wdcs_doctors]',
				'preview'      => 'doctors-carousel.svg',
				'jetengine_id' => '',
			),
			'doctors_list' => array(
				'label'        => 'Doctors List',
				'shortcode'    => '[wdcs_doctors_list]',
				'preview'      => 'doctors-list.svg',
				'jetengine_id' => '',
			),
			'looking_for' => array(
				'label'        => 'What Are You Looking For',
				'shortcode'    => '[wdcs_looking_for]',
				'preview'      => 'looking-for.svg',
				'jetengine_id' => '',
			),
			'services' => array(
				'label'        => 'Dentistry Services',
				'shortcode'    => '[wdcs_services]',
				'preview'      => 'services.svg',
				'jetengine_id' => '',
			),
			'gallery' => array(
				'label'        => 'Gallery Carousel',
				'shortcode'    => '[wdcs_gallery]',
				'preview'      => 'gallery.svg',
				'jetengine_id' => '',
			),
			'staff' => array(
				'label'        => 'Our Staff',
				'shortcode'    => '[wdcs_staff]',
				'preview'      => 'staff.svg',
				'jetengine_id' => '',
			),
		) );
	}

	private function get_post_types() {
		return apply_filters( 'wdcs_sections_post_types', array( 'page' ) );
	}

	public function __construct() {
		add_action( 'add_meta_boxes',        array( $this, 'register_meta_box' ) );
		add_action( 'save_post',             array( $this, 'save_meta' ), 10, 2 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	public function register_meta_box() {
		foreach ( $this->get_post_types() as $post_type ) {
			add_meta_box(
				'wdcs-smile-sections',
				'Smile Sections',
				array( $this, 'render_meta_box' ),
				$post_type,
				'side',
				'default'
			);
		}
	}

	public function render_meta_box( $post ) {
		$sections = $this->get_sections();
		$saved    = get_post_meta( $post->ID, self::META_KEY, true );
		$active   = is_array( $saved ) ? $saved : array();

		wp_nonce_field( self::NONCE_ACTION, self::NONCE_FIELD );
		?>
		<div class="wdcs-sections-picker">
			<?php foreach ( $sections as $slug => $section ) :
				$checked   = in_array( $slug, $active, true );
				$image_url = WDCS_PLUGIN_URL . 'assets/images/sections/' . $section['preview'];
			?>
			<label class="wdcs-section-item<?php echo $checked ? ' is-checked' : ''; ?>"
			       for="wdcs_section_<?php echo esc_attr( $slug ); ?>">

				<div class="wdcs-section-top">
					<input type="checkbox"
					       id="wdcs_section_<?php echo esc_attr( $slug ); ?>"
					       name="wdcs_active_sections[]"
					       value="<?php echo esc_attr( $slug ); ?>"
					       data-jetengine-id="<?php echo esc_attr( $section['jetengine_id'] ); ?>"
					       <?php checked( $checked ); ?>>
					<span class="wdcs-section-label"><?php echo esc_html( $section['label'] ); ?></span>
				</div>

				<div class="wdcs-section-preview">
					<img src="<?php echo esc_url( $image_url ); ?>"
					     alt="<?php echo esc_attr( $section['label'] ); ?>">
				</div>

				<code class="wdcs-section-shortcode"><?php echo esc_html( $section['shortcode'] ); ?></code>

			</label>
			<?php endforeach; ?>
		</div>
		<?php
	}

	public function save_meta( $post_id, $post ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}
		if ( ! isset( $_POST[ self::NONCE_FIELD ] ) ||
		     ! wp_verify_nonce( sanitize_key( $_POST[ self::NONCE_FIELD ] ), self::NONCE_ACTION ) ) {
			return;
		}
		$post_type_object = get_post_type_object( $post->post_type );
		if ( ! current_user_can( $post_type_object->cap->edit_post, $post_id ) ) {
			return;
		}
		if ( ! in_array( $post->post_type, $this->get_post_types(), true ) ) {
			return;
		}

		$valid_slugs = array_keys( $this->get_sections() );
		$submitted   = isset( $_POST['wdcs_active_sections'] ) ? (array) $_POST['wdcs_active_sections'] : array();
		$sanitized   = array_values( array_intersect( array_map( 'sanitize_key', $submitted ), $valid_slugs ) );

		update_post_meta( $post_id, self::META_KEY, $sanitized );
	}

	public function enqueue_assets( $hook ) {
		if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
			return;
		}
		$screen = get_current_screen();
		if ( ! $screen || ! in_array( $screen->post_type, $this->get_post_types(), true ) ) {
			return;
		}

		wp_enqueue_style(
			'wdcs-admin',
			WDCS_PLUGIN_URL . 'assets/css/wdcs-admin.css',
			array(),
			WDCS_VERSION
		);

		wp_enqueue_script(
			'wdcs-admin',
			WDCS_PLUGIN_URL . 'assets/js/wdcs-admin.js',
			array( 'jquery' ),
			WDCS_VERSION,
			true
		);

		$sections_js = array();
		foreach ( $this->get_sections() as $slug => $section ) {
			$sections_js[ $slug ] = array( 'jetengineId' => $section['jetengine_id'] );
		}

		wp_localize_script( 'wdcs-admin', 'wdcsSections', array(
			'sections' => $sections_js,
		) );
	}
}
