<?php
/**
 * Accounts Controller
 * 
 * @package default
 * @author  Hamilton Nieri
 */
class WPLE_REST_Accounts_Controller extends WPL_Core {

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
	* Builds the WPL_REST_Accounts_Controller
	*
	* @param string $post_type
	* @author H.Nieri
	* @access public
	*/
	public function __construct() {
		$this->namespace = 'wplister';
		$this->rest_base = 'accounts';
	}

	/**
	 * Register the routes for the objects of the controller
	 */
	public function register_routes() {

		register_rest_route( $this->namespace, '/' . $this->rest_base, array(
			array(
				'methods'         => WP_REST_Server::READABLE,
				'callback'        => array( $this, 'get_accounts' ),
				'permission_callback' => array( $this, 'get_accounts_permission_callback' ),
				'validation_callback' => array( $this, 'get_accounts_validation_callback' )
			),
			array(
				'methods'         => WP_REST_Server::CREATABLE,
				'callback'        => array( $this, 'create_accounts' ),
				'permission_callback' => array( $this, 'create_accounts_permissions_check' ),
				'validation_callback' => array( $this, 'create_accounts_validation_callback' )
			)
		) );

		register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)', array(
			array(
				'methods'         => WP_REST_Server::READABLE,
				'callback'        => array( $this, 'get_account' ),
				'permission_callback' => array( $this, 'get_account_permissions_check' )
			),
			array(
				'methods'         => WP_REST_Server::EDITABLE,
				'callback'        => array( $this, 'update_account' ),
				'permission_callback' => array( $this, 'update_account_permissions_check' ),
			),
			array(
				'methods'  => WP_REST_Server::DELETABLE,
				'callback' => array( $this, 'delete_account' ),
				'permission_callback' => array( $this, 'delete_account_permissions_check' )
			)
		) );
	}

	// ================================ GET /accounts ================================ 

	/**
	 * Check if a given request has access to read /accounts
	 *
	 * @param  WP_REST_Request $request Full details about the request
	 * @return WP_Error|boolean
	 */
	public function get_accounts_permission_callback( $request ) {
		return true;
	}

	/**
	 * Check if a given request is correct
	 *
	 * @param  WP_REST_Request $request Full details about the request
	 * @return WP_Error|boolean
	 */
	public function get_accounts_validation_callback( $request ) {
		return true;
	}

	/**
	 * Get a collection of profiles
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_accounts( $request ) {

		// Get Params
		$args                 = array();
		$args['fields']       = explode( ",", $request['fields'] );
		$args['orderby']      = $request['orderby'];
		$args['page']         = $request['page'];
		$args['per_page']     = $request['per_page'];
		$args['active']       = $request['active'];
		$args['site_id']      = $request['site_id'];
		$args['site_code']    = $request['site_code'];
		$args['sandbox_mode'] = $request['sandbox_mode'];

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

		$query['from']   = "FROM {$wpdb->prefix}ebay_accounts";

		$query['where'] = "WHERE 1";

		if ( ! empty($args['active']) ) {
			$query['where'] .= " AND active={$args['active']}";
		}
		
		if ( ! empty($args['site_id']) ) {
			$query['where'] .= " AND site_id={$args['site_id']}";
		}
		
		if ( ! empty($args['site_code']) ) {
			$query['where'] .= " AND site_code={$args['site_code']}";
		}

		if ( ! empty($args['sandbox_mode']) ) {
			$query['where'] .= " AND type={$args['sandbox_mode']}";
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

	// ================================ GET /accounts/id ================================ 

	/**
	 * Check if a given request has access to read /accounts/id
	 *
	 * @param  WP_REST_Request $request Full details about the request
	 * @return WP_Error|boolean
	 */
	public function get_account_permission_callback( $request ) {
		return true;
	}

	/**
	 * Check if a given request is correct
	 *
	 * @param  WP_REST_Request $request Full details about the request
	 * @return WP_Error|boolean
	 */
	public function get_account_validation_callback( $request ) {
		return true;
	}

	/**
	 * Get a specific profile
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_account( $request ) {

		// Get Params
		$args                 = array();
		$args['fields']       = explode( ",", $request['fields'] );

		$id = $request['id'];

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

		$query['from']   = "FROM {$wpdb->prefix}ebay_accounts";

		$query['where'] = "WHERE id={$id}";

		$query = implode( " ", $query );

		echo json_encode( $wpdb->get_results($query, ARRAY_A) );

		exit;

	}

	// ================================ POST /accounts ================================ 

	/**
	 * Check if a given request has access to create /accounts
	 *
	 * @param  WP_REST_Request $request Full details about the request
	 * @return WP_Error|boolean
	 */
	public function create_account_permission_callback( $request ) {
		return true;
	}

	/**
	 * Check if a given request has access to create /accounts
	 *
	 * @param  WP_REST_Request $request Full details about the request
	 * @return WP_Error|boolean
	 */
	public function create_account_validation_callback( $request ) {
		return true;
	}

	/**
	 * Create an account
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function create_account( $request ) {
		return true;
	}

	// ================================ PATCH /accounts/id ================================ 

	/**
	 * Check if a given request has access to update an account
	 *
	 * @param  WP_REST_Request $request Full details about the request
	 * @return WP_Error|boolean
	 */
	public function update_account_permission_callback( $request ) {
		return true;
	}

	/**
	 * Update a single account
	 *
	 * @param WP_REST_Request $request Full details about the request
	 * @return WP_Error|WP_REST_Response
	 */
	public function update_account( $request ) {
		return true;
	}

	// ================================ DELETE /accounts/id ================================ 

	/**
	 * Check if a given request has access to delete a specific account
	 *
	 * @param  WP_REST_Request $request Full details about the request
	 * @return WP_Error|boolean
	 */
	public function delete_account_permission_callback( $request ) {
		return true;
	}

	/**
	 * Delete a single account
	 *
	 * @param WP_REST_Request $request Full details about the request
	 * @return WP_Error|WP_REST_Response
	 */
	public function delete_account( $request ) {

		$account = new WPLE_eBayAccount( $request['id'] );
		$account->delete();
		
		return true;
	}

	// ================================ PUT /accounts/id/enable ================================ 

	/**
	 * Enable a single account
	 *
	 * @param WP_REST_Request $request Full details about the request
	 * @return WP_Error|WP_REST_Response
	 */
	public function enable_account( $request ) {

		$account = new WPLE_eBayAccount( $request['id'] );
		$account->active = 1;
		$account->update();

	}

	// ================================ PUT /accounts/id/disable ================================ 

	/**
	 * Disable a single account
	 *
	 * @param WP_REST_Request $request Full details about the request
	 * @return WP_Error|WP_REST_Response
	 */
	public function disable_account( $request ) {

		$account = new WPLE_eBayAccount( $request['id'] );
		$account->active = 0;
		$account->update();

	}

	// ================================ PUT /accounts/id/disable ================================ 

	/**
	 * Disable a single account
	 *
	 * @param WP_REST_Request $request Full details about the request
	 * @return WP_Error|WP_REST_Response
	 */
	public function disable_account( $request ) {

		$account = new WPLE_eBayAccount( $request['id'] );
		$account->active = 0;
		$account->update();

	}

}