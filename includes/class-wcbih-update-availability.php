<?php
/**
 * WCBIH_Update_Availability class does all the heavy lifting.
 *
 * @package WC_Bookings_Instructor_Helper
 * @since   1.0.0
 * @version 1.0.0
 */
class WCBIH_Update_Availability {

	/**
	 * Booking.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	private $booking = null;

	/**
	 * The availability the booking takes up.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	private $booking_availability = null;

	/**
	 * Product related to the booking.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	private $booking_product = null;

	/**
	 * Resource related to the booking.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	private $resource = null;

	/**
	 * Products related to the resource.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	private $products = null;


	/**
	 * Constructor.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function __construct() {
		// Maybe add product availability for new bookings.
		add_action( 'woocommerce_new_booking', [ $this, 'maybe_add_product_availability' ] );
		add_action( 'untrash_post', [ $this, 'maybe_add_product_availability' ] );

		// Maybe update product availability for existing bookings.
		add_action( 'woocommerce_booking_process_meta', [ $this, 'maybe_update_product_availability' ], 20 );

		// Maybe remove product availability for bookings being cancelled, deleted, etc. 
		add_action( 'wp_trash_post', [ $this, 'maybe_remove_product_availability' ], 20 );
		add_action( 'woocommerce_booking_was-in-cart', [ $this, 'maybe_remove_product_availability' ], 20 );
		add_action( 'before_delete_post', [ $this, 'maybe_remove_product_availability' ], 20 );
		add_action( 'woocommerce_booking_cancelled', [ $this, 'maybe_remove_product_availability' ], 20 );
	}

	/**
	 * Triggers action to start adding availability to a product based on a booking.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 * @param   int|str $booking_id The booking ID to work on.
	 */
	public function maybe_add_product_availability( $booking_id ) {
		// Hand off to worker.
		$this->maybe_action_product_availability( 'add', $booking_id );
	}

	/**
	 * Triggers action to start updating availability to a product based on a booking.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 * @param   int|str $booking_id The booking ID to work on.
	 */
	public function maybe_update_product_availability( $booking_id ) {
		// Hand off to worker.
		$this->maybe_action_product_availability( 'update', $booking_id );
	}

	/**
	 * Triggers action to start removing availability to a product based on a booking.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 * @param   int|str $booking_id The booking ID to work on.
	 */
	public function maybe_remove_product_availability( $booking_id ) {

		if ( ! $booking_id ) {
			// No logging due to this is a common hook and may be called a lot. 
			return;
		}

		if ( 'wc_booking' !== get_post_type( $booking_id ) ) {
			// No logging due to this is a common hook and may be called a lot. 
			return;
		}

		// Hand off to worker.
		$this->maybe_action_product_availability( 'remove', $booking_id );
	}

	/**
	 * Main worker to add/update/remove availability from a product.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 * @param   str     $action     The action to be taken.
	 * @param   int|str $booking_id The booking id to work on.
	 * @return  null
	 */
	public function maybe_action_product_availability( $action, $booking_id ) {

		// Log what's been started.
		WCBIH_Logger::log( 'Performing '. $action .' on product availability based on booking: '. $booking_id );

		// Hand off to initial processor. If there's an error, log it and exit.
		$processing = $this->start_processing_availability( $booking_id );
		if ( is_wp_error( $processing ) ) {
			WCBIH_Logger::log( $processing->get_error_message() );
			return;
		}

		// Default existing availability. Variable is used to update/remove.
		$existing_availability = null;

		if ( 'add' === $action ) {
			// If we are adding, we add the availability rules to the booking itself for possible future processing.
			update_post_meta( $this->booking->get_id(), '_wcbih_availability_rules', $this->booking_availability );
		}


		if ( 'update' === $action ) {
			// Get the existing availability previously set on the booking and log them.
			$existing_availability = get_post_meta( $this->booking->get_id(), '_wcbih_availability_rules', true );
			WCBIH_Logger::log( 'Previous availability rules are: '. print_r( $existing_availability, true ) );

			// Check if the new availability matches the previous availability.
			if ( $this->is_rule_exact( $this->booking_availability['time'], $existing_availability, 'time' ) ) {
			 	// If it does, log it and exit, we don't need to update anything. 
			 	WCBIH_Logger::log( 'Availability unchanged, exiting.' );
				return;
			}
		}

		if ( 'remove' === $action ) {
			// Get the existing availability so we know what to remove.
			$existing_availability = $this->booking_availability;
		}

		// Hand off to processor, then log that we are done.
		$this->process_product_availability( $action, $existing_availability );
		WCBIH_Logger::log( 'Finished performing '. $action .' on product availability based on booking: '. $booking_id );
	}

