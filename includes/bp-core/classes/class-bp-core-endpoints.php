<?php

defined( 'ABSPATH' ) || exit;

/**
 * BuddyPress REST Core endpoint.
 *
 * @since 0.1.0
 */
class BP_REST_Core_Controller extends WP_REST_Controller {

	public function __construct( $member_type = false ) {
		$this->namespace = bp_rest_namespace() . '/' . bp_rest_version();
	}

	/**
	 * Register the routes.
	 *
	 * @since 0.1.0
	 */
	public function register_routes() {

		register_rest_route( $this->namespace, '/core', array(
			array(
				'methods' => WP_REST_Server::READABLE,
				'callback' => array( $this, 'get_items' ),
			),

		));

	}


	/**
	 * Retrieve members.
	 *
	 * @since 0.1.0
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Request List of activity object data.
	 */
	public function get_items( $request ) {
		return array( '' );
	}

}
