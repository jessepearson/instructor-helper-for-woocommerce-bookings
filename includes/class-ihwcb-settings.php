<?php
/**
 * IHWCB_Settings class handles settings for the plugin.
 *
 * @package Instructor_Helper_For_WC_Bookings
 * @since   1.0.0
 * @version 1.0.0
 */
class IHWCB_Settings {

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
		add_submenu_page( 'edit.php?post_type=wc_booking', __( 'Instructor Helper', 'instructor-helper-wc-bookings' ), __( 'Instructor Helper', 'instructor-helper-wc-bookings' ), 'edit_wc_bookings', 'ihwcb_settings', [ $this, 'ihwcb_settings' ] );
	}

	/**
	 * Renders the settings page.
	 * 
	 * @since   1.0.0
	 * @version 1.0.0 
	 */
	public function ihwcb_settings() {

		?>
		<div class="wrap">
			<style>

				#ihwcb_settings label {
					font-size: 1.2em;
					font-weight: bold;
					display: inline-block;
					min-width: 10em;
					line-height: 1.2em;
				}

				#ihwcb_settings span {
					font-size: 1.1em;
				}
			</style>
		<?php
		// If they cannot edit bookings, they shouldn't be here. 
		if ( ! current_user_can( 'edit_wc_bookings' ) ) {
			esc_attr_e( 'Sorry, you are not allowed to access this page.', 'instructor-helper-wc-bookings' );
		} else {

		// Process anything that was saved.
		$this->process_settings();

		?>
			<h2><?php _e( 'Instructor Helper Settings', 'instructor-helper-wc-bookings' ); ?></h2>
			<div id="content">
				<form method="post" action="" id="ihwcb_settings">
					<div id="poststuff">
						<div class="inside">
							<p class="form-field ihwcb_logging_enabled_field">
								<label for="ihwcb_logging_enabled"><?php _e( 'Enable logging', 'instructor-helper-wc-bookings' ); ?></label>
								<input type="checkbox" class="checkbox" style="" name="ihwcb_logging_enabled" id="ihwcb_logging_enabled" value="yes" <?php checked( 'yes', get_option( 'ihwcb_logging_enabled', false ), true );?>> 
								<span class="description"><?php _e( 'Enables logging for debugging purposes.', 'instructor-helper-wc-bookings' ); ?></span>
							</p>

						</div>
					</div>
					<p class="submit">
						<input type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e( 'Save Changes', 'instructor-helper-wc-bookings' ); ?>" />
						<?php wp_nonce_field( 'submit_ihwcb_settings', 'submit_ihwcb_settings_nonce' ); ?>
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
			&& isset( $_POST['submit_ihwcb_settings_nonce'] )
			&& wp_verify_nonce( wc_clean( wp_unslash( $_POST['submit_ihwcb_settings_nonce'] ) ), 'submit_ihwcb_settings' )
			&& current_user_can( 'edit_wc_bookings' )
		) {
			// Save the field values.
			$logging = ( null === $_POST['ihwcb_logging_enabled'] ) ? 'no' : 'yes';
			update_option( 'ihwcb_logging_enabled', $logging );
		}
	}
}

new IHWCB_Settings();
