<?php
/**
 * Pro-VIP API Authenticate Class
 *
 * @author      Pro-WP
 * @category    API
 * @package     Pro-VIP/API
 * @since       0.1.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class PV_API_VIP_Authentication extends PV_API_Resource {

	/** @var string $base the route base */
	protected $base = '/vip-authenticate';


	/**
	 * Register the routes for this class
	 *
	 * GET /authenticate
	 *
	 * @since 0.1.2
	 *
	 * @param array $routes
	 *
	 * @return array
	 */
	public function register_routes( $routes ) {

		# GET /authenticate
		$routes[ $this->base ] = array(
			array( array( $this, 'authenticate' ), PV_API_Server::METHOD_POST )
		);


		return $routes;
	}


	public function authenticate() {

		$params = pv()->api->server->params[ 'POST' ];

		if( !empty( $params['user_id'] ) && is_numeric( $params[ 'user_id' ] ) ){
			$getBy = 'id';
			$val = $params['user_id'];
		}
		else if ( !empty( $params[ 'username' ] ) && is_string( $params[ 'username' ] )){
			$getBy = 'login';
			$val   = $params[ 'username' ];
		}
		else {
			$error                     = new WP_Error( 'provip_api_authenticate_user_not_defined', __( 'User not defined.', 'provip' ) );
			$error->errors[ 'res' ] = 0;

			return $error;
		}

		$user = get_user_by( $getBy, $val );

		if ( ! $user ) {
			$error                  = new WP_Error( 'provip_api_authenticate_user_not_found', __( 'User not found.', 'provip' ) );
			$error->errors[ 'res' ] = 0;

			return $error;
		}

		if ( empty( $params[ 'level' ] ) || ! is_numeric( $params[ 'level' ] ) ) {
			$error                     = new WP_Error( 'provip_api_authenticate_empty_level', __( 'Empty level', 'provip' ) );
			$error->errors[ 'res' ] = 0;

			return $error;
		}


		$isVIP = Pro_VIP_Member::isVip( $user->ID, $params[ 'level' ] );


		if ( $isVIP ) {
			return array( 'res' => 1 );
		} else {
			$error                     = new WP_Error( 'provip_api_authenticate_user_not_vip', __( 'User is not in the VIP level.', 'provip' ) );
			$error->errors[ 'res' ] = 0;

			return $error;
		}
	}

}
