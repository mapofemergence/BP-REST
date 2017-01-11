<?php
defined( 'ABSPATH' ) || exit;

/**
 * Groups endpoints.
 *
 * @since 0.1.0
 */
class BP_REST_Groups_Controller extends WP_REST_Controller {

	/**
	 * Constructor.
	 *
	 * @since 0.1.0
	 */
	public function __construct() {
		$this->namespace = 'buddypress/v1';
		$this->rest_base = buddypress()->groups->id;
	}

	/**
	 * Register the plugin routes.
	 *
	 * @since 0.1.0
	 */
	public function register_routes() {
		register_rest_route( $this->namespace, '/' . $this->rest_base, array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_items' ),
				'permission_callback' => array( $this, 'get_items_permissions_check' ),
				'args'                => $this->get_collection_params(),
			),
			'schema' => array( $this, 'get_public_item_schema' ),
		) );

		register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_item' ),
				'permission_callback' => array( $this, 'get_item_permissions_check' ),
				'args'                => array(
					'context' => $this->get_context_param( array( 'default' => 'view' ) ),
				),
			),
			'schema' => array( $this, 'get_public_item_schema' ),
		) );


	}

	/**
	 * Get the plugin schema, conforming to JSON Schema.
	 *
	 * @since 0.1.0
	 *
	 * @return array
	 */
	public function get_item_schema() {
		$schema = array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'groups',
			'type'       => 'object',

			'properties' => array(
				'id' => array(
					'context'     => array( 'view', 'edit', 'embed' ),
					'description' => __( 'A unique alphanumeric ID for the object.', 'buddypress' ),
					'readonly'    => true,
					'type'        => 'integer',
				),

				'slug' => array(
					'context'     => array( 'view', 'edit' ),
					'description' => __( 'The slug of the group.', 'buddypress' ),
					'type'        => 'string',
				),

				'name' => array(
					'context'     => array( 'view', 'edit', 'embed' ),
					'description' => __( 'Name of the group', 'buddypress' ),
					'type'        => 'string',
				),
				'description' => array(
					'context'     => array( 'view', 'edit',  ),
					'description' => __( 'Description of the group', 'buddypress' ),
					'type'        => 'string',
				),
				'creator_id' => array(
					'context'     => array( 'view', 'edit' ),
					'description' => __( 'The ID for the creator of the object.', 'buddypress' ),
					'type'        => 'integer',
				),

				'link' => array(
					'context'     => array( 'view', 'edit' ),
					'description' => __( 'The permalink to this object on the site.', 'buddypress' ),
					'format'      => 'url',
					'type'        => 'string',
				),

				'component' => array(
					'context'     => array( 'view', 'edit' ),
					'description' => __( 'The BuddyPress component the object relates to.', 'buddypress' ),
					'type'        => 'string',
					'enum'        => array_keys( bp_core_get_components() ),
				),

				'status' => array(
					'context'     => array( 'view', 'edit' ),
					'description' => __( 'The groups type of the object.', 'buddypress' ),
					'type'        => 'string',
				),

				'is_member' => array(
					'context'     => array( 'view', 'edit' ),
					'description' => __( 'Is the user invited? ', 'buddypress' ),
					'type'        => 'boolean',
				),

				'is_invited' => array(
					'context'     => array( 'view', 'edit' ),
					'description' => __( 'Current user is invited.', 'buddypress' ),
					'type'        => 'boolean',
				),

				'date_created' => array(
					'description' => __( "The date the object was published, in the site's timezone.", 'buddypress' ),
					'type'        => 'string',
					'format'      => 'date-time',
					'context'     => array( 'view', 'edit' ),
				),

				'total_member_count' => array(
					'context'     => array( 'view', 'edit' ),
					'description' => __( 'How many members are part of this group.', 'buddypress' ),
					'type'        => 'integer',

				),

				'is_pending' => array(
					'description'  => __( 'The ID of the parent of the object.', 'buddypress' ),
					'type'         => 'boolean',
					'context'      => array( 'view', 'edit' ),
				),
			)
		);

		return $schema;
	}


	/**
	 * Get the query params for collections of plugins.
	 *
	 * @since 0.1.0
	 *
	 * @return array
	 */
	public function get_collection_params() {
		$params                       = parent::get_collection_params();
		$params['context']['default'] = 'view';

		$params['exclude'] = array(
			'description'       => __( 'Ensure result set excludes specific IDs.', 'buddypress' ),
			'type'              => 'array',
			'default'           => array(),
			'sanitize_callback' => 'wp_parse_id_list',
		);

		$params['include'] = array(
			'description'       => __( 'Ensure result set includes specific IDs.', 'buddypress' ),
			'type'              => 'array',
			'default'           => array(),
			'sanitize_callback' => 'wp_parse_id_list',
		);

		$params['order'] = array(
			'description'       => __( 'Order sort attribute ascending or descending.', 'buddypress' ),
			'type'              => 'string',
			'default'           => 'desc',
			'enum'              => array( 'asc', 'desc' ),
			'validate_callback' => 'rest_validate_request_arg',
		);

		$params['after'] = array(
			'description'       => __( 'Limit result set to items published after a given ISO8601 compliant date.', 'buddypress' ),
			'type'              => 'string',
			'format'            => 'date-time',
			'validate_callback' => 'rest_validate_request_arg',
		);

		$params['per_page'] = array(
			'description'       => __( 'Maximum number of results returned per result set.', 'buddypress' ),
			'default'           => 20,
			'type'              => 'integer',
			'sanitize_callback' => 'absint',
			'validate_callback' => 'rest_validate_request_arg',
		);

		$params['page'] = array(
			'description'       => __( 'Offset the result set by a specific number of pages of results.', 'buddypress' ),
			'default'           => 1,
			'type'              => 'integer',
			'sanitize_callback' => 'absint',
			'validate_callback' => 'rest_validate_request_arg',
		);

		$params['author'] = array(
			'description'       => __( 'Limit result set to items created by specific authors.', 'buddypress' ),
			'type'              => 'array',
			'default'           => 0,
			'type'              => 'integer',
			'sanitize_callback' => 'absint',
			'validate_callback' => 'rest_validate_request_arg',
//            'default'           => array(),
//            'sanitize_callback' => 'wp_parse_id_list',
//            'validate_callback' => 'rest_validate_request_arg',
		);

		$params['status'] = array(
			'default'           => 'published',
			'description'       => __( 'Limit result set to items with a specific status.', 'buddypress' ),
			'type'              => 'string',
			'enum'              => array( 'published', 'spam' ),
			'sanitize_callback' => 'sanitize_key',
			'validate_callback' => 'rest_validate_request_arg',
		);

		$params['primary_id'] = array(
			'description'       => __( 'Limit result set to items with a specific prime assocation.', 'buddypress' ),
			'type'              => 'array',
			'default'           => array(),
			'sanitize_callback' => 'wp_parse_id_list',
		);

		$params['secondary_id'] = array(
			'description'       => __( 'Limit result set to items with a specific secondary assocation.', 'buddypress' ),
			'type'              => 'array',
			'default'           => array(),
			'sanitize_callback' => 'wp_parse_id_list',
		);

		$params['component'] = array(
			'description'       => __( 'Limit result set to items with a specific BuddyPress component.', 'buddypress' ),
			'type'              => 'string',
			'enum'              => array_keys( bp_core_get_components() ),
			'sanitize_callback' => 'sanitize_key',
			'validate_callback' => 'rest_validate_request_arg',
		);

//        $params['type'] = array(
//            'description'       => __( 'Limit result set to items with a specific groups type.', 'buddypress' ),
//            'type'              => 'string',
//            'enum'              => array_keys( bp_groups_get_group_types() ),
//            'sanitize_callback' => 'sanitize_key',
//            'validate_callback' => 'rest_validate_request_arg',
//        );

		$params['search'] = array(
			'description'       => __( 'Limit result set to items that match this search query.', 'buddypress' ),
			'default'           => '',
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'validate_callback' => 'rest_validate_request_arg',
		);

		return $params;
	}

	/**
	 * Retrieve activities.
	 *
	 * @since 0.1.0
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Request List of groups object data.
	 */
	public function get_items( $request ) {


		$args = array(
			'exclude'           => $request['exclude'],
			'in'                => $request['include'],
			'page'              => $request['page'],
			'per_page'          => $request['per_page'],
			'search_terms'      => $request['search'],
			'order'              => $request['order'],
			'user_id'           => $request['author'],

			'count_total'       => true,
			'fields'            => 'all',
			'show_hidden'       => false,
			'update_meta_cache' => true,
		);



		if ( isset( $request['component'] ) ) {
			if ( ! isset( $args['filter'] ) ) {
				$args['filter'] = array( 'object' => $request['component'] );
			} else {
				$args['filter']['object'] = $request['component'];
			}
		}

		if ( isset( $request['type'] ) ) {
			if ( ! isset( $args['filter'] ) ) {
				$args['filter'] = array( 'action' => $request['type'] );
			} else {
				$args['filter']['action'] = $request['type'];
			}
		}

		if ( $args['in'] ) {
			$args['count_total'] = false;
		}

		// Override certain options for security.
		// @TODO: Verify and confirm this show_hidden logic, and check core for other edge cases.
		if ( $request['component'] === 'groups' &&
			(
				groups_is_user_member( get_current_user_id(), $request['id'] ) ||
				bp_current_user_can( 'bp_moderate' )
			)
		) {
			$args['show_hidden'] = true;
		}


		$retval = array();



		$groupdata = groups_get_groups( $args );



		foreach ( $groupdata['groups'] as $group ) {
			$retval[] = $this->prepare_response_for_collection(
				$this->prepare_item_for_response( $group, $request )
			);
		}

		return rest_ensure_response( $retval );
	}

	/**
	 * Retrieve groups.
	 *
	 * @since 0.1.0
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Request|WP_Error Plugin object data on success, WP_Error otherwise.
	 */
	public function get_item( $request ) {
		// TODO: query logic. and permissions. and other parameters that might need to be set. etc

		$groups = groups_get_group( array(
			'group_id'        => BP_Groups_Group::get_id_from_slug( $request['slug'] ),
			'populate_extras' => $request['populate_extras'],
		) );



		$retval = array( $this->prepare_response_for_collection(
			$this->prepare_item_for_response( $groups['groups'][0], $request )
		) );

		return rest_ensure_response( $retval );

	}

	/**
	 * Check if a given request has access to get information about a specific groups.
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
	 * Check if a given request has access to groups items.
	 *
	 * @since 0.1.0
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|bool
	 */
	public function get_items_permissions_check( $request ) {
		// TODO: handle private activities etc
		return true;
	}

	/**
	 * Prepares groups data for return as an object.
	 *
	 * @since 0.1.0
	 *
	 * @param stdClass $group Groups data.
	 * @param WP_REST_Request $request
	 * @param boolean $is_raw Optional, not used. Defaults to false.
	 * @return WP_REST_Response
	 */
	public function prepare_item_for_response( $group, $request, $is_raw = false ) {

		// Parse the arguments.


		$avatar = bp_core_fetch_avatar( array(
			'item_id'    => $group->id,
			'title'      => $group->name,
			'avatar_dir' => 'group-avatars',
			'object'     => 'group',
			'type'       => 'thumb',
			'alt'        => '',
			'css_id'     => false,
			'class'      => null,
			'width'      => false,
			'height'     => false,
			'html' => false
		) );

		$data = array(
			'id'                    => absint($group->id),
			'name'                  => $group->name,
			'slug'                  => $group->slug,
			'description'           => $group->description,
			'creator_id'            => absint($group->creator_id),
			'date_created'          => $this->prepare_date_response( $group->date_created ),
			'total_member_count'    => $group->total_member_count,
			'last_activity'         => $this->prepare_date_response( $group->last_activity ),
			'is_member'             => $group->is_member == "0" ? false : true,
			'is_invited'            => $group->is_invited == "0" ? false : true,
			'is_pending'            => $group->is_pending  == "0" ? false : true,
			'is_banned'             => $group->is_banned  == "0" ? false : true,
			'url'                   => bp_get_group_permalink($group),
			'thumbnail'             => $avatar
		);



		$context = ! empty( $request['context'] ) ? $request['context'] : 'view';
		$data    = $this->add_additional_fields_to_object( $data, $request );
		$data    = $this->filter_response_by_context( $data, $context );

		$response = rest_ensure_response( $data );
		$response->add_links( $this->prepare_links( $group ) );

		/**
		 * Filter an groups value returned from the API.
		 *
		 * @param array           $response
		 * @param WP_REST_Request $request Request used to generate the response.
		 */
		return apply_filters( 'rest_prepare_buddypress_groups_value', $response, $request );
	}

	/**
	 * Prepare links for the request.
	 *
	 * @since 0.1.0
	 *
	 * @param array $groups Groups.
	 * @return array Links for the given plugin.
	 */
	protected function prepare_links( $groups ) {
		$base = sprintf( '/%s/%s/', $this->namespace, $this->rest_base );

		// Entity meta.
		$links = array(
			'self' => array(
				'href' => rest_url( $base . $groups->id ),
			),
			'collection' => array(
				'href' => rest_url( $base ),
			),
			'author' => array(
				'href' => rest_url( '/wp/v2/users/' . $groups->user_id ),
			)
		);

		if ( $groups->type === 'groups_comment' ) {
			$links['up'] = array(
				'href' => rest_url( $base . $groups->item_id ),
			);
		}

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

}
