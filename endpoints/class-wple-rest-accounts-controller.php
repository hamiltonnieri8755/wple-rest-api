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
	 * Attribute keys of account
	 *
	 * @var string
	 */
	protected $fieldnames;

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
		$this->namespace  = 'wplister';
		$this->rest_base  = 'accounts';
		$this->fieldnames = array(
			'title',
			'site_id',
			'site_code',
			'active',
			'sandbox_mode',
			'token',
			'user_name',
			'user_details',
			'valid_until',
			'ebay_motors',
			'oosc_mode',
			'seller_profiles',
			'shipping_profiles',
			'payment_profiles',
			'return_profiles',
			'shipping_discount_profiles',
			'categories_map_ebay',
			'categories_map_store',
			'default_ebay_category_id',
			'paypal_email',
			'sync_orders',
			'sync_products',
			'last_orders_sync',
		);
	}

	/**
	 * Register the routes for the objects of the controller
	 */
	public function register_routes() {

		register_rest_route( $this->namespace, '/' . $this->rest_base, array(
			array(
				'methods'         => WP_REST_Server::READABLE,
				'callback'        => array( $this, 'get_accounts' ),
				'permission_callback' => array( $this, 'manage_accounts_permission_callback' ),
				'validation_callback' => array( $this, 'get_accounts_validation_callback' )
			),
			array(
				'methods'         => WP_REST_Server::CREATABLE,
				'callback'        => array( $this, 'create_account' ),
				'permission_callback' => array( $this, 'manage_accounts_permission_callback' ),
				'validation_callback' => array( $this, 'create_account_validation_callback' )
			)
		) );

		register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)', array(
			array(
				'methods'         => WP_REST_Server::READABLE,
				'callback'        => array( $this, 'get_account' ),
				'permission_callback' => array( $this, 'manage_accounts_permission_callback' )
			),
			array(
				'methods'         => WP_REST_Server::EDITABLE,
				'callback'        => array( $this, 'update_account' ),
				'permission_callback' => array( $this, 'manage_accounts_permission_callback' ),
			),
			array(
				'methods'  => WP_REST_Server::DELETABLE,
				'callback' => array( $this, 'delete_account' ),
				'permission_callback' => array( $this, 'manage_accounts_permission_callback' )
			)
		) );

		register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)/userdetails', array(
			array(
				'methods'         => WP_REST_Server::EDITABLE,
				'callback'        => array( $this, 'update_userdetails' ),
				'permission_callback' => array( $this, 'manage_accounts_permission_callback' )
			)
		) );

		register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)/enable', array(
			array(
				'methods'         => WP_REST_Server::EDITABLE,
				'callback'        => array( $this, 'enable_account' ),
				'permission_callback' => array( $this, 'manage_accounts_permission_callback' )
			)
		) );	

		register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)/disable', array(
			array(
				'methods'         => WP_REST_Server::EDITABLE,
				'callback'        => array( $this, 'disable_account' ),
				'permission_callback' => array( $this, 'manage_accounts_permission_callback' )
			)
		) );

		register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)/makedefault', array(
			array(
				'methods'         => WP_REST_Server::EDITABLE,
				'callback'        => array( $this, 'make_default' ),
				'permission_callback' => array( $this, 'manage_accounts_permission_callback' )
			)
		) );

		register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)/fetchtoken', array(
			array(
				'methods'         => WP_REST_Server::EDITABLE,
				'callback'        => array( $this, 'fetch_token' ),
				'permission_callback' => array( $this, 'manage_accounts_permission_callback' )
			)
		) );

		register_rest_route( $this->namespace, '/' . $this->rest_base . '/dev', array(
			array(
				'methods'         => WP_REST_Server::CREATABLE,
				'callback'        => array( $this, 'add_devaccount' ),
				'permission_callback' => array( $this, 'manage_accounts_permission_callback' ),
				'validation_callback' => array( $this, 'create_account_validation_callback' )
			)
		) );

	}

	// ============================= Permission callbacks ============================

	/**
	 * Check if a given request has access to manage accounts
	 *
	 * @param  WP_REST_Request $request Full details about the request
	 * @return WP_Error|boolean
	 */
	public function manage_accounts_permission_callback( $request ) {
		
		$username = null;
		$password = null;

		if ( isset($_SERVER['PHP_AUTH_USER']) ) {

		    $username = $_SERVER['PHP_AUTH_USER'];
		    $password = $_SERVER['PHP_AUTH_PW'];

		} elseif ( isset($_SERVER['HTTP_AUTHORIZATION']) ) {

		    if ( strpos(strtolower($_SERVER['HTTP_AUTHORIZATION']),'basic') === 0 )
		        list($username,$password) = explode(':',base64_decode(substr($_SERVER['HTTP_AUTHORIZATION'], 6)));

		}

		$user = wp_authenticate( $username, $password );
		
		if ( is_wp_error($user) ) {

			// Invalid Username and Password
			return false;

		} else {

			// Valid Username and Password, Now check wplister ebay capabilities for this user
			if ( $user->has_cap('manage_ebay_options') )
				return true;
			else 
				return false;

		}

	}


	// ================================ GET /accounts ================================ 

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
	 * Get a collection of accounts
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
	 * Check if a given request is correct
	 *
	 * @param  WP_REST_Request $request Full details about the request
	 * @return WP_Error|boolean
	 */
	public function get_account_validation_callback( $request ) {
		return true;
	}

	/**
	 * Get a specific account
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

		// call FetchToken
		$this->initEC();
		$ebay_token = $this->EC->doFetchToken( false );
		$this->EC->closeEbay();

		// check if we have a token
		if ( $ebay_token ) {
			
			// create new account
			$account = new WPLE_eBayAccount();
			// $account->title     = stripslashes( $_POST['wplister_account_title'] );
			$account->title        = $request['title'];
			$account->site_id      = $request['site_id'];
			$account->site_code    = EbayController::getEbaySiteCode( $request['site_code'] );
			$account->sandbox_mode = $request['sandbox_mode'];
			$account->token        = $ebay_token;
			$account->active       = 1;
			$account->add();

			// set enabled flag for site
			$site = WPLE_eBaySite::getSiteObj($account->site_id);
			$site->enabled = 1;
			$site->update();	

			// update user details
			$account->updateUserDetails();

			// set default account automatically
			if ( ! get_option( 'wplister_default_account_id' ) ) {
				update_option( 'wplister_default_account_id', $account->id );
				$this->make_default( array( "id" => $account->id ) );
			}

			//return true;
		} else {
			//return false;
		}
		
	}

	// ================================ PATCH /accounts/id ================================ 

	/**
	 * Update a single account
	 *
	 * @param WP_REST_Request $request Full details about the request
	 * @return WP_Error|WP_REST_Response
	 */
	public function update_account( $request ) {

		global $wpdb;

		$args = array();

		foreach ( $this->fieldnames as $field ) {
			if ( isset($request[$field]) ) {
				$args[$field] = $request[$field];
			}
		}

		$account = new WPLE_eBayAccount( $request['id'] );

		if ( sizeof( $args ) > 0 ) {
			$result = $wpdb->update( "{$wpdb->prefix}ebay_accounts", $args, array( 'id' => $account->id ) );
		}

		return true;
	}

	// ================================ DELETE /accounts/id ================================ 

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

		return true;

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

		return true;

	}

	// ================================ PUT /accounts/id/userdetails ================================ 

	/**
	 * Update user details from ebay
	 *
	 * @param WP_REST_Request $request Full details about the request
	 * @return WP_Error|WP_REST_Response
	 */
	public function update_userdetails( $request ) {

		$account = new WPLE_eBayAccount( $request['id'] );
		if ( ! $account ) return;

		// update user details
		$account->updateUserDetails();
		
		return true;
	}

	// ================================ PUT /accounts/id/makedefault ================================ 

	/**
	 * Make a specific account default
	 *
	 * @param WP_REST_Request $request Full details about the request
	 * @return WP_Error|WP_REST_Response
	 */
	public function make_default( $request ) {

		$account = new WPLE_eBayAccount( $request['id'] );
		if ( ! $account ) return;

		// update default account
		update_option( 'wplister_default_account_id', 			$account->id );

		// backwards compatibility
		update_option( 'wplister_ebay_site_id', 				$account->site_id );
		update_option( 'wplister_ebay_token', 					$account->token );
		update_option( 'wplister_ebay_token_userid', 			$account->user_name );
		update_option( 'wplister_sandbox_enabled', 				$account->sandbox_mode );
		update_option( 'wplister_ebay_token_expirationtime', 	$account->valid_until );
		update_option( 'wplister_enable_ebay_motors', 			$account->ebay_motors ); // deprecated
		update_option( 'wplister_ebay_seller_profiles_enabled', $account->seller_profiles );
		update_option( 'wplister_default_ebay_category_id', 	$account->default_ebay_category_id );
		update_option( 'wplister_paypal_email', 				$account->paypal_email );
		update_option( 'wplister_oosc_mode', 					$account->oosc_mode );
		update_option( 'wplister_ebay_user', 					maybe_unserialize( $account->user_details ) );
		update_option( 'wplister_categories_map_ebay', 			maybe_unserialize( $account->categories_map_ebay ) );
		update_option( 'wplister_categories_map_store', 		maybe_unserialize( $account->categories_map_store ) );

		return true;
	}

	// ================================ PUT /accounts/id/fetchtoken ================================ 

	/**
	 * Fetch token for this account
	 *
	 * @param WP_REST_Request $request Full details about the request
	 * @return WP_Error|WP_REST_Response
	 */
	public function fetch_token( $request ) {

		$account_id = $request['id'];

		// call FetchToken
		$this->initEC( $account_id );
		$ebay_token = $this->EC->doFetchToken( $account_id );
		$this->EC->closeEbay();

		// check if we have a token
		if ( $ebay_token ) {

			// update token expiry date (and other details)
			$this->updateAccount( $account_id );

			// update legacy option
			update_option( 'wplister_ebay_token_is_invalid', false );

			return true;
		} else {
			return false;
		}
	
	}

	// ================================ POST /accounts/dev ================================ 

	/**
	 * Add Developer Account
	 *
	 * @param WP_REST_Request $request Full details about the request
	 * @return WP_Error|WP_REST_Response
	 */
	public function add_devaccount( $request ) {

		$account = new WPLE_eBayAccount();

		$account->title        = 'New Account (DEV)';
		$account->active       = 0;
		$account->site_id      = 0;
		$account->site_code    = 'US';
		$account->user_name    = 'NONE';
		$account->sandbox_mode = 1;
		$account->ebay_motors  = 0;

		return $account->add();
	}

}