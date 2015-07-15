<?php
/**
 * Pro-VIP API
 *
 * Handles parsing JSON request bodies and generating JSON responses
 *
 * @author      Pro-WP
 * @category    API
 * @package     Pro-VIP/API
 * @since       0.1.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if( !interface_exists('PV_API_Handler') ){
	require dirname(__FILE__) . '/interface-pv-api-handler.php';
}

class PV_API_JSON_Handler implements PV_API_Handler {

	/**
	 * Get the content type for the response
	 *
	 * @since 0.1.2
	 * @return string
	 */
	public function get_content_type() {

		return sprintf( '%s; charset=%s', isset( $_GET[ '_jsonp' ] ) ? 'application/javascript' : 'application/json', get_option( 'blog_charset' ) );
	}

	/**
	 * Parse the raw request body entity
	 *
	 * @since 0.1.2
	 *
	 * @param string $body the raw request body
	 *
	 * @return array|mixed
	 */
	public function parse_body( $body ) {

		return json_decode( $body, true );
	}

	/**
	 * Generate a JSON response given an array of data
	 *
	 * @since 0.1.2
	 *
	 * @param array $data the response data
	 *
	 * @return string
	 */
	public function generate_response( $data ) {

		if ( isset( $_GET[ '_jsonp' ] ) ) {

			// JSONP enabled by default
			if ( ! apply_filters( 'provip_api_jsonp_enabled', true ) ) {

				pv()->api->server->send_status( 400 );

				$data = array(
					array(
						'code'    => 'provip_api_jsonp_disabled',
						'message' => __( 'JSONP support is disabled on this site', 'provip' )
					)
				);
			}

			// Check for invalid characters (only alphanumeric allowed)
			if ( preg_match( '/\W/', $_GET[ '_jsonp' ] ) ) {

				pv()->api->server->send_status( 400 );

				$data = array(
					array(
						'code' => 'provip_api_jsonp_callback_invalid',
						__( 'The JSONP callback function is invalid', 'provip' )
					)
				);
			}

			// see http://miki.it/blog/2014/7/8/abusing-jsonp-with-rosetta-flash/
			pv()->api->server->header( 'X-Content-Type-Options', 'nosniff' );

			// Prepend '/**/' to mitigate possible JSONP Flash attacks
			return '/**/' . $_GET[ '_jsonp' ] . '(' . json_encode( $data ) . ')';
		}

		return json_encode( $data );
	}

}
