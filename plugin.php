<?php
/**
 * Plugin Name: WPLISTER EBAY REST API
 * Plugin URI: https://www.wplab.com/
 * Description: A toolkit that helps you create, retrieve, update and delete wplister data
 * Version: 1.0
 * Author: Hamilton Nieri
 * Author URI: https://www.wplab.com/
 * License: GNU General Public License v3.0 
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * ----------------------------------------------------------------------
 * Copyright (C) 2016  Hamilton Nieri  (Email: hamiltonnieri8755@yahoo.com)
 * ----------------------------------------------------------------------
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * ----------------------------------------------------------------------
 */

// Including WP core file
if ( ! function_exists( 'get_plugins' ) )
    require_once ABSPATH . 'wp-admin/includes/plugin.php';

/**
 * WPL_REST_Listings_Controller class.
 */
if ( ! class_exists( 'WPL_REST_Listings_Controller' ) ) {
	require_once dirname( __FILE__ ) . '/endpoints/class-wple-rest-listings-controller.php';
}

/**
 * WPL_REST_Profiles_Controller class.
 */
if ( ! class_exists( 'WPL_REST_Profiles_Controller' ) ) {
	require_once dirname( __FILE__ ) . '/endpoints/class-wple-rest-profiles-controller.php';
}

/**
 * WPL_REST_Accounts_Controller class.
 */
if ( ! class_exists( 'WPL_REST_Accounts_Controller' ) ) {
	require_once dirname( __FILE__ ) . '/endpoints/class-wple-rest-accounts-controller.php';
}

if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) :

add_action( 'rest_api_init', 'register_wplister_route' );

function register_wplister_route() {

	// Listings Controller
	$listings_controller = new WPLE_REST_Listings_Controller;
	$listings_controller->register_routes();

	// Profiles Controller 
	$profiles_controller = new WPLE_REST_Profiles_Controller;
	$profiles_controller->register_routes();

	// Accounts Controller 
	$accounts_controller = new WPLE_REST_Accounts_Controller;
	$accounts_controller->register_routes();

}

endif;

/*
NOTES:

***listings endpoint

GET    /listings    - Retrieves a list of ebay listings
GET    /listings/id - Retrieves a specific ebay listing
POST   /listings    - Creates a new ebay listing
PUT    /listings/id - Updates a specific ebay listing 
PATCH  /listings/id - Partially updates a listing
DELETE /listings/id - Deletes a specific listing

Filtering : 
GET /listings?status=[ published | sold | archived | ended | prepared | changed ]
GET /listings?account_id=
GET /listings?site_id=
GET /listings?auction_type=
GET /listings?locked=
GET /listings?profile_id=

Sorting   : 
GET /listings?sort= [ date_created | date_published | date_finished | end_date | relist_date | price ]

Limiting  :
GET /listings?fields=id,ebay_id,auction_title,acution_type,...

Action    :
PUT /listings/id/verify
PUT /listings/id/publish
PUT /listings/id/end
PUT /listings/id/relist
PUT /listings/id/lock
PUT /listings/id/unlock
PUT /listings/id/revise

***profiles endpoint

GET    /profiles    - Retrieves a list of profiles
GET    /profiles/id - Retrieves a specific profile
POST   /profiles    - Creates a new profile
PUT    /profiles/id - Updates a specific profile
PATCH  /profiles/id - Partially updates a specific profile
DELETE /profiles/id - Deletes a specific profile

Filtering :
GET /profiles?condition=
GET /profiles?account_id=
GET /profiles?site_id=

Sorting   :
GET /profiles?sort=[ profile_id | profile_name ]

Limiting  :
GET /profiles?fields=profile_id,profile_name,profile_description,...

Action    :

***accounts endpoint

GET     /accounts     - Retrieves a list of accounts
GET     /accounts/id  - Retrieves a specific account
POST    /accounts     - Creates a new account
PUT     /accounts/id  - Updates a specific account
PATCH   /accounts/id  - Partially updates a specific account
DELETE  /accounts/id  - Deletes a specific account

Filtering :
GET /accounts?active=
GET /accounts?site_id=

Sorting   :
GET /accounts?sort=[ id | title ]

Limiting  :
GET /accounts?fields=id,title,site_id,site_code,sandbox_mode,...

Action    :


*/