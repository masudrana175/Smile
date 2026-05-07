<?php
/**
 * Plugin Name: Smile
 * Plugin URI:  https://welchdentistry.wpenginepowered.com/
 * Description: Smile plugin for Welch Dentistry.
 * Version:     1.0.0
 * Author:      WelchDentistry
 * Author URI:  https://welchdentistry.wpenginepowered.com/
 * License:     GPL-2.0+
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: smile
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'WDCS_VERSION', '1.0.0' );
define( 'WDCS_PLUGIN_FILE', __FILE__ );
define( 'WDCS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WDCS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

if ( ! class_exists( 'Smile' ) ) {

	class Smile {

		private static $instance = null;

		public static function instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		private function __construct() {
			$this->includes();
			$this->hooks();
		}

		private function includes() {
			require_once WDCS_PLUGIN_DIR . 'includes/class-wdcs-doctors-shortcode.php';
			new WDCS_Doctors_Shortcode();

			require_once WDCS_PLUGIN_DIR . 'includes/class-wdcs-looking-shortcode.php';
			new WDCS_Looking_Shortcode();

			require_once WDCS_PLUGIN_DIR . 'includes/class-wdcs-services-shortcode.php';
			new WDCS_Services_Shortcode();
		}

		private function hooks() {
			add_action( 'wp_enqueue_scripts', array( $this, 'register_assets' ) );
			register_activation_hook( WDCS_PLUGIN_FILE, array( $this, 'activate' ) );
			register_deactivation_hook( WDCS_PLUGIN_FILE, array( $this, 'deactivate' ) );
		}

		public function register_assets() {
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
				'wdcs-smile',
				WDCS_PLUGIN_URL . 'assets/css/wdcs-smile.css',
				array( 'slick-css' ),
				WDCS_VERSION
			);
			wp_register_script(
				'wdcs-smile',
				WDCS_PLUGIN_URL . 'assets/js/wdcs-smile.js',
				array( 'jquery', 'slick-js' ),
				WDCS_VERSION,
				true
			);
		}

		public function activate() {}
		public function deactivate() {}
	}
}

function wdcs_smile() {
	return Smile::instance();
}

wdcs_smile();
