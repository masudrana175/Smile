<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WDCS_Options_Page {

	const OPTION_KEY = 'wdcs_sections_settings';

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

		$defaults = WDCS_Admin_Sections::get_section_defaults();
		$existing = self::get_settings();
		$saved    = array();

		foreach ( array_keys( $defaults ) as $slug ) {
			$saved[ $slug ] = array(
				'image_url'    => isset( $_POST['wdcs_image'][ $slug ] )
				                  ? esc_url_raw( $_POST['wdcs_image'][ $slug ] )
				                  : ( isset( $existing[ $slug ]['image_url'] ) ? $existing[ $slug ]['image_url'] : '' ),
				'jetengine_id' => isset( $_POST['wdcs_jeid'][ $slug ] )
				                  ? sanitize_text_field( $_POST['wdcs_jeid'][ $slug ] )
				                  : '',
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

		$sections = WDCS_Admin_Sections::get_all_sections();
		$updated  = isset( $_GET['updated'] ) && '1' === $_GET['updated'];
		?>
		<div class="wrap wdcs-options-wrap">
			<h1 class="wp-heading-inline">
				<span class="dashicons dashicons-layout" style="font-size:28px;margin-right:8px;vertical-align:middle;color:#1ab5b6;"></span>
				Smile Sections
			</h1>

			<?php if ( $updated ) : ?>
			<div class="notice notice-success is-dismissible" style="margin-top:16px;">
				<p><strong>Settings saved.</strong></p>
			</div>
			<?php endif; ?>

			<p class="description" style="margin:12px 0 24px;">
				Upload a preview image and enter the JetEngine meta box ID for each section.
				The preview helps editors identify which section to enable on their page.
			</p>

			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="wdcs_save_options">
				<?php wp_nonce_field( 'wdcs_save_options', 'wdcs_options_nonce' ); ?>

				<div class="wdcs-options-grid">
					<?php foreach ( $sections as $slug => $section ) :
						$image_url = ! empty( $section['image_url'] ) ? $section['image_url'] : '';
						$jeid      = ! empty( $section['jetengine_id'] ) ? $section['jetengine_id'] : '';
					?>
					<div class="wdcs-option-card">

						<div class="wdcs-option-preview-wrap">
							<?php if ( $image_url ) : ?>
							<img src="<?php echo esc_url( $image_url ); ?>"
							     alt="<?php echo esc_attr( $section['label'] ); ?>"
							     class="wdcs-option-thumb wdcs-js-enlarge"
							     data-full="<?php echo esc_url( $image_url ); ?>"
							     title="Click to enlarge">
							<?php else : ?>
							<div class="wdcs-option-placeholder">
								<span class="dashicons dashicons-format-image"></span>
								<span>No image</span>
							</div>
							<?php endif; ?>
						</div>

						<div class="wdcs-option-body">
							<h3 class="wdcs-option-title"><?php echo esc_html( $section['label'] ); ?></h3>

							<div class="wdcs-option-field">
								<label>Section Preview Image</label>
								<div class="wdcs-media-row">
									<input type="text"
									       name="wdcs_image[<?php echo esc_attr( $slug ); ?>]"
									       value="<?php echo esc_attr( $image_url ); ?>"
									       class="wdcs-image-url regular-text"
									       placeholder="https://">
									<button type="button"
									        class="button wdcs-upload-btn"
									        data-target="wdcs_image[<?php echo esc_attr( $slug ); ?>]">
										Upload / Select
									</button>
									<?php if ( $image_url ) : ?>
									<button type="button" class="button wdcs-remove-btn">Remove</button>
									<?php endif; ?>
								</div>
							</div>

							<div class="wdcs-option-field">
								<label for="wdcs_jeid_<?php echo esc_attr( $slug ); ?>">JetEngine Meta Box ID</label>
								<input type="text"
								       id="wdcs_jeid_<?php echo esc_attr( $slug ); ?>"
								       name="wdcs_jeid[<?php echo esc_attr( $slug ); ?>]"
								       value="<?php echo esc_attr( $jeid ); ?>"
								       class="regular-text"
								       placeholder="e.g. jet-engine-meta-box-doctors">
								<p class="description">
									Inspect the meta box <code>&lt;div id="..."&gt;</code> on the post edit screen to find this ID.
								</p>
							</div>
						</div>

					</div>
					<?php endforeach; ?>
				</div>

				<p class="submit" style="margin-top:24px;">
					<?php submit_button( 'Save Smile Sections', 'primary large', 'submit', false ); ?>
				</p>
			</form>
		</div>

		<!-- Lightbox modal -->
		<div class="wdcs-lightbox" id="wdcs-lightbox" style="display:none;">
			<div class="wdcs-lightbox-overlay wdcs-js-close-lightbox"></div>
			<div class="wdcs-lightbox-inner">
				<button type="button" class="wdcs-lightbox-close wdcs-js-close-lightbox">&times;</button>
				<img src="" alt="" id="wdcs-lightbox-img">
			</div>
		</div>
		<?php
	}

	public function enqueue_assets( $hook ) {
		if ( 'toplevel_page_wdcs-smile-sections' !== $hook ) {
			return;
		}
		wp_enqueue_media();
		wp_enqueue_style(  'wdcs-admin', WDCS_PLUGIN_URL . 'assets/css/wdcs-admin.css', array(), WDCS_VERSION );
		wp_enqueue_script( 'wdcs-admin', WDCS_PLUGIN_URL . 'assets/js/wdcs-admin.js',  array( 'jquery' ), WDCS_VERSION, true );
		wp_localize_script( 'wdcs-admin', 'wdcsSections', array( 'sections' => array() ) );
	}

	public static function get_settings() {
		return (array) get_option( self::OPTION_KEY, array() );
	}
}
