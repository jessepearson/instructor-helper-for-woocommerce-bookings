<?php
/**
 * Plugin Name: Instructor Helper For WooCommerce Bookings
 * Plugin URI: https://github.com/jessepearson/instructor-helper-for-woocommerce-bookings
 * Description: Allows automation to block availability time off on multiple products shared by a single resource with availability that's higher than 1.
 * Author: Jesse Pearson
 * Author URI: https://jessepearson.net
 * Text Domain: instructor-helper-wc-bookings
 * Version: 1.0.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Instructor_Helper_For_WC_Bookings' ) ) {
	/**
	 * Main class.
	 *
	 * @package Instructor_Helper_For_WC_Bookings
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	class Instructor_Helper_For_WC_Bookings {

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
			require_once( 'includes/class-ihwcb-dependencies.php' );
			
			// Check to see if we need to deactivate the plugin.
			$dependencies = new IHWCB_Dependencies( __FILE__ );
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
			require_once( 'includes/class-ihwcb-logger.php' );
			require_once( 'includes/class-ihwcb-update-availability.php' );
		
			// Files only the admin needs.
			if ( is_admin() ) {
				require_once( 'includes/class-ihwcb-resource-meta-box.php' );
				require_once( 'includes/class-ihwcb-settings.php' );
			}
		}
	}

	new Instructor_Helper_For_WC_Bookings();
}
