<?php
defined( 'ABSPATH' ) || exit;

/**
<<<<<<< HEAD
 * Endpoints to retrieve information about profile fields.
 *
 * Use /xprofile/fields/{id} to return info about a single field
=======
 * Endpoints to retrieve information about profile field groups.
 *
 * Use /xprofile/ to find info about all groups
 * Use /xprofile/{id} to return info about a single group
 * Use BP_REST_XProfile_Fields_Controller to get the field info for a group.
>>>>>>> dcavins/commons-working
 *
 * @since 0.1.0
 */
class BP_REST_XProfile_Fields_Controller extends WP_REST_Controller {

	/**
	 * Constructor.
	 *
	 * @since 0.1.0
	 */
	public function __construct() {
		$this->namespace = 'buddypress/v1';
		$this->rest_base = buddypress()->profile->id . '/fields';
	}

	/**
	 * Register the routes.
	 *
	 * @since 0.1.0
	 */
	public function register_routes() {
		// Fetch xprofile fields.
		// @TODO: There's no general `get()` for fields, so maybe we don't do this.
		// register_rest_route( $this->namespace, '/' . $this->rest_base, array(
		// 	array(
		// 		'methods'             => WP_REST_Server::READABLE,
		// 		'callback'            => array( $this, 'get_items' ),
		// 		'permission_callback' => array( $this, 'get_items_permissions_check' ),
		// 		'args'                => $this->get_collection_params(),
		// 	),
		// 	'schema' => array( $this, 'get_item_schema' ),
		// ) );

<<<<<<< HEAD
		// Fetch a single xprofile field with field data.
=======
		// Fetch a single xprofile group with fields & field data.
>>>>>>> dcavins/commons-working
		register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_item' ),
				'permission_callback' => array( $this, 'get_item_permissions_check' ),
				'args'                => $this->get_item_params(),
			),
			'schema' => array( $this, 'get_item_schema' ),
		) );
	}

	/**
	 * Get the extended profile field schema, conforming to JSON Schema.
	 *
	 * @since 0.1.0
	 *
	 * @return array
	 */
	public function get_item_schema() {
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

				'group_id' => array(
					'context'     => array( 'view', 'edit' ),
					'description' => __( 'The ID of the group the field is part of.', 'buddypress' ),
					'type'        => 'integer',
				),

				'parent_id' => array(
					'context'     => array( 'view', 'edit' ),
					'description' => __( 'The ID of the field parent.', 'buddypress' ),
					'type'        => 'integer',
				),

				'type' => array(
					'context'     => array( 'view', 'edit' ),
					'description' => __( 'The type of field, like checkbox or select.', 'buddypress' ),
					'type'        => 'string',
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

				'is_required' => array(
					'context'     => array( 'view', 'edit' ),
					'description' => __( 'Whether the profile field must have a value.', 'buddypress' ),
					'type'        => 'boolean',
				),

				'can_delete' => array(
					'context'     => array( 'view', 'edit' ),
					'description' => __( 'Whether the profile field can be deleted or not.', 'buddypress' ),
					'type'        => 'boolean',
				),

				'field_order' => array(
					'context'     => array( 'view', 'edit' ),
					'description' => __( 'The order of the field.', 'buddypress' ),
					'type'        => 'integer',
				),

				'option_order' => array(
					'context'     => array( 'view', 'edit' ),
					'description' => __( 'The order of the field\'s options.', 'buddypress' ),
					'type'        => 'integer',
				),

				'order_by' => array(
					'context'     => array( 'view', 'edit' ),
					'description' => __( 'How the field\'s options are ordered.', 'buddypress' ),
					'type'        => 'string',
				),

				'is_default_option' => array(
					'context'     => array( 'view', 'edit' ),
					'description' => __( 'Whether the option is the default option.', 'buddypress' ),
					'type'        => 'boolean',
				),

				'visibility_level' => array(
					'context'     => array( 'view', 'edit' ),
					'description' => __( 'Who may see the saved value for this field.', 'buddypress' ),
					'type'        => 'string',
				),

				'data' => array(
					'context'     => array( 'view', 'edit' ),
					'description' => __( 'The saved value for this field.', 'buddypress' ),
					'type'        => 'array',
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
	public function get_collection_params() {
		$params                       = parent::get_collection_params();
		$params['context']['default'] = 'view';

		// @TODO Maybe nothing here, given that maybe we don't do collections of fields (outside of groups).
		return $params;
	}

	/**
	 * Get the query params for single xprofile fields.
	 *
	 * @since 0.1.0
	 *
	 * @return array
	 */
	public function get_item_params() {
		$params                       = parent::get_collection_params();
		$params['context']['default'] = 'view';

		$params['user_id'] = array(
			'description'       => __( 'Required if you want to load a specific user\'s data.', 'buddypress' ),
			'type'              => 'integer',
			'default'           => bp_loggedin_user_id(),
			'sanitize_callback' => 'absint',
			'validate_callback' => 'rest_validate_request_arg',
		);

		return $params;
	}

	/**
	 * Retrieve single xprofile field.
	 *
	 * @since 0.1.0
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Request|WP_Error Plugin object data on success, WP_Error otherwise.
	 */
	public function get_item( $request ) {
		$profile_field_id = (int) $request['id'];

		$field = xprofile_get_field( $profile_field_id );

<<<<<<< HEAD
		// @TODO: Visibility doesn't protect field values in this function.
		if ( ! empty( $request['user_id'] ) ) {
			$field->data = new stdClass;
			$field->data->value = xprofile_get_field_data( $profile_field_id, $request['user_id'] );
			// Set 'fetch_field_data' to true so that the data is included in the response.
			$request['fetch_field_data'] = true;
=======
		if ( ! empty( $request['user_id'] ) ) {
			$field->data->value = xprofile_get_field_data( $profile_field_id, $request['user_id'] );
>>>>>>> dcavins/commons-working
		}

		if ( empty( $profile_field_id ) || empty( $field->id ) ) {
			return new WP_Error( 'bp_rest_invalid_field_id', __( 'Invalid resource id.' ), array( 'status' => 404 ) );
		} else {
			$retval = $this->prepare_item_for_response( $field, $request );
		}

		return rest_ensure_response( $retval );
	}

	/**
	 * Assembles single xprofile field data for return as an object.
	 *
	 * @since 0.1.0
	 *
	 * @param stdClass $item Xprofile group data.
	 * @param WP_REST_Request $request
	 * @param boolean $is_raw Optional, not used. Defaults to false.
	 * @return Array
	 */
	public function assemble_response_data( $item, $request, $is_raw = false ) {
		// Core data
		$data = array(
			'id'                => (int) $item->id,
			'group_id'          => (int) $item->group_id,
			'parent_id'         => (int) $item->parent_id,
			'type'              => $item->type,
			'name'              => $item->name,
			'description'       => $item->description,
			'is_required'       => (bool) $item->is_required,
			'can_delete'        => (bool) $item->can_delete,
			'field_order'       => (int) $item->field_order,
			'option_order'      => (int) $item->option_order,
			'order_by'          => $item->order_by,
			'is_default_option' => (bool) $item->is_default_option,
		);

		if ( ! empty( $request['fetch_visibility_level'] ) ) {
			$data['visibility_level'] = $item->visibility_level;
		}

		IF ( ! empty( $request['fetch_field_data'] ) ) {
			if ( isset( $item->data->id ) ) {
				$data['data']['id']    = $item->data->id;
			}
			$data['data']['value'] = maybe_unserialize( $item->data->value );
		}

		$context = ! empty( $request['context'] ) ? $request['context'] : 'view';

		$data    = $this->add_additional_fields_to_object( $data, $request );
		$data    = $this->filter_response_by_context( $data, $context );

		return $data;
	}

	/**
	 * Prepares single xprofile field data for return as an object.
	 *
	 * @since 0.1.0
	 *
	 * @param stdClass $item Xprofile group data.
	 * @param WP_REST_Request $request
	 * @param boolean $is_raw Optional, not used. Defaults to false.
	 * @return WP_REST_Response
	 */
	public function prepare_item_for_response( $item, $request, $is_raw = false ) {
		$data = $this->assemble_response_data( $item, $request, $is_raw );

		$response = rest_ensure_response( $data );
		$response->add_links( $this->prepare_links( $item ) );

		/**
		 * Filter the xprofile groups overview value returned from the API.
		 *
		 * @param array           $response
		 * @param WP_REST_Request $request Request used to generate the response.
		 */
		return apply_filters( 'rest_prepare_buddypress_xprofile_fields_value', $response, $request );
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
<<<<<<< HEAD
		// @TODO: Much of this is handled by the visibility logic.
=======
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
>>>>>>> dcavins/commons-working

		return true;
	}

	/**
	 * Prepare links for the request.
	 *
	 * @since 0.1.0
	 *
<<<<<<< HEAD
	 * @param array $item Field.
=======
	 * @param array $item Group.
>>>>>>> dcavins/commons-working
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
}
