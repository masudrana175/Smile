<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WDCS_Admin_Sections {

	const META_KEY     = '_wdcs_active_sections';
	const NONCE_ACTION = 'wdcs_save_sections';
	const NONCE_FIELD  = 'wdcs_sections_nonce';

	/**
	 * Base section definitions (label only).
	 * Image URLs and JetEngine IDs come from the options page settings.
	 */
	public static function get_section_defaults() {
		return apply_filters( 'wdcs_sections_registry', array(
			'doctors_carousel' => array( 'label' => 'Doctors Carousel' ),
			'doctors_list'     => array( 'label' => 'Doctors List' ),
			'looking_for'      => array( 'label' => 'What Are You Looking For' ),
			'services'         => array( 'label' => 'Dentistry Services' ),
			'gallery'          => array( 'label' => 'Gallery Carousel' ),
			'staff'            => array( 'label' => 'Our Staff' ),
		) );
	}

	/**
	 * Merges base definitions with saved options (image_url, jetengine_id).
	 */
	public static function get_all_sections() {
		$defaults = self::get_section_defaults();
		$settings = WDCS_Options_Page::get_settings();
		$merged   = array();

		foreach ( $defaults as $slug => $def ) {
			$saved           = isset( $settings[ $slug ] ) ? $settings[ $slug ] : array();
			$merged[ $slug ] = array(
				'label'        => $def['label'],
				'image_url'    => ! empty( $saved['image_url'] )    ? $saved['image_url']    : '',
				'jetengine_id' => ! empty( $saved['jetengine_id'] ) ? $saved['jetengine_id'] : '',
			);
		}

		return $merged;
	}

	/**
	 * Post types that show the Smile Sections side panel.
	 * Defaults to all post types with a UI + patient-services.
	 */
	private function get_post_types() {
		$all = array_keys( get_post_types( array( 'show_ui' => true ) ) );
		$all[] = 'patient-services';
		return apply_filters( 'wdcs_sections_post_types', array_unique( $all ) );
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
		$sections = self::get_all_sections();
		$saved    = get_post_meta( $post->ID, self::META_KEY, true );
		$active   = is_array( $saved ) ? $saved : array();

		wp_nonce_field( self::NONCE_ACTION, self::NONCE_FIELD );
		?>
		<div class="wdcs-sections-picker">
			<?php foreach ( $sections as $slug => $section ) :
				$checked = in_array( $slug, $active, true );
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

				<?php if ( $section['image_url'] ) : ?>
				<div class="wdcs-section-preview">
					<img src="<?php echo esc_url( $section['image_url'] ); ?>"
					     alt="<?php echo esc_attr( $section['label'] ); ?>"
					     class="wdcs-section-thumb wdcs-js-enlarge"
					     data-full="<?php echo esc_url( $section['image_url'] ); ?>"
					     title="Click to enlarge">
				</div>
				<?php else : ?>
				<div class="wdcs-section-preview wdcs-section-no-image">
					<span class="dashicons dashicons-format-image"></span>
					<span>No preview — set one in <a href="<?php echo esc_url( admin_url( 'admin.php?page=wdcs-smile-sections' ) ); ?>">Smile Sections</a></span>
				</div>
				<?php endif; ?>

			</label>
			<?php endforeach; ?>
		</div>

		<!-- Inline lightbox for preview enlargement -->
		<div class="wdcs-lightbox" id="wdcs-lightbox" style="display:none;">
			<div class="wdcs-lightbox-overlay wdcs-js-close-lightbox"></div>
			<div class="wdcs-lightbox-inner">
				<button type="button" class="wdcs-lightbox-close wdcs-js-close-lightbox">&times;</button>
				<img src="" alt="" id="wdcs-lightbox-img">
			</div>
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
		if ( ! $post_type_object || ! current_user_can( $post_type_object->cap->edit_post, $post_id ) ) {
			return;
		}

		$valid_slugs = array_keys( self::get_section_defaults() );
		$submitted   = isset( $_POST['wdcs_active_sections'] ) ? (array) $_POST['wdcs_active_sections'] : array();
		$sanitized   = array_values( array_intersect( array_map( 'sanitize_key', $submitted ), $valid_slugs ) );

		update_post_meta( $post_id, self::META_KEY, $sanitized );
	}

	public function enqueue_assets( $hook ) {
		if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
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
		foreach ( self::get_all_sections() as $slug => $section ) {
			$sections_js[ $slug ] = array( 'jetengineId' => $section['jetengine_id'] );
		}

		wp_localize_script( 'wdcs-admin', 'wdcsSections', array(
			'sections' => $sections_js,
		) );
	}
}