	/**
	 * Beginning processor that sets up many of the variables for the object.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 * @param   int|str $booking_id The booking ID to work on.
	 * @return  obj|null WP_Error object on error or null.
	 */
	public function start_processing_availability( $booking_id ) {

		// If we have not booking id, return an error. 
		if ( empty( $booking_id ) ) {
			return new WP_Error( 'no_booking_id', 'No booking id found, so exiting.' );
		}

		// Get the booking object and then the booking product object, log it. 
		$this->booking         = get_wc_booking( $booking_id );
		$this->booking_product = get_wc_product_booking( $this->booking->get_product_id() );
		WCBIH_Logger::log( 'Related product id: '. $this->booking->get_product_id() );

		// Get the resource remove the product, if there's an error, return it. 
		$this->resource = $this->get_product_resource( $this->booking_product );
		if ( is_wp_error( $this->resource ) ) {
			return $resource;
		}

		// Get all the products related to the resource.
		$this->products = $this->get_resource_products( $this->resource );

		// If we only have one product, we have nothing to work on, throw error.
		if ( 1 >= count( $this->products ) ) {
			return new WP_Error( 'one_product', 'Only one product related to booking resource, exiting.' );
		}

		// Log how many products we're working on. 
		WCBIH_Logger::log( 'Total products found related to resource: '. count( $this->products ) );

		// Get the booking's availability that it is using. 
		$this->booking_availability = $this->get_booking_availability_rules( $this->booking );
	}

	/**
	 * Gets the resource related to the product, or throws error.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 * @param   obj $product A product object from Bookings.
	 * @return  obj A single resource object or WP_Error object.
	 */
	private function get_product_resource( $product ) {

		// Get the resources related to the product. 
		$resources = $product->get_resources();

		// If we have no resources, or more than one resource, throw error.
		if ( 0 === count( $resources ) ) {
			return new WP_Error( 'no_resources', 'No resources found on product, exiting.' );
		} 

		if ( 1 < count( $resources ) ) {
			return new WP_Error( 'multiple_resources', 'There is more than one resource, exiting.' );
		}

		// We have our resource.
		$resource = $resources[0];

		// Should we be working on this resource?
		if ( ! get_post_meta( $resource->get_id(), '_wcbih_resource_enabled', true ) ) {
			return new WP_Error( 'not_enabled', 'Resource '. $resource->get_id() .' does not have automation enabled, exiting.' );
		}

		return $resource;
	}

