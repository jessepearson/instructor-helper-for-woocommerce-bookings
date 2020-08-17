<?php
/**
 * WCBIH_Settings class handles settings for the plugin.
 *
 * @package WC_Bookings_Instructor_Helper
 * @since   1.0.0
 * @version 1.0.0
 */
class WCBIH_Settings {

	/**
	 * Constructor.
	 * 
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function __construct() {
		add_action( 'admin_menu', [ $this, 'admin_menu' ], 99 );
	}
	
	/**
	 * Function to add the helper option to the Bookings menu in the admin.
	 * 
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function admin_menu() {
		add_submenu_page( 'edit.php?post_type=wc_booking', __( 'Instructor Helper', 'wbrih' ), __( 'Instructor Helper', 'wbrih' ), 'edit_wc_bookings', 'wcbih_settings', [ $this, 'wcbih_settings' ] );
	}

	/**
	 * Renders the settings page.
	 * 
	 * @since   1.0.0
	 * @version 1.0.0 
	 */
	public function wcbih_settings() {

		?>
		<div class="wrap">
			<style>

				#wcbih_settings label {
					font-size: 1.2em;
					font-weight: bold;
					display: inline-block;
					min-width: 10em;
					line-height: 1.2em;
				}

				#wcbih_settings span {
					font-size: 1.1em;
				}
			</style>
		<?php
		// If they cannot edit bookings, they shouldn't be here. 
		if ( ! current_user_can( 'edit_wc_bookings' ) ) {
			esc_attr_e( 'Sorry, you are not allowed to access this page.', 'wbrih' );
		} else {

		// Process anything that was saved.
		$this->process_settings();

		?>
			<h2><?php _e( 'Bookings Resource Instructor Helper Settings', 'wbrih' ); ?></h2>
			<div id="content">
				<form method="post" action="" id="wcbih_settings">
					<div id="poststuff">
						<div class="inside">
							<p class="form-field wcbih_logging_enabled_field">
								<label for="wcbih_logging_enabled"><?php _e( 'Enable logging', 'wbrih' ); ?></label>
								<input type="checkbox" class="checkbox" style="" name="wcbih_logging_enabled" id="wcbih_logging_enabled" value="yes" <?php checked( 'yes', get_option( 'wcbih_logging_enabled', false ), true );?>> 
								<span class="description"><?php _e( 'Enables logging for debugging purposes.', 'wbrih' ); ?></span>
							</p>

						</div>
					</div>
					<p class="submit">
						<input type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e( 'Save Changes', 'wcbih' ); ?>" />
						<?php wp_nonce_field( 'submit_wcbih_settings', 'submit_wcbih_settings_nonce' ); ?>
					</p>
				</form>
			</div>
		<?php } ?>
		</div>
		<?php
	}

	/**
	 * Processes the settings that were saved.
	 * 
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	private function process_settings() {
		// We need to check a lot before saving.
		if (
			isset( $_POST['Submit'] )
			&& isset( $_POST['submit_wcbih_settings_nonce'] )
			&& wp_verify_nonce( wc_clean( wp_unslash( $_POST['submit_wcbih_settings_nonce'] ) ), 'submit_wcbih_settings' )
			&& current_user_can( 'edit_wc_bookings' )
		) {
			// Save the field values.
			$logging = ( null === $_POST['wcbih_logging_enabled'] ) ? 'no' : 'yes';
			update_option( 'wcbih_logging_enabled', $logging );
		}
	}
}

new WCBIH_Settings();
