<?php
/**
 * WCBIH_Logger is our logger for the plugin.
 *
 * @package WC_Bookings_Instructor_Helper
 * @since   1.0.0
 * @version 1.0.0
 */
class WCBIH_Logger {

	/**
	 * It's log, it's better than bad, it's good!
	 * 
	 * @since   1.0.0
	 * @version 1.0.0
	 * @param   string $log String to be logged.
	 */
	static function log( $log ) {

		if ( ! get_option( 'wcbih_logging_enabled', false ) ) {
			return;
		}
		
		// Uses WooCommerce's logger.
		$logger = wc_get_logger();
		$logger->debug( $log, [ 'source' => 'wcbih-logging' ] );
	}
}
