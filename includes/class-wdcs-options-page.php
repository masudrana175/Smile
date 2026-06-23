<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WDCS_Options_Page {

	const OPTION_KEY = 'wdcs_sections_settings';

	/**
	 * Default sections seeded on first use.
	 */
	private static function get_defaults() {
		return array(
			array( 'label' => 'Doctors Carousel',        'image_url' => '', 'jetengine_id' => '' ),
			array( 'label' => 'Doctors List',             'image_url' => '', 'jetengine_id' => '' ),
			array( 'label' => 'What Are You Looking For', 'image_url' => '', 'jetengine_id' => '' ),
			array( 'label' => 'Dentistry Services',       'image_url' => '', 'jetengine_id' => '' ),
			array( 'label' => 'Gallery Carousel',         'image_url' => '', 'jetengine_id' => '' ),
			array( 'label' => 'Our Staff',                'image_url' => '', 'jetengine_id' => '' ),
		);
	}

	public function __construct() {
		add_action( 'admin_menu',            array( $this, 'register_menu' ) );
		add_action( 'admin_post_wdcs_save_options', array( $this, 'handle_save' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	public function register_menu() {
		add_menu_page(
			'Smile Sections',
			'Smile Sections',
			'manage_options',
			'wdcs-smile-sections',
			array( $this, 'render_page' ),
			'dashicons-layout',
			60
		);
	}

	public function handle_save() {
		if ( ! isset( $_POST['wdcs_options_nonce'] ) ||
		     ! wp_verify_nonce( sanitize_key( $_POST['wdcs_options_nonce'] ), 'wdcs_save_options' ) ) {
			wp_die( 'Security check failed.' );
		}
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Permission denied.' );
		}

		$rows   = isset( $_POST['wdcs_sections'] ) ? (array) $_POST['wdcs_sections'] : array();
		$saved  = array();

		foreach ( $rows as $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}
			$label = sanitize_text_field( isset( $row['label'] ) ? $row['label'] : '' );
			if ( '' === $label ) {
				continue; // Skip blank rows.
			}
			$slug = str_replace( '-', '_', sanitize_title( $label ) );
			if ( '' === $slug ) {
				continue;
			}
			// If slug already exists (duplicate label), append a counter.
			$base = $slug;
			$i    = 2;
			while ( isset( $saved[ $slug ] ) ) {
				$slug = $base . '_' . $i++;
			}
			$saved[ $slug ] = array(
				'label'        => $label,
				'image_url'    => isset( $row['image_url'] )    ? esc_url_raw( $row['image_url'] )            : '',
				'jetengine_id' => isset( $row['jetengine_id'] ) ? sanitize_text_field( $row['jetengine_id'] ) : '',
			);
		}

		update_option( self::OPTION_KEY, $saved );

		wp_safe_redirect( add_query_arg( array(
			'page'    => 'wdcs-smile-sections',
			'updated' => '1',
		), admin_url( 'admin.php' ) ) );
		exit;
	}

	public function render_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$sections = self::get_settings();
		$updated  = isset( $_GET['updated'] ) && '1' === $_GET['updated'];
		$index    = 0;
		?>
		<div class="wrap wdcs-options-wrap">

			<h1>
				<span class="dashicons dashicons-layout" style="font-size:26px;vertical-align:middle;margin-right:8px;color:#1ab5b6;"></span>
				Smile Sections
			</h1>

			<?php if ( $updated ) : ?>
			<div class="notice notice-success is-dismissible" style="margin-top:16px;">
				<p><strong>Settings saved.</strong></p>
			</div>
			<?php endif; ?>

			<p class="description" style="margin:10px 0 24px;font-size:14px;">
				Add unlimited sections, upload a preview image, and set the JetEngine meta box ID for each.
				Editors can enable sections per post from the <strong>Select section(s)</strong> side panel.
			</p>

			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="wdcs_save_options">
				<?php wp_nonce_field( 'wdcs_save_options', 'wdcs_options_nonce' ); ?>

				<div id="wdcs-sections-list">
					<?php foreach ( $sections as $slug => $section ) :
						$img  = ! empty( $section['image_url'] )    ? $section['image_url']    : '';
						$jeid = ! empty( $section['jetengine_id'] ) ? $section['jetengine_id'] : '';
					?>
					<div class="wdcs-section-row" data-index="<?php echo esc_attr( $index ); ?>">
						<?php echo $this->row_html( $index, $section['label'], $img, $jeid ); ?>
					</div>
					<?php $index++; endforeach; ?>
				</div>

				<div class="wdcs-add-row">
					<button type="button" id="wdcs-add-section" class="button button-secondary">
						<span class="dashicons dashicons-plus-alt2" style="vertical-align:middle;margin-right:4px;"></span>
						Add Section
					</button>
				</div>

				<p class="submit" style="margin-top:24px;">
					<?php submit_button( 'Save Smile Sections', 'primary large', 'submit', false ); ?>
				</p>
			</form>
		</div>

		<!-- Row template for JS cloning -->
		<script type="text/html" id="wdcs-row-template">
			<?php echo $this->row_html( '__IDX__', '', '', '' ); ?>
		</script>

		<!-- Lightbox -->
		<div class="wdcs-lightbox" id="wdcs-lightbox" style="display:none;">
			<div class="wdcs-lightbox-overlay wdcs-js-close-lightbox"></div>
			<div class="wdcs-lightbox-inner">
				<button type="button" class="wdcs-lightbox-close wdcs-js-close-lightbox">&times;</button>
				<img src="" alt="" id="wdcs-lightbox-img">
			</div>
		</div>
		<?php
	}

	/**
	 * Generates the inner HTML for a single section row.
	 * Used both on render and as a JS clone template.
	 */
	private function row_html( $index, $label, $image_url, $jetengine_id ) {
		$idx = esc_attr( $index );
		$img = esc_url( $image_url );
		ob_start();
		?>
		<div class="wdcs-row-thumb">
			<?php if ( $image_url ) : ?>
			<img src="<?php echo $img; ?>"
			     class="wdcs-row-thumb-img wdcs-js-enlarge"
			     data-full="<?php echo $img; ?>"
			     alt="Preview">
			<?php else : ?>
			<div class="wdcs-row-thumb-placeholder">
				<span class="dashicons dashicons-format-image"></span>
			</div>
			<?php endif; ?>
		</div>

		<div class="wdcs-row-fields">
			<input type="text"
			       name="wdcs_sections[<?php echo $idx; ?>][label]"
			       value="<?php echo esc_attr( $label ); ?>"
			       placeholder="Section Name"
			       class="regular-text">
			<input type="text"
			       name="wdcs_sections[<?php echo $idx; ?>][jetengine_id]"
			       value="<?php echo esc_attr( $jetengine_id ); ?>"
			       placeholder="JetEngine Meta Box ID"
			       class="regular-text">
		</div>

		<div class="wdcs-row-actions">
			<input type="hidden"
			       name="wdcs_sections[<?php echo $idx; ?>][image_url]"
			       value="<?php echo $img; ?>"
			       class="wdcs-image-url">
			<button type="button"
			        class="button wdcs-upload-btn"
			        data-index="<?php echo $idx; ?>">
				Upload Image
			</button>
			<button type="button"
			        class="button wdcs-remove-section"
			        title="Remove this section">
				<span class="dashicons dashicons-trash"></span>
			</button>
		</div>
		<?php
		return ob_get_clean();
	}

	public function enqueue_assets( $hook ) {
		if ( 'toplevel_page_wdcs-smile-sections' !== $hook ) {
			return;
		}
		wp_enqueue_media();
		wp_enqueue_style(  'wdcs-admin', WDCS_PLUGIN_URL . 'assets/css/wdcs-admin.css', array(), WDCS_VERSION );
		wp_enqueue_script( 'wdcs-admin', WDCS_PLUGIN_URL . 'assets/js/wdcs-admin.js',  array( 'jquery' ), WDCS_VERSION, true );
		wp_localize_script( 'wdcs-admin', 'wdcsSections', array(
			'sections'     => array(),
			'sectionCount' => count( self::get_settings() ),
		) );
	}

	/**
	 * Returns all saved sections as an associative array keyed by slug.
	 * Seeds defaults on first call if options are empty.
	 */
	public static function get_settings() {
		$saved = get_option( self::OPTION_KEY, null );

		if ( null === $saved ) {
			// First time: seed defaults and save.
			$seeded = array();
			foreach ( self::get_defaults() as $def ) {
				$slug           = str_replace( '-', '_', sanitize_title( $def['label'] ) );
				$seeded[ $slug ] = $def;
			}
			update_option( self::OPTION_KEY, $seeded );
			return $seeded;
		}

		return is_array( $saved ) ? $saved : array();
	}
}
