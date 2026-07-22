<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WDCS_Admin_Sections {

	const META_KEY     = '_wdcs_active_sections';
	const NONCE_ACTION = 'wdcs_save_sections';
	const NONCE_FIELD  = 'wdcs_sections_nonce';

	public static function get_all_sections() {
		return WDCS_Options_Page::get_settings();
	}

	private function get_post_types() {
		return apply_filters( 'wdcs_sections_post_types', array( 'patient-services' ) );
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
				'Select section(s)',
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
		<div class="wdcs-builder">

			<div class="wdcs-builder-available">
				<p class="wdcs-builder-heading">Sections</p>
				<?php foreach ( $sections as $slug => $section ) : ?>
				<div class="wdcs-avail-item"
				     data-slug="<?php echo esc_attr( $slug ); ?>"
				     data-label="<?php echo esc_attr( $section['label'] ); ?>"
				     data-jetengine="<?php echo esc_attr( $section['jetengine_id'] ); ?>">
					<?php if ( ! empty( $section['image_url'] ) ) : ?>
					<img src="<?php echo esc_url( $section['image_url'] ); ?>"
					     alt=""
					     class="wdcs-avail-thumb wdcs-js-enlarge"
					     data-full="<?php echo esc_url( $section['image_url'] ); ?>">
					<?php else : ?>
					<span class="wdcs-avail-no-thumb dashicons dashicons-format-image"></span>
					<?php endif; ?>
					<span class="wdcs-avail-label"><?php echo esc_html( $section['label'] ); ?></span>
					<button type="button" class="wdcs-add-to-active button button-small">+</button>
				</div>
				<?php endforeach; ?>
			</div>

			<div class="wdcs-builder-active">
				<p class="wdcs-builder-heading">
					Active <span class="wdcs-builder-hint">— drag to reorder</span>
				</p>
				<ul class="wdcs-active-list" id="wdcs-active-list">
					<?php foreach ( $active as $slug ) :
						if ( ! isset( $sections[ $slug ] ) ) continue;
						$section = $sections[ $slug ];
					?>
					<li class="wdcs-active-item"
					    data-slug="<?php echo esc_attr( $slug ); ?>"
					    data-jetengine="<?php echo esc_attr( $section['jetengine_id'] ); ?>">
						<span class="wdcs-drag-handle dashicons dashicons-menu"></span>
						<span class="wdcs-active-label"><?php echo esc_html( $section['label'] ); ?></span>
						<button type="button" class="wdcs-remove-active">&times;</button>
						<input type="hidden" name="wdcs_active_sections[]" value="<?php echo esc_attr( $slug ); ?>">
					</li>
					<?php endforeach; ?>
				</ul>
				<p class="wdcs-active-empty"<?php echo ! empty( $active ) ? ' style="display:none"' : ''; ?>>
					Click <strong>+</strong> above to add a section.
				</p>
			</div>

		</div>

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

		$valid_slugs = array_keys( self::get_all_sections() );
		$submitted   = isset( $_POST['wdcs_active_sections'] ) ? (array) $_POST['wdcs_active_sections'] : array();

		// Preserve order and allow duplicates; only reject unknown slugs.
		$sanitized = array();
		foreach ( $submitted as $slug ) {
			$slug = sanitize_key( $slug );
			if ( in_array( $slug, $valid_slugs, true ) ) {
				$sanitized[] = $slug;
			}
		}

		update_post_meta( $post_id, self::META_KEY, $sanitized );
	}

	public function enqueue_assets( $hook ) {
		if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
			return;
		}

		wp_enqueue_script( 'jquery-ui-sortable' );
		wp_enqueue_style(  'wdcs-admin', WDCS_PLUGIN_URL . 'assets/css/wdcs-admin.css', array(), WDCS_VERSION );
		wp_enqueue_script( 'wdcs-admin', WDCS_PLUGIN_URL . 'assets/js/wdcs-admin.js',  array( 'jquery', 'jquery-ui-sortable' ), WDCS_VERSION, true );

		wp_localize_script( 'wdcs-admin', 'wdcsSections', array( 'sections' => array() ) );
	}
}
