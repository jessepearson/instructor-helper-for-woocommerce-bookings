<?php
/**
 * WCBIH_Resource_Meta_Box class handles adding a meta box to resources to enable automation.
 * 
 * @package WC_Bookings_Instructor_Helper
 * @since   1.0.0
 * @version 1.0.0
 */
class WCBIH_Resource_Meta_Box {

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
			'wcbih_bookable_resource_toggle',
			__( 'Availability automation', 'wbrih' ),
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
			.wcbih_resource_meta_box label {
				font-weight: bold;
				display: inline-block;
				margin: 0 .75em 0 0;
			}

			.wcbih_resource_meta_box span {
				display: block;
				margin: .5em 0 0;
			}
		</style>
		<div class="woocommerce wcbih_resource_meta_box">
			<div class="panel-wrap">
				<div class="options_group">
					<?php
						woocommerce_wp_checkbox([
							'id'          => 'wcbih_resource_enabled',
							'label'       => __( 'Enable automatic availability', 'wcbih' ),
							'description' => __( 'This enables automation to create availability rules under products related to this resource.', 'wcbih' ),
							'value'       => wc_bool_to_string( get_post_meta( $post->ID, '_wcbih_resource_enabled', true ) ),
						]);
					?>
				</div>

				<div class="clear"></div>
			</div>
		</div>
		<?php
		wp_nonce_field( 'wcbih_bookable_resource_toggle_meta_box', 'wcbih_bookable_resource_toggle_meta_box_nonce' );
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
		if ( ! isset( $_POST['wcbih_bookable_resource_toggle_meta_box_nonce'] ) || ! wp_verify_nonce( $_POST['wcbih_bookable_resource_toggle_meta_box_nonce'], 'wcbih_bookable_resource_toggle_meta_box' ) ) {
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
		
		update_post_meta( $post_id, '_wcbih_resource_enabled', isset( $_POST['wcbih_resource_enabled'] ) );
	}
}

new WCBIH_Resource_Meta_Box();