	/**
	 * Updates the availability rules on the resource's products.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 * @param   $action The action being taken. 
	 * @param   $existing_availability The existing availabilty on the booking, if any. 
	 */
	private function process_product_availability( $action, $existing_availability = null ) {

		// Loop through each of the products. 
		foreach( $this->products as $product ) {

			// Log which product is being worked on. 
			WCBIH_Logger::log( 'Beginning work on product id: '. $product );

			// If the product is the one the booking was made on, log it, and skip it. 
			if ( $this->booking_product->get_id() === $product ) {
				WCBIH_Logger::log( 'Product is the one related to booking, skipping.' );
				continue;
			}

			// Get the product object, the duration unit it uses, and its current availability.
			$product      = get_wc_product_booking( $product );
			$unit         = ( $this->booking->is_all_day() || 'day' === $product->get_duration_unit() || 'month' === $product->get_duration_unit() ) ? 'day' : 'time';
			$availability = $product->get_availability();

			// Log the product's data. 
			WCBIH_Logger::log( 'Product\'s duration unit is: '. $unit );
			WCBIH_Logger::log( 'Product\'s availability is: '. print_r( $availability, true ) );

			// For updating and removing, we go through each availability rule from the product. 
			if ( in_array( $action, [ 'update', 'remove' ] ) ) {

				foreach ( $availability as $key => $rule ) {

					if ( $this->is_rule_exact( $rule, $existing_availability, $unit ) ) {
					 	// If the rule exists on the product, we remove it from the array (and the product), and log.
					 	// If the booking is all day or day based, this will remove then readd the same rule. 
					 	WCBIH_Logger::log( 'Previous availability rule exists on product, removing it.' );
						unset( $availability[ $key ] );
					}
				}
			}

			// For updating and adding, we go through each availability rule from the product. 
			if ( in_array( $action, [ 'update', 'add' ] ) ) {

				foreach ( $availability as $key => $rule ) {

					if ( $this->is_rule_exact( $rule, $this->booking_availability, $unit ) ) {
					 	// If the rule exists on the product, we log it and move on to the next productt.
					 	WCBIH_Logger::log( 'Exact availability rule exists on product, moving on.' );
						continue 2;
					}

				}

				// We log what availability we are adding, and add it to availability array.
				WCBIH_Logger::log( 'Adding availability to product:'. print_r( $this->booking_availability[ $unit ], true ) );
				$availability[] = $this->booking_availability[ $unit ];
			}

			// Set the availability on the product and save it via native functions.
			$product->set_availability( $availability );
			$product->save();
		}

		// Update the availability meta on the booking itself. 
		update_post_meta( $this->booking->get_id(), '_wcbih_availability_rules', $this->booking_availability );
	}

	/**
	 * Checks for exact matches of rules. 
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 * @param   $rule The rule we are checking.
	 * @param   $availability The existing availabilty on the booking, if any. 
	 * @param   $unit The unit the product uses. 
	 * @return  bool
	 */
	public function is_rule_exact( $rule, $availability, $unit ) {

		// Log the rule and availability we're working on. 
		WCBIH_Logger::log( 'Checking for exact match:' );
		WCBIH_Logger::log( '> Rule: '. print_r( $rule, true ) );
		WCBIH_Logger::log( '> Availability: '. print_r( $availability[ $unit ], true ) );

		// Go through each rule to see if the values match existing availability. 
		foreach ( $rule as $key => $value ) {
			if ( $availability[ $unit ][ $key ] !== $value ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Returns an array of the availability the booking is taking up. 
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 * @param   $booking The booking object we're working on.
	 * @return  arr Array of availability the booking is taking up. 
	 */
	public function get_booking_availability_rules( $booking ) {

		// We get the availability as a day unit. 
		$availability['day'] = [
			'type'      => 'custom',
			'bookable'  => 'no',
			'priority'  => (int) 1,
			'from'      => date( 'Y-m-d', $booking->get_start() ),
			'to'        => date( 'Y-m-d', $booking->get_end() ),
		];

		// And the availability as a time unit. 
		$availability['time'] = [
			'type'      => 'custom:daterange',
			'bookable'  => 'no',
			'priority'  => (int) 1,
			'from'      => date( 'H:i', $booking->get_start() ),
			'to'        => date( 'H:i', $booking->get_end() ),
			'from_date' => date( 'Y-m-d', $booking->get_start() ),
			'to_date'   => date( 'Y-m-d', $booking->get_end() ),
		];

		// We log what we've found and return it. 
		WCBIH_Logger::log( 'Booking availability rules are: '. print_r( $availability, true ) );
		return $availability;
	}

	/**
	 * Returns an array of the product ids related to the resource. 
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 * @param   $resource The resource object we're working on.
	 * @return  arr Array product ids related to the resource.
	 */
	public function get_resource_products( $resource ) {
		global $wpdb;

		// Query the relationships table to determine which products are related to the resource.
		$product_ids = wp_parse_id_list( $wpdb->get_col( $wpdb->prepare( "
			SELECT product_id
			FROM {$wpdb->prefix}wc_booking_relationships AS relationships
			WHERE relationships.resource_id = %d
			ORDER BY sort_order ASC
		", $resource->get_id() ) ) );

		return $product_ids;
	}
}

new WCBIH_Update_Availability();
