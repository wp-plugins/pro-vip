<?php
/**
 * Pro-VIP API
 *
 * Handles PV-API endpoint requests
 *
 * @author      Pro-WP
 * @category    API
 * @package     Pro-VIP
 * @since       0.1.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class PV_API {

	/** This is the major version for the REST API and takes
	 * first-order position in endpoint URLs
	 */
	const VERSION = 1;

	/** @var PV_API_Server the REST API server */
	public $server;


	public static function instance() {
		static $instance;
		if ( ! is_object( $instance ) ) {
			$instance = new self;
		}

		return $instance;
	}


	/**
	 * Setup class
	 *
	 * @access public
	 * @since  0.1.2
	 * @return PV_API
	 */
	protected function __construct() {

		require dirname( __FILE__ ) . '/api-functions.php';

		// add query vars
		add_filter( 'query_vars', array( $this, 'add_query_vars' ), 0 );

		// register API endpoints
		add_action( 'init', array( $this, 'add_endpoint' ), 0 );

		// handle REST API requests
		add_action( 'parse_request', array( $this, 'handle_rest_api_requests' ), 0 );

		// handle pv-api endpoint requests
		add_action( 'parse_request', array( $this, 'handle_api_requests' ), 0 );
	}

	/**
	 * add_query_vars function.
	 *
	 * @access public
	 * @since  0.1.2
	 *
	 * @param $vars
	 *
	 * @return string[]
	 */
	public function add_query_vars( $vars ) {
		$vars[ ] = 'pv-api';
		$vars[ ] = 'pv-api-version';
		$vars[ ] = 'pv-api-route';

		return $vars;
	}

	/**
	 * add_endpoint function.
	 *
	 * @access public
	 * @since  0.1.2
	 * @return void
	 */
	public function add_endpoint() {

		// REST API
		add_rewrite_rule( '^pv-api/v([1-2]{1})/?$', 'index.php?pv-api-version=$matches[1]&pv-api-route=/', 'top' );
		add_rewrite_rule( '^pv-api/v([1-2]{1})(.*)?', 'index.php?pv-api-version=$matches[1]&pv-api-route=$matches[2]', 'top' );

		// PV API for payment gateway IPNs, etc
		add_rewrite_endpoint( 'pv-api', EP_ALL );
	}


	/**
	 * Handle REST API requests
	 *
	 * @since 2.2
	 */
	public function handle_rest_api_requests() {
		global $wp;

		if ( ! empty( $_GET[ 'pv-api-version' ] ) ) {
			$wp->query_vars[ 'pv-api-version' ] = $_GET[ 'pv-api-version' ];
		}

		if ( ! empty( $_GET[ 'pv-api-route' ] ) ) {
			$wp->query_vars[ 'pv-api-route' ] = $_GET[ 'pv-api-route' ];
		}

		// REST API request
		if ( ! empty( $wp->query_vars[ 'pv-api-version' ] ) && ! empty( $wp->query_vars[ 'pv-api-route' ] ) ) {

			define( 'PV_API_REQUEST', true );
			define( 'PV_API_REQUEST_VERSION', absint( $wp->query_vars[ 'pv-api-version' ] ) );

			// legacy v1 API request
			if ( 1 === PV_API_REQUEST_VERSION ) {

				$this->includes();

				$this->server = new PV_API_Server( $wp->query_vars[ 'pv-api-route' ] );

				// load API resource classes
				$this->register_resources( $this->server );

				// Fire off the request
				$this->server->serve_request();

			}

			exit;
		}
	}

	/**
	 * Include required files for REST API request
	 *
	 * @since 2.1
	 */
	public function includes() {
		// allow plugins to load other response handlers or resource classes
		do_action( 'pro_vip_api_loaded' );
	}

	/**
	 * Register available API resources
	 *
	 * @since 2.1
	 *
	 * @param PV_API_Server $server the REST server
	 */
	public function register_resources( $server ) {

		$classes = array();

		if ( pvGetOption( 'user_authentication_api', 'no' ) === 'yes' ) {
			$classes[ ] = 'PV_API_VIP_Authentication';
		}

		$api_classes = apply_filters( 'pro_vip_api_classes', $classes );

		foreach ( $api_classes as $api_class ) {
			$this->$api_class = new $api_class( $server );
		}
	}


	/**
	 * API request - Trigger any API requests
	 *
	 * @access public
	 * @since  0.1.2
	 * @return void
	 */
	public function handle_api_requests() {
		global $wp;

		if ( ! empty( $_GET[ 'pv-api' ] ) ) {
			$wp->query_vars[ 'pv-api' ] = $_GET[ 'pv-api' ];
		}

		// pv-api endpoint requests
		if ( ! empty( $wp->query_vars[ 'pv-api' ] ) ) {

			// Buffer, we won't want any output here
			ob_start();

			// Get API trigger
			$api = strtolower( esc_attr( $wp->query_vars[ 'pv-api' ] ) );

			// Load class if exists
			if ( class_exists( $api ) ) {
				new $api();
			}

			// Trigger actions
			do_action( 'pro_vip_api_' . $api );

			// Done, clear buffer and exit
			ob_end_clean();
			die( '1' );
		}
	}
}

