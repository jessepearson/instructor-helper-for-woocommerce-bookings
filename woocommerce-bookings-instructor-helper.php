<?php
/**
 * Plugin Name: WooCommerce Bookings Instructor Helper
 * Plugin URI: 
 * Description: Allows automation to block availability time off on multiple products shared by a single resource with availability that's higher than 1.
 * Author: Jesse Pearson
 * Author URI: https://jessepearson.net
 * Text Domain: wcbih
 * Version: 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WC_Bookings_Instructor_Helper' ) ) {
	/**
	 * Main class.
	 *
	 * @package WC_Bookings_Instructor_Helper
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	class WC_Bookings_Instructor_Helper {

		/**
		 * Constructor.
		 * 
		 * @since   1.0.0
		 * @version 1.0.0
		 */
		public function __construct() {
			add_action( 'init', [ $this, 'check_dependencies' ] );
		}

		/**
		 * Checks dependencies
		 * 
		 * @since   1.0.0
		 * @version 1.0.0
		 */
		public function check_dependencies() {
			// Get dependencies class file.
			require_once( 'includes/class-wcbih-dependencies.php' );
			
			// Check to see if we need to deactivate the plugin.
			$dependencies = new WCBIH_Dependencies( __FILE__ );
			$deactivated  = $dependencies->maybe_deactivate_plugin();

			// If we didn't deactivate, include everything else.
			if ( ! $deactivated ) {
				$this->includes();
			}
		}

		/**
		 * Includes needed files.
		 * 
		 * @since   1.0.0
		 * @version 1.0.0
		 */
		public function includes() {
			// Files we always need.
			require_once( 'includes/class-wcbih-logger.php' );
			require_once( 'includes/class-wcbih-update-availability.php' );
		
			// Files only the admin needs.
			if ( is_admin() ) {
				require_once( 'includes/class-wcbih-resource-meta-box.php' );
				require_once( 'includes/class-wcbih-settings.php' );
			}
		}
	}

	new WC_Bookings_Instructor_Helper();
}
