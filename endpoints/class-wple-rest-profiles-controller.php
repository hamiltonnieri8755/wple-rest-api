<?php
/**
 * Profiles Controller
 * 
 * @package default
 * @author  Hamilton Nieri
 */
class WPLE_REST_Profiles_Controller extends WPL_Core {

	/**
	 * The namespace of this controller's route.
	 *
	 * @var string
	 */
	protected $namespace;

	/**
	 * The base of this controller's route.
	 *
	 * @var string
	 */
	protected $rest_base;

	/**
	* __construct
	* 
	* Builds the WPL_REST_Profiles_Controller
	*
	* @param string $post_type
	* @author H.Nieri
	* @access public
	*/
	public function __construct() {
		$this->namespace = 'wplister';
		$this->rest_base = 'profiles';
	}

	/**
	 * Register the routes for the objects of the controller
	 */
	public function register_routes() {

		register_rest_route( $this->namespace, '/' . $this->rest_base, array(
			array(
				'methods'         => WP_REST_Server::READABLE,
				'callback'        => array( $this, 'get_profiles' ),
				'permission_callback' => array( $this, 'manage_profiles_permission_callback' ),
				'validation_callback' => array( $this, 'get_profiles_validation_callback' )
			),
			array(
				'methods'         => WP_REST_Server::CREATABLE,
				'callback'        => array( $this, 'create_profile' ),
				'permission_callback' => array( $this, 'manage_profiles_permission_callback' ),
				'validation_callback' => array( $this, 'create_profile_validation_callback' )
			)
		) );

		register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)', array(
			array(
				'methods'         => WP_REST_Server::READABLE,
				'callback'        => array( $this, 'get_profile' ),
				'permission_callback' => array( $this, 'manage_profiles_permission_callback' )
			),
			array(
				'methods'         => WP_REST_Server::EDITABLE,
				'callback'        => array( $this, 'update_profile' ),
				'permission_callback' => array( $this, 'manage_profiles_permission_callback' ),
			),
			array(
				'methods'  => WP_REST_Server::DELETABLE,
				'callback' => array( $this, 'delete_profile' ),
				'permission_callback' => array( $this, 'manage_profiles_permission_callback' )
			)
		) );

		register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)/duplicate', array(
			array(
				'methods'         => WP_REST_Server::EDITABLE,
				'callback'        => array( $this, 'duplicate_profile' ),
				'permission_callback' => array( $this, 'manage_profiles_permission_callback' )
			)
		) );

	}

	// ============================= Permission helper / callbacks ============================

	/**
	 * Check if a given request has access to read /profiles
	 *
	 * @param  WP_REST_Request $request Full details about the request
	 * @return WP_Error|boolean
	 */
	public function manage_profiles_permission_callback( $request ) {
		
		$username = null;
		$password = null;

		if ( isset($_SERVER['PHP_AUTH_USER']) ) {

		    $username = $_SERVER['PHP_AUTH_USER'];
		    $password = $_SERVER['PHP_AUTH_PW'];

		} elseif ( isset($_SERVER['HTTP_AUTHORIZATION']) ) {

		    if ( strpos(strtolower($_SERVER['HTTP_AUTHORIZATION']),'basic')===0)
		        list($username,$password) = explode(':',base64_decode(substr($_SERVER['HTTP_AUTHORIZATION'], 6)));

		}

		$user = wp_authenticate( $username, $password );
		
		if ( is_wp_error($user) ) {

			// Invalid Username and Password
			return false;

		} else {

			// Valid Username and Password, Now check wplister ebay capabilities for this user
			if ( $user->has_cap('manage_ebay_listings') )
				return true;
			else 
				return false;

		}

	}

	// ================================ GET /profiles ================================ 

	/**
	 * Check if a given request is correct
	 *
	 * @param  WP_REST_Request $request Full details about the request
	 * @return WP_Error|boolean
	 */
	public function get_profiles_validation_callback( $request ) {
		return true;
	}

	/**
	 * Get a collection of profiles
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_profiles( $request ) {

		// Get Params
		$args                 = array();
		$args['fields']       = explode( ",", $request['fields'] );
		$args['orderby']      = $request['orderby'];
		$args['page']         = $request['page'];
		$args['per_page']     = $request['per_page'];
		$args['conditions']   = $request['conditions'];
		$args['account_id']   = $request['account_id'];
		$args['site_id']      = $request['site_id'];

		global $wpdb;

		// Build A Query
		$query = array();
		if ( count($request['fields']) > 0 ) {

			$select = array();
			foreach ( $args['fields'] as $field ) {
				array_push( $select, $field );
			}

			$query['select'] = "SELECT " . implode( ',', $select );

		} else {

			$query['select'] = "SELECT *";

		}

		$query['from']   = "FROM {$wpdb->prefix}ebay_profiles";

		$query['where'] = "WHERE 1";

		if ( ! empty($args['conditions']) ) {
			$query['where'] .= " AND conditions={$args['conditions']}";
		}

		if ( ! empty($args['account_id']) ) {
			$query['where'] .= " AND account_id={$args['account_id']}";
		}

		if ( ! empty($args['site_id']) ) {
			$query['where'] .= " AND site_id={$args['site_id']}";
		}
		
		if ( ! empty($args['type']) ) {
			$query['where'] .= " AND type={$args['type']}";
		}

		if ( ! empty($args['orderby']) ) {
			$query['orderby'] = "ORDER BY {$args['orderby']}";			
		}

		if ( ! empty($args['page']) && ! empty($args['per_page']) ) {
			$offset = ( $args['page'] - 1 ) * $args['per_page'];
			$query['limit'] = "LIMIT {$offset}, {$args['per_page']}";
		}

		$query = implode( " ", $query );

		echo json_encode( $wpdb->get_results($query, ARRAY_A) );

		exit;

	}

	// ================================ GET /profiles/id ================================ 

	/**
	 * Check if a given request is correct
	 *
	 * @param  WP_REST_Request $request Full details about the request
	 * @return WP_Error|boolean
	 */
	public function get_profile_validation_callback( $request ) {
		return true;
	}

	/**
	 * Get a specific profile
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_profile( $request ) {

		// Get Params
		$args                 = array();
		$args['fields']       = explode( ",", $request['fields'] );
		$args['orderby']      = $request['orderby'];
		$args['page']         = $request['page'];
		$args['per_page']     = $request['per_page'];
		$args['conditions']   = $request['conditions'];
		$args['account_id']   = $request['account_id'];
		$args['site_id']      = $request['site_id'];

		$id = (int) $request['id'];

		global $wpdb;

		// Build A Query
		$query = array();
		if ( count($request['fields']) > 0 ) {

			$select = array();
			foreach ( $args['fields'] as $field ) {
				array_push( $select, $field );
			}

			$query['select'] = "SELECT " . implode( ',', $select );

		} else {

			$query['select'] = "SELECT *";

		}

		$query['from']   = "FROM {$wpdb->prefix}ebay_profiles";

		$query['where'] = "WHERE id={$id}";

		if ( ! empty($args['conditions']) ) {
			$query['where'] .= " AND conditions={$args['conditions']}";
		}

		if ( ! empty($args['account_id']) ) {
			$query['where'] .= " AND account_id={$args['account_id']}";
		}

		if ( ! empty($args['site_id']) ) {
			$query['where'] .= " AND site_id={$args['site_id']}";
		}
		
		if ( ! empty($args['type']) ) {
			$query['where'] .= " AND type={$args['type']}";
		}

		if ( ! empty($args['orderby']) ) {
			$query['orderby'] = "ORDER BY {$args['orderby']}";			
		}

		if ( ! empty($args['page']) && ! empty($args['per_page']) ) {
			$offset = ( $args['page'] - 1 ) * $args['per_page'];
			$query['limit'] = "LIMIT {$offset}, {$args['per_page']}";
		}

		$query = implode( " ", $query );

		echo json_encode( $wpdb->get_results($query, ARRAY_A) );

		exit;

	}

	// ================================ POST /profiles ================================ 

	/**
	 * Check if a given request has access to create /profiles
	 *
	 * @param  WP_REST_Request $request Full details about the request
	 * @return WP_Error|boolean
	 */
	public function create_profile_validation_callback( $request ) {
		return true;
	}

	/**
	 * Create a profile
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function create_profile( $request ) {

		global $wpdb;

		$args = array();

		if ( ! empty($request['profile_name']) ) {
			$args['profile_name'] = $request['profile_name'];
		}

		if ( ! empty($request['profile_description']) ) {
			$args['profile_description'] = $request['profile_description'];
		}

		if ( ! empty($request['listing_duration']) ) {
			$args['listing_duration'] = $request['listing_duration'];
		}

		if ( ! empty($request['type']) ) {
			$args['type'] = $request['type'];
		}

		if ( ! empty($request['sort_order']) ) {
			$args['sort_order'] = $request['sort_order'];
		}

		if ( ! empty($request['details']) ) {
			$args['details'] = $request['details'];
		}

		if ( ! empty($request['conditions']) ) {
			$args['conditions'] = $request['conditions'];
		}

		if ( ! empty($request['category_specifics']) ) {
			$args['category_specifics'] = $request['category_specifics'];
		}

		if ( ! empty($request['account_id']) ) {
			$args['account_id'] = $request['account_id'];
		}

		if ( ! empty($request['site_id'])) {
			$args['site_id'] = $request['site_id'];
		}

		$wpdb->insert( "{$wpdb->prefix}ebay_profiles", $args );

		$profile_id = $wpdb->insert_id;

		return $profile_id;

	}

	// ================================ PATCH /profiles/id ================================ 

	/**
	 * Update a single profile
	 *
	 * @param WP_REST_Request $request Full details about the request
	 * @return WP_Error|WP_REST_Response
	 */
	public function update_profile( $request ) {

		global $wpdb;

		$id   = (int) $request['id'];

		$args = array();

		if ( ! empty($request['profile_name']) ) {
			$args['profile_name'] = $request['profile_name'];
		}

		if ( ! empty($request['profile_description']) ) {
			$args['profile_description'] = $request['profile_description'];
		}

		if ( ! empty($request['listing_duration']) ) {
			$args['listing_duration'] = $request['listing_duration'];
		}

		if ( ! empty($request['type']) ) {
			$args['type'] = $request['type'];
		}

		if ( ! empty($request['sort_order']) ) {
			$args['sort_order'] = $request['sort_order'];
		}

		if ( ! empty($request['details']) ) {
			$args['details'] = $request['details'];
		}

		if ( ! empty($request['conditions']) ) {
			$args['conditions'] = $request['conditions'];
		}

		if ( ! empty($request['category_specifics']) ) {
			$args['category_specifics'] = $request['category_specifics'];
		}

		if ( ! empty($request['account_id']) ) {
			$args['account_id'] = $request['account_id'];
		}

		if ( ! empty($request['site_id'])) {
			$args['site_id'] = $request['site_id'];
		}

		$affected_profiles = $wpdb->update( 'wplab_ebay_profiles', $args, array( 'profile_id' => $id ) );

		if ( $affected_profiles )
			return true;
		else
			return false;

	}

	// ================================ DELETE /profiles/id ================================ 

	/**
	 * Delete a single profile
	 *
	 * @param WP_REST_Request $request Full details about the request
	 * @return WP_Error|WP_REST_Response
	 */
	public function delete_profile( $request ) {

		global $wpdb;

		$id = $request['id'];

		// check if there are listings using this profile
		$listings = WPLE_ListingQueryHelper::getAllWithProfile( $id );
		if ( ! empty($listings) ) {
			echo 'This profile is applied to '.count($listings).' listings and can not be deleted.';
			exit;
		}

		$wpdb->query( $wpdb->prepare("
			DELETE
			FROM {$wpdb->prefix}ebay_profiles
			WHERE profile_id = %s
		", $id ) );

		return true;
	}

	// ================================ PUT /profiles/id/duplicate ================================ 

	/**
	 * Duplicate a single profile
	 *
	 * @param WP_REST_Request $request Full details about the request
	 * @return WP_Error|WP_REST_Response
	 */
	public function duplicate_profile( $request ) {
		$profilesModel = new ProfilesModel();
		$new_profile_id = $profilesModel->duplicateProfile( $request['id'] );
		return $new_profile_id;
	}

}