<?php
/**
 * IHWCB_Resource_Meta_Box class handles adding a meta box to resources to enable automation.
 * 
 * @package Instructor_Helper_For_WC_Bookings
 * @since   1.0.0
 * @version 1.0.0
 */
class IHWCB_Resource_Meta_Box {

	/**
	 * Constructor.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function __construct() {
		add_action( 'save_post', [ $this, 'meta_box_save' ], 10, 2 );
		add_action( 'add_meta_boxes', [ $this, 'add_meta_box' ], 50 );
	}

	/**
	 * Adds meta box to resource page.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function add_meta_box() {

		add_meta_box(
			'ihwcb_bookable_resource_toggle',
			__( 'Instructor Helper', 'instructor-helper-wc-bookings' ),
			[ $this, 'meta_box_inner' ],
			[ 'bookable_resource' ],
			'side'
		);
	}

	/**
	 * Displays the meta box.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 * @param   obj $post The post object the meta box is added to.
	 */
	public function meta_box_inner( $post ) {

		?>
		<style>
			.ihwcb_resource_meta_box label {
				font-weight: bold;
				display: inline-block;
				margin: 0 .75em 0 0;
			}

			.ihwcb_resource_meta_box span {
				display: block;
				margin: .5em 0 0;
			}
		</style>
		<div class="woocommerce ihwcb_resource_meta_box">
			<div class="panel-wrap">
				<div class="options_group">
					<?php
						woocommerce_wp_checkbox([
							'id'          => 'ihwcb_resource_enabled',
							'label'       => __( 'Enable automatic availability', 'instructor-helper-wc-bookings' ),
							'description' => __( 'This enables automation to create availability rules under products related to this resource.', 'instructor-helper-wc-bookings' ),
							'value'       => wc_bool_to_string( get_post_meta( $post->ID, '_ihwcb_resource_enabled', true ) ),
						]);
					?>
				</div>

				<div class="clear"></div>
			</div>
		</div>
		<?php
		wp_nonce_field( 'ihwcb_bookable_resource_toggle_meta_box', 'ihwcb_bookable_resource_toggle_meta_box_nonce' );
	}

	/**
	 * Handles saving the meta box data.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 * @param   int|str $post_id The post id the meta box is added to.
	 * @param   obj     $post    The post object the meta box is added to.
	 */
	public function meta_box_save( $post_id, $post ) {
		if ( ! isset( $_POST['ihwcb_bookable_resource_toggle_meta_box_nonce'] ) || ! wp_verify_nonce( $_POST['ihwcb_bookable_resource_toggle_meta_box_nonce'], 'ihwcb_bookable_resource_toggle_meta_box' ) ) {
			return $post_id;
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $post_id;
		}
		if ( empty( $_POST['post_ID'] ) || intval( $_POST['post_ID'] ) !== $post_id ) {
			return $post_id;
		}
		if ( 'bookable_resource' !== $post->post_type ) {
			return $post_id;
		}
		
		update_post_meta( $post_id, '_ihwcb_resource_enabled', isset( $_POST['ihwcb_resource_enabled'] ) );
	}
}

new IHWCB_Resource_Meta_Box();
