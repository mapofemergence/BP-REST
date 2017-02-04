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
				'permission_callback' => array( $this, 'core_api_permissions' ),
			),
			'schema' => array( $this, 'get_schema' ),

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

		$core = array(
			'version'            => buddypress()->version,
			'active_components'  => buddypress()->active_components,
			'component_page_ids' => bp_core_get_directory_page_ids(),
		);

		$core = apply_filters( 'core_api_data_filter', $core );

		$response = new WP_REST_Response();
		$response->set_data( $core );
		$response = rest_ensure_response( $response );

		return $response;
	}

	/**
	 * core_api_permissions function.
	 *
	 * allow permission to access core info
	 *
	 * @access public
	 * @return void
	 */
	public function core_api_permissions() {

		$response = apply_filters( 'core_api_permissions', true );

		return $response;

	}

	/**
	 * Get the core schema conforming to JSON Schema
	 *
	 * @return array
	 */
	public function get_schema(){
		$schema = array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'core',
			'type'       => 'object',
			/*
			 * Base properties for each Activity
			 */
			'properties' => array(
				'version' => array(
					'description' => 'BuddyPress plugin version.',
					'type'        => 'string',
					'context'     => array( 'view' ),
				),
				'active_components' => array(
					'description' => 'Active BuddyPress compontents.',
					'type'        => 'array',
					'context'     => array( 'view' ),
				),
				'component_page_ids' => array(
					'description' => 'Component page ids.',
					'type'        => 'array',
					'context'     => array( 'view' ),
				),
			),
		);
		return $this->add_additional_fields_schema( $schema );

	}

}
