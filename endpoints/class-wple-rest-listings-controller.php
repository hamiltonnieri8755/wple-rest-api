<?php
/**
 * Listings Controller
 * 
 * @package default
 * @author  Hamilton Nieri
 */
class WPLE_REST_Listings_Controller extends WPL_Core {

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
	 * Array of fields that could be 
	 *
	 * @var string
	 */
	protected $adjustable_fields;

	/**
	 * Username 
	 *
	 * @var string
	 */
	protected $username;

	/**
	 * Password 
	 *
	 * @var string
	 */
	protected $password;

	/**
	* __construct
	* 
	* Builds the WPL_REST_Listings_Controller
	*
	* @param string $post_type
	* @author H.Nieri
	* @access public
	*/
	public function __construct() {
		$this->namespace = 'wplister';
		$this->rest_base = 'listings';
		$this->adjustable_fields = array( "auction_title", "relist_date", "status", "locked", "profile_id", "post_id", "account_id", "site_id" );
	}

	/**
	 * Register the routes for the objects of the controller
	 */
	public function register_routes() {

		register_rest_route( $this->namespace, '/' . $this->rest_base, array(
			array(
				'methods'         => WP_REST_Server::READABLE,
				'callback'        => array( $this, 'get_listings' ),
				'permission_callback' => array( $this, 'manage_listings_permission_callback' ),
				'validation_callback' => array( $this, 'get_listings_validation_callback' )
			),
			array(
				'methods'         => WP_REST_Server::CREATABLE,
				'callback'        => array( $this, 'create_listing' ),
				'permission_callback' => array( $this, 'prepare_listings_permission_callback' ),
				'validation_callback' => array( $this, 'create_listing_validation_callback' )
			)
		) );

		register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)', array(
			array(
				'methods'         => WP_REST_Server::READABLE,
				'callback'        => array( $this, 'get_listing' ),
				'permission_callback' => array( $this, 'manage_listings_permission_callback' )
			),
			array(
				'methods'         => WP_REST_Server::EDITABLE,
				'callback'        => array( $this, 'update_listing' ),
				'permission_callback' => array( $this, 'manage_listings_permission_callback' ),
			),
			array(
				'methods'  => WP_REST_Server::DELETABLE,
				'callback' => array( $this, 'delete_listing' ),
				'permission_callback' => array( $this, 'manage_listings_permission_callback' )
			)
		) );

		register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)/verify', array(
			array(
				'methods'         => WP_REST_Server::EDITABLE,
				'callback'        => array( $this, 'verify_listing' ),
				'permission_callback' => array( $this, 'manage_listings_permission_callback' )
			)
		) );

		register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)/revise', array(
			array(
				'methods'         => WP_REST_Server::EDITABLE,
				'callback'        => array( $this, 'revise_listing' ),
				'permission_callback' => array( $this, 'manage_listings_permission_callback' )
			)
		) );

		register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)/publish', array(
			array(
				'methods'         => WP_REST_Server::EDITABLE,
				'callback'        => array( $this, 'publish_listing' ),
				'permission_callback' => array( $this, 'publish_listings_permission_callback' )
			)
		) );

		register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)/end', array(
			array(
				'methods'         => WP_REST_Server::EDITABLE,
				'callback'        => array( $this, 'end_listing' ),
				'permission_callback' => array( $this, 'manage_listings_permission_callback' )
			)
		) );

		register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)/relist', array(
			array(
				'methods'         => WP_REST_Server::EDITABLE,
				'callback'        => array( $this, 'relist_listing' ),
				'permission_callback' => array( $this, 'manage_listings_permission_callback' )
			)
		) );

		register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)/lock', array(
			array(
				'methods'         => WP_REST_Server::EDITABLE,
				'callback'        => array( $this, 'lock_listing' ),
				'permission_callback' => array( $this, 'manage_listings_permission_callback' )
			)
		) );

		register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)/unlock', array(
			array(
				'methods'         => WP_REST_Server::EDITABLE,
				'callback'        => array( $this, 'unlock_listing' ),
				'permission_callback' => array( $this, 'manage_listings_permission_callback' )
			)
		) );

		register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)/reapply', array(
			array(
				'methods'         => WP_REST_Server::EDITABLE,
				'callback'        => array( $this, 'reapply_profile2listing' ),
				'permission_callback' => array( $this, 'manage_listings_permission_callback' )
			)
		) );

		register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)/cleareps', array(
			array(
				'methods'         => WP_REST_Server::EDITABLE,
				'callback'        => array( $this, 'clear_eps' ),
				'permission_callback' => array( $this, 'manage_listings_permission_callback' )
			)
		) );

		register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)/resetstatus', array(
			array(
				'methods'         => WP_REST_Server::EDITABLE,
				'callback'        => array( $this, 'reset_status' ),
				'permission_callback' => array( $this, 'manage_listings_permission_callback' )
			)
		) );

		register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)/archive', array(
			array(
				'methods'         => WP_REST_Server::EDITABLE,
				'callback'        => array( $this, 'archive_listing' ),
				'permission_callback' => array( $this, 'manage_listings_permission_callback' )
			)
		) );

		register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)/cancelsched', array(
			array(
				'methods'         => WP_REST_Server::EDITABLE,
				'callback'        => array( $this, 'cancel_schedule' ),
				'permission_callback' => array( $this, 'manage_listings_permission_callback' )
			)
		) );

		register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)/updatefromebay', array(
			array(
				'methods'         => WP_REST_Server::EDITABLE,
				'callback'        => array( $this, 'update_from_ebay' ),
				'permission_callback' => array( $this, 'manage_listings_permission_callback' )
			)
		) );

	}

	// ============================= Permission helper / callbacks ============================

	/**
	 * Listings endpoint permission helper
	 *
	 * @param  WP_REST_Request $request Full details about the request, capability
	 * @return boolean
	 */
	public function listings_permission_helper( $capability ) {

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
			if ( $user->has_cap($capability) )
				return true;
			else 
				return false;

		}

	}

	/**
	 * Check if a given request has access to manage ebay listings
	 *
	 * @param  WP_REST_Request $request Full details about the request
	 * @return WP_Error|boolean
	 */
	public function manage_listings_permission_callback( $request ) {
		return $this->listings_permission_helper( 'manage_ebay_listings' );
	}

	/**
	 * Check if a given request has access to create /listings
	 *
	 * @param  WP_REST_Request $request Full details about the request
	 * @return WP_Error|boolean
	 */
	public function prepare_listings_permission_callback( $request ) {
		return $this->listings_permission_helper( 'prepare_ebay_listings' );
	}

	/**
	 * Check if a given request has access to create /listings
	 *
	 * @param  WP_REST_Request $request Full details about the request
	 * @return WP_Error|boolean
	 */
	public function publish_listings_permission_callback( $request ) {
		return $this->listings_permission_helper( 'publish_ebay_listings' );
	}

	// ================================ GET /listings ================================ 

	/**
	 * Check if a given request is correct
	 *
	 * @param  WP_REST_Request $request Full details about the request
	 * @return WP_Error|boolean
	 */
	public function get_listings_validation_callback( $request ) {
		return true;
	}

	/**
	 * Get a collection of listings
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_listings( $request ) {

		// Get Params
		$args                 = array();
		$args['fields']       = explode( ",", $request['fields'] );
		$args['orderby']      = $request['orderby'];
		$args['page']         = $request['page'];
		$args['per_page']     = $request['per_page'];
		$args['status']       = $request['status'];
		$args['account_id']   = $request['account_id'];
		$args['site_id']      = $request['site_id'];
		$args['auction_type'] = $request['auction_type'];
		$args['locked']       = $request['locked'];
		$args['profile_id']   = $request['profile_id'];

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

		$query['from']   = "FROM {$wpdb->prefix}ebay_auctions";

		$query['where'] = "WHERE 1";

		if ( ! empty($args['status']) ) {
			$query['where'] .= " AND status={$args['status']}";
		}

		if ( ! empty($args['account_id']) ) {
			$query['where'] .= " AND account_id={$args['account_id']}";
		}

		if ( ! empty($args['site_id']) ) {
			$query['where'] .= " AND site_id={$args['site_id']}";
		}
		
		if ( ! empty($args['auction_type']) ) {
			$query['where'] .= " AND auction_type={$args['auction_type']}";
		}
		
		if ( ! empty($args['locked']) ) {
			$query['where'] .= " AND locked={$args['locked']}";
		}

		if ( ! empty($args['profile_id']) ) {
			$query['where'] .= " AND profile_id={$args['profile_id']}";
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

	// ================================ GET /listings/id ================================ 

	/**
	 * Get a single listing
	 *
	 * @param WP_REST_Request $request Full details about the request
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_listing( $request ) {
		
		// Get Params
		$args                 = array();
		$args['fields']       = explode( ",", $request['fields'] );
		$args['orderby']      = $request['orderby'];
		$args['page']         = $request['page'];
		$args['per_page']     = $request['per_page'];
		$args['status']       = $request['status'];
		$args['account_id']   = $request['account_id'];
		$args['site_id']      = $request['site_id'];
		$args['auction_type'] = $request['auction_type'];
		$args['locked']       = $request['locked'];
		$args['profile_id']   = $request['profile_id'];
		
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

		$query['from']  = "FROM {$wpdb->prefix}ebay_auctions";

		$query['where'] = "WHERE id={$id}";

		if ( ! empty($args['status']) ) {
			$query['where'] .= " AND status={$args['status']}";
		}

		if ( ! empty($args['account_id']) ) {
			$query['where'] .= " AND account_id={$args['account_id']}";
		}

		if ( ! empty($args['site_id']) ) {
			$query['where'] .= " AND site_id={$args['site_id']}";
		}
		
		if ( ! empty($args['auction_type']) ) {
			$query['where'] .= " AND auction_type={$args['auction_type']}";
		}
		
		if ( ! empty($args['locked']) ) {
			$query['where'] .= " AND locked={$args['locked']}";
		}

		if ( ! empty($args['profile_id']) ) {
			$query['where'] .= " AND profile_id={$args['profile_id']}";
		}

		if ( ! empty($args['orderby']) ) {
			$query['orderby'] = "ORDER BY {$args['orderby']}";			
		}

		if ( ! empty($args['page']) && ! empty($args['per_page']) ) {
			$offset = ( $args['page'] - 1 ) * $args['per_page'];
			$query['limit'] = "LIMIT {$offset}, {$args['per_page']}";
		}

		$query = implode( " ", $query );

		echo json_encode($wpdb->get_results($query, ARRAY_A));

		exit;
	}

	// ================================ POST /listings ================================ 

	/**
	 * Check if a given request has access to create /listings
	 *
	 * @param  WP_REST_Request $request Full details about the request
	 * @return WP_Error|boolean
	 */
	public function create_listing_validation_callback( $request ) {
		return true;
	}

	/**
	 * Create a listing
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function create_listing( $request ) {

		if ( !isset($request['profile_id']) || !isset($request['product_id']) )
			return false;

		// Get Profile
		$profilesModel = new ProfilesModel();
        $profile = $profilesModel->getItem( $request['profile_id'] );

        if ( $profile ) {
	
			// Prepare Product
			$listingsModel = new ListingsModel();
	        $listing_id = $listingsModel->prepareProductForListing( $request['product_id'], $profile['profile_id'] );

			if ( $listing_id ) {
		        $listingsModel->applyProfileToNewListings( $profile );		      
				return $listing_id;
			} else { 
				return false; 
			}	

        }

        return false;

	}

	// ================================ PATCH /listings/id ================================ 

	/**
	 * Update a single listing
	 *
	 * @param WP_REST_Request $request Full details about the request
	 * @return WP_Error|WP_REST_Response
	 */
	public function update_listing( $request ) {
		global $wpdb;

		// Get Param
		$listing_id = $request['id'];

		// Set Published Items To Changed
		$affected_listings = $wpdb->update( "{$wpdb->prefix}ebay_auctions", array( 'status' => 'changed' ), array( 'status' => 'published', 'id' => $listing_id ) );

		if ( $affected_listings ) 
			return true;
		else
			return false;
	}

	// ================================ PUT /listings/id/verify ================================ 

	/**
	 * Verify a listing
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function verify_listing( $request ) {

		$account_id = isset( $request['id'] ) ? WPLE_ListingQueryHelper::getAccountID( $request['id'] ) : false;
		$this->initEC( $account_id );
		$this->EC->verifyItems( $request['id'] );
		$this->EC->closeEbay();

		if ( $this->EC->isSuccess ) {
			return true;
		} else {
			return false;
		} 

	}

	// ================================ PUT /listings/id/revise ================================ 

	/**
	 * Publish a listing
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function revise_listing( $request ) {

		$account_id = isset( $request['id'] ) ? WPLE_ListingQueryHelper::getAccountID( $request['id'] ) : false;
		$this->initEC( $account_id );
		$this->EC->reviseItems( $request['id'] );
		$this->EC->closeEbay();

		if ( $this->EC->isSuccess ) {
			return true;
		} else {
			return false;
		} 

	}

	// ================================ PUT /listings/id/publish ================================ 

	/**
	 * Publish a listing
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function publish_listing( $request ) {

		$account_id = isset( $request['id'] ) ? WPLE_ListingQueryHelper::getAccountID( $request['id'] ) : false;
		$this->initEC( $account_id );
		$this->EC->sendItemsToEbay( $request['id'] );
		$this->EC->closeEbay();

		if ( $this->EC->isSuccess ) {
			return true;
		} else {
			return false;
		} 

	}

	// ================================ PUT /listings/id/relist ================================ 

	/**
	 * Relist a listing
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function relist_listing( $request ) {

		$account_id = isset( $request['id'] ) ? WPLE_ListingQueryHelper::getAccountID( $request['id'] ) : false;
		$this->initEC( $account_id );
		$this->EC->relistItems( $request['id'] );
		$this->EC->closeEbay();

		if ( $this->EC->isSuccess ) {
			return true;
		} else {
			return false;					
		}

	}

	// ================================ PUT /listings/id/end ================================ 

	/**
	 * End a listing
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function end_listing( $request ) {

		$account_id = isset( $request['id'] ) ? WPLE_ListingQueryHelper::getAccountID( $request['id'] ) : false;
		$this->initEC( $account_id );
		$this->EC->endItemsOnEbay( $request['id'] );
		$this->EC->closeEbay();

		if ( $this->EC->isSuccess ) {
			return true;
		} else {
			return false;
		}

	}

	// ================================ PUT /listings/id/lock ================================ 

	/**
	 * Lock a listing
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function lock_listing( $request ) {

		$account_id = isset( $request['id'] ) ? WPLE_ListingQueryHelper::getAccountID( $request['id'] ) : false;
		$id = $request['id'];
        $data = array( 'locked' => true );
		ListingsModel::updateListing( $id, $data );
       
		return true;

	}

	// ================================ PUT /listings/id/reapply ================================ 

	/**
	 * Reapply profile to a listing
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function reapply_profile2listing( $request ) {

		$listingsModel = new ListingsModel();
        $listingsModel->reapplyProfileToItems( $request['id'] );

		return true;

	}

	// ================================ PUT /listings/id/unlock ================================ 

	/**
	 * Unlock a listing
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function unlock_listing( $request ) {

		$account_id = isset( $request['id'] ) ? WPLE_ListingQueryHelper::getAccountID( $request['id'] ) : false;
		$id = $request['id'];
        $data = array( 'locked' => false );
		ListingsModel::updateListing( $id, $data );
       
		return true;

	}

	// ================================ PUT /listings/id/cleareps ================================ 

	/**
	 * Clear a listing's eps
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function clear_eps( $request ) {

		ListingsModel::updateWhere( 
	     	array( 'id' => $request['id'] ), 
	       	array( 'eps' => '' )
	    );
       
		return true;

	}

	// ================================ PUT /listings/id/resetstatus ================================ 

	/**
	 * Reset the status of a listing
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function reset_status( $request ) {

		$lm = new ListingsModel();
        $id = $request['id'];
        $data = array( 
			'status'         => 'prepared',
			'ebay_id'        => NULL,
			'end_date'       => NULL,
			'date_published' => NULL,
			'last_errors'    => '',
        );

        $status = WPLE_ListingQueryHelper::getStatus( $id );
    	if ( ! in_array( $status, array('ended','sold','archived') ) ) {
    		return false;
    	}
        ListingsModel::updateListing( $id, $data );
        $lm->reapplyProfileToItem( $id );

        return true;

	}

	// ================================ PUT /listings/id/archive ================================ 

	/**
	 * Archive a listing
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function archive_listing( $request ) {

		$id = $request['id'];
        $data = array( 'status' => 'archived' );
        ListingsModel::updateListing( $id, $data );

		return true;

	}

	// ================================ PUT /listings/id/cancelsched ================================ 

	/**
	 * Cancel schedule of a listing
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function cancel_schedule( $request ) {

        $id = $request['id'];
        $data = array( 'relist_date' => null );
        ListingsModel::updateListing( $id, $data );

        return true;

	}

	// ================================ PUT /listings/id/updatefromebay ================================ 

	/**
	 * Update a listing from ebay
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function update_from_ebay( $request ) {

		$account_id = isset( $request['id'] ) ? WPLE_ListingQueryHelper::getAccountID( $request['id'] ) : false;
		$this->initEC( $account_id );
		$this->EC->updateItemsFromEbay( $request['id'] );
		$this->EC->closeEbay();

        return true;

	}

	// ================================ DELETE /listings/id ================================

	/**
	 * Delete a single listing
	 *
	 * @param WP_REST_Request $request Full details about the request
	 * @return WP_Error|WP_REST_Response
	 */
	public function delete_listing( $request ) {
		
		$id = $request['id'];
		WPLE_ListingQueryHelper::deleteItem( $id );

	}

}