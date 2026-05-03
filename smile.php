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
		}

		private function hooks() {
			register_activation_hook( WDCS_PLUGIN_FILE, array( $this, 'activate' ) );
			register_deactivation_hook( WDCS_PLUGIN_FILE, array( $this, 'deactivate' ) );
		}

		public function activate() {
			// Activation logic.
		}

		public function deactivate() {
			// Deactivation logic.
		}
	}
}

function wdcs_smile() {
	return Smile::instance();
}

wdcs_smile();
