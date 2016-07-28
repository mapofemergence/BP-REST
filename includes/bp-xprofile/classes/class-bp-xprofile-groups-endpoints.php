<?php
defined( 'ABSPATH' ) || exit;

/**
 * Endpoints to retrieve information about profile field groups.
 *
 * Use /xprofile/ to find info about all groups
 * Use /xprofile/{id} to return info about a single group
 * Use BP_REST_XProfile_Fields_Controller to get the field info for a group.
 *
 * @since 0.1.0
 */
class BP_REST_XProfile_Groups_Controller extends WP_REST_Controller {

	/**
	 * Constructor.
	 *
	 * @since 0.1.0
	 */
	public function __construct() {
		$this->namespace = 'buddypress/v1';
		$this->rest_base = buddypress()->profile->id;
	}

	/**
	 * Register the routes.
	 *
	 * @since 0.1.0
	 */
	public function register_routes() {
		// Fetch the xprofile groups overview.
		register_rest_route( $this->namespace, '/' . $this->rest_base, array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_groups_overview' ),
				'permission_callback' => array( $this, 'get_items_permissions_check' ),
				'args'                => $this->get_overview_collection_params(),
			),
			'schema' => array( $this, 'xprofile_groups_overview_schema' ),
		) );

		// Fetch a single xprofile group with fields & field data.
		register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_item' ),
				'permission_callback' => array( $this, 'get_item_permissions_check' ),
				'args'                => $this->get_item_collection_params(),

			),
			'schema' => array( $this, 'xprofile_group_single_schema' ),
		) );
	}

	// Xprofile groups overview

	/**
	 * Get the extended profile group schema, conforming to JSON Schema.
	 *
	 * @since 0.1.0
	 *
	 * @return array
	 */
	public function xprofile_groups_overview_schema() {
		$schema = array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'xprofile_groups_overview',
			'type'       => 'object',
			'properties' => array(
				'id' => array(
					'context'     => array( 'view', 'edit' ),
					'description' => __( 'A unique alphanumeric ID for the object.', 'buddypress' ),
					'readonly'    => true,
					'type'        => 'integer',
				),

				'name' => array(
					'context'     => array( 'view', 'edit' ),
					'description' => __( 'The name of the profile field group.', 'buddypress' ),
					'type'        => 'string',
				),

				'description' => array(
					'context'     => array( 'view', 'edit' ),
					'description' => __( 'The description of the profile field group.', 'buddypress' ),
					'type'        => 'string',
				),

				'group_order' => array(
					'context'     => array( 'view', 'edit' ),
					'description' => __( 'The order of the group.', 'buddypress' ),
					'type'        => 'integer',
				),

				'can_delete' => array(
					'context'     => array( 'view', 'edit' ),
					'description' => __( 'Whether the profile field group can be deleted or not.', 'buddypress' ),
					'type'        => 'boolean',
				),
			)
		);

		return $schema;
	}

	/**
	 * Get the query params for collections of xprofile field groups.
	 *
	 * @since 0.1.0
	 *
	 * @return array
	 */
	public function get_overview_collection_params() {
		$params                       = parent::get_collection_params();
		$params['context']['default'] = 'view';

		$params['hide_empty_groups'] = array(
			'description'       => __( 'True to hide groups that do not have any fields.', 'buddypress' ),
			'default'           => false,
			'type'              => 'boolean',
			'validate_callback' => 'rest_validate_request_arg',
		);

		return $params;
	}

	/**
	 * Retrieve groups.
	 *
	 * @since 0.1.0
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Request List of activity object data.
	 */
	public function get_groups_overview( $request ) {

		$args = array(
			'hide_empty_groups' => $request['hide_empty_groups'],
			'user_id'           => false,
		);

		$field_groups = bp_xprofile_get_groups( $args );

		$retval = array();
		foreach ( $field_groups as $item ) {
			$retval[] = $this->prepare_response_for_collection(
				$this->prepare_group_overview_for_response( $item, $request )
			);
		}

		return rest_ensure_response( $retval );
	}

	/**
	 * Prepares xprofile groups overvew data for return as an object.
	 *
	 * @since 0.1.0
	 *
	 * @param stdClass $item Xprofile group data.
	 * @param WP_REST_Request $request
	 * @param boolean $is_raw Optional, not used. Defaults to false.
	 * @return WP_REST_Response
	 */
	public function prepare_group_overview_for_response( $item, $request, $is_raw = false ) {
		$data = array(
			'id'           => (int) $item->id,
			'name'         => $item->name,
			'description'  => $item->description,
			'group_order'  => (int) $item->group_order,
			'can_delete'   => (bool) $item->can_delete,
		);

		$context = ! empty( $request['context'] ) ? $request['context'] : 'view';

		$data    = $this->add_additional_fields_to_object( $data, $request );
		$data    = $this->filter_response_by_context( $data, $context );

		$response = rest_ensure_response( $data );
		$response->add_links( $this->prepare_links( $item ) );

		/**
		 * Filter the xprofile groups overview value returned from the API.
		 *
		 * @param array           $response
		 * @param WP_REST_Request $request Request used to generate the response.
		 */
		return apply_filters( 'rest_prepare_buddypress_xprofile_group_overview_value', $response, $request );
	}

	// Xprofile single group retrieval, with fields & field data

	/**
	 * Get the extended profile group schema, conforming to JSON Schema.
	 *
	 * @since 0.1.0
	 *
	 * @return array
	 */
	public function xprofile_group_single_schema() {
		$schema = array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'xprofile_group_single',
			'type'       => 'object',
			'properties' => array(
				'id' => array(
					'context'     => array( 'view', 'edit' ),
					'description' => __( 'A unique alphanumeric ID for the object.', 'buddypress' ),
					'readonly'    => true,
					'type'        => 'integer',
				),

				'name' => array(
					'context'     => array( 'view', 'edit' ),
					'description' => __( 'The name of the profile field group.', 'buddypress' ),
					'type'        => 'string',
				),

				'description' => array(
					'context'     => array( 'view', 'edit' ),
					'description' => __( 'The description of the profile field group.', 'buddypress' ),
					'type'        => 'string',
				),

				'group_order' => array(
					'context'     => array( 'view', 'edit' ),
					'description' => __( 'The order of the group.', 'buddypress' ),
					'type'        => 'integer',
				),

				'can_delete' => array(
					'context'     => array( 'view', 'edit' ),
					'description' => __( 'Whether the profile field group can be deleted or not.', 'buddypress' ),
					'type'        => 'boolean',
				),
			)
		);

		return $schema;
	}

	/**
	 * Get the query params for collections of xprofile field groups.
	 *
	 * @since 0.1.0
	 *
	 * @return array
	 */
	public function get_item_collection_params() {
		$params                       = parent::get_collection_params();
		$params['context']['default'] = 'view';

		$params['hide_empty_groups'] = array(
			'description'       => __( 'True to hide groups that do not have any fields.', 'buddypress' ),
			'default'           => false,
			'type'              => 'boolean',
			'validate_callback' => 'rest_validate_request_arg',
		);

		$params['user_id'] = array(
			'description'       => __( 'Required if you want to load a specific user\'s data.', 'buddypress' ),
			'type'              => 'integer',
			'default'           => bp_loggedin_user_id(),
			'sanitize_callback' => 'absint',
			'validate_callback' => 'rest_validate_request_arg',
		);

		$params['member_type'] = array(
			'description'       => __( 'Limit fields by those restricted to a given member type, or array of member types. If `$user_id` is provided, the value of `$member_type` will be overridden by the member types of the provided user. The special value of \'any\' will return only those fields that are unrestricted by member type - i.e., those applicable to any type.', 'buddypress' ),
			'type'              => 'array',
			'default'           => null,
			'sanitize_callback' => array( $this, 'sanitize_member_types' ),
			'validate_callback' => array( $this, 'validate_member_types' ),
		);

		$params['hide_empty_fields'] = array(
			'description'       => __( 'True to hide fields where the user has not provided data.', 'buddypress' ),
			'default'           => false,
			'type'              => 'boolean',
			'validate_callback' => 'rest_validate_request_arg',
		);

		$params['fetch_fields'] = array(
			'description'       => __( 'Whether to fetch the fields for each group.', 'buddypress' ),
			'default'           => false,
			'type'              => 'boolean',
			'validate_callback' => 'rest_validate_request_arg',
		);

		$params['fetch_field_data'] = array(
			'description'       => __( 'Whether to fetch data for each field. Requires a $user_id.', 'buddypress' ),
			'default'           => false,
			'type'              => 'boolean',
			'validate_callback' => 'rest_validate_request_arg',
		);

		$params['fetch_visibility_level'] = array(
			'description'       => __( 'Whether to fetch the visibility level for each field. Requires a $user_id.', 'buddypress' ),
			'default'           => false,
			'type'              => 'boolean',
			'validate_callback' => 'rest_validate_request_arg',
		);

		$params['exclude_fields'] = array(
			'description'       => __( 'Ensure result set excludes specific profile fields.', 'buddypress' ),
			'type'              => 'array',
			'default'           => false,
			'sanitize_callback' => 'wp_parse_id_list',
		);

		$params['update_meta_cache'] = array(
			'description'       => __( 'Whether to pre-fetch xprofilemeta for all retrieved groups, fields, and data.', 'buddypress' ),
			'default'           => true,
			'type'              => 'boolean',
			'validate_callback' => 'rest_validate_request_arg',
		);

		return $params;
	}

	/**
	 * Retrieve single xprofile group.
	 *
	 * @since 0.1.0
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Request|WP_Error Plugin object data on success, WP_Error otherwise.
	 */
	public function get_item( $request ) {
		$profile_group_id = (int) $request['id'];

		$args = array(
			'profile_group_id'       => $profile_group_id,
			'user_id'                => $request['user_id'],
			'member_type'            => $request['member_type'],
			'hide_empty_fields'      => $request['hide_empty_fields'],
			'fetch_fields'           => $request['fetch_fields'],
			'fetch_field_data'       => $request['fetch_field_data'],
			'fetch_visibility_level' => $request['fetch_visibility_level'],
			'exclude_fields'         => $request['exclude_fields'],
			'update_meta_cache'      => $request['update_meta_cache'],
		);

		$field_groups = bp_xprofile_get_groups( $args );
		$field_group  = current( $field_groups );

		if ( empty( $profile_group_id ) || empty( $field_group->id ) ) {
			return new WP_Error( 'bp_rest_invalid_group_id', __( 'Invalid resource id.' ), array( 'status' => 404 ) );
		} else {
			$retval = $this->prepare_item_for_response( $field_group, $request );
		}

		return rest_ensure_response( $retval );
	}

	/**
	 * Prepares single xprofile group data for return as an object.
	 *
	 * @since 0.1.0
	 *
	 * @param stdClass $item Xprofile group data.
	 * @param WP_REST_Request $request
	 * @param boolean $is_raw Optional, not used. Defaults to false.
	 * @return WP_REST_Response
	 */
	public function prepare_item_for_response( $item, $request, $is_raw = false ) {
		// Core data
		$data = array(
			'id'           => (int) $item->id,
			'name'         => $item->name,
			'description'  => $item->description,
			'group_order'  => (int) $item->group_order,
			'can_delete'   => (bool) $item->can_delete,
		);

		// If the fields have been requested, we populate them.
		if ( $request['fetch_fields'] ) {
			$data['fields'] = array();
			$fields_controller = new BP_REST_XProfile_Fields_Controller;
			foreach ( $item->fields as $field ) {
				$data['fields'][] = $fields_controller->assemble_response_data( $field, $request );
			}
		}

		$context = ! empty( $request['context'] ) ? $request['context'] : 'view';

		$data    = $this->add_additional_fields_to_object( $data, $request );
		$data    = $this->filter_response_by_context( $data, $context );

		$response = rest_ensure_response( $data );
		$response->add_links( $this->prepare_links( $item ) );

		/**
		 * Filter the xprofile groups overview value returned from the API.
		 *
		 * @param array           $response
		 * @param WP_REST_Request $request Request used to generate the response.
		 */
		return apply_filters( 'rest_prepare_buddypress_xprofile_group_overview_value', $response, $request );
	}


	/**
	 * Check if a given request has access to get information about a specific activity.
	 *
	 * @since 0.1.0
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return bool
	 */
	public function get_item_permissions_check( $request ) {
		return $this->get_items_permissions_check( $request );
	}

	/**
	 * Check if a given request has access to group items.
	 *
	 * @since 0.1.0
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|bool
	 */
	public function get_items_permissions_check( $request ) {
		// Only bp_moderators and logged in users (viewing their own groups) can see hidden groups.
		// if ( ! empty( $request['show_hidden'] ) &&
		// 		(   ! bp_current_user_can( 'bp_moderate' ) &&
		// 		 	! ( ! empty( $request['user_id'] ) && $request['user_id'] == bp_loggedin_user_id() )
		// 	   	)
		// 	) {
		// 	return new WP_Error( 'rest_user_cannot_view', __( 'Sorry, you cannot view hidden groups.'), array( 'status' => rest_authorization_required_code() ) );
		// 	}

		// if ( 'edit' === $request['context'] && ! bp_current_user_can( 'bp_moderate' ) ) {
		// 	return new WP_Error( 'rest_forbidden_context', __( 'Sorry, you cannot view this resource with edit context.' ), array( 'status' => rest_authorization_required_code() ) );
		// }

		return true;
	}

	/**
	 * Prepare links for the request.
	 *
	 * @since 0.1.0
	 *
	 * @param array $item Group.
	 * @return array Links for the given plugin.
	 */
	protected function prepare_links( $item ) {
		$base = sprintf( '/%s/%s/', $this->namespace, $this->rest_base );

		// Entity meta.
		$links = array(
			'self' => array(
				'href' => rest_url( $base . $item->id ),
			),
			'collection' => array(
				'href' => rest_url( $base ),
			),
		);

		return $links;
	}

	/**
	 * Convert the input date to RFC3339 format.
	 *
	 * @param string $date_gmt
	 * @param string|null $date Optional. Date object.
	 * @return string|null ISO8601/RFC3339 formatted datetime.
	 */
	protected function prepare_date_response( $date_gmt, $date = null ) {
		if ( isset( $date ) ) {
			return mysql_to_rfc3339( $date );
		}

		if ( $date_gmt === '0000-00-00 00:00:00' ) {
			return null;
		}

		return mysql_to_rfc3339( $date_gmt );
	}

	/**
	 * Clean up member_type input.
	 *
	 * @param string $value Comma-separated list of group types.
	 *
	 * @return array|null
	 */
	public function sanitize_member_types( $value ) {
		if ( ! empty( $value ) ) {
			$types            = explode( ',', $value );
			$registered_types = bp_get_member_types();
			// Add the special value.
			$registered_types[] = 'any';
			$valid_types = array_intersect( $types, $registered_types );

			if ( ! empty( $valid_types ) ) {
				return $valid_types;
			} else {
				return null;
			}
		}
		return $value;
	}

	/**
	 * Validate member_type input.
	 *
	 * @param  mixed            $value
	 * @param  WP_REST_Request  $request
	 * @param  string           $param
	 *
	 * @return WP_Error|boolean
	 */
	public function validate_member_types( $value, $request, $param ) {
		if ( ! empty( $value ) ) {
			$types            = explode( ',', $value );
			$registered_types = bp_get_member_types();
			// Add the special value.
			$registered_types[] = 'any';
			foreach ( $types as $type) {
				if ( ! in_array( $type, $registered_types ) ) {
					return new WP_Error( 'rest_invalid_group_type', sprintf( __( 'The member type you provided, %s, is not one of %s.' ), $type, implode( ', ', $registered_types ) ) );
				}
			}
		}
		return true;
	}
}
