<?php
/**
 * Pro-VIP API
 *
 * Defines an interface that API request/response handlers should implement
 *
 * @author      Pro-WP
 * @category    API
 * @package     Pro-VIP/API
 * @since       0.1.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

interface PV_API_Handler {

	/**
	 * Get the content type for the response
	 *
	 * This should return the proper HTTP content-type for the response
	 *
	 * @since 0.1.2
	 * @return string
	 */
	public function get_content_type();

	/**
	 * Parse the raw request body entity into an array
	 *
	 * @since 0.1.2
	 * @param string $data
	 * @return array
	 */
	public function parse_body( $data );

	/**
	 * Generate a response from an array of data
	 *
	 * @since 0.1.2
	 * @param array $data
	 * @return string
	 */
	public function generate_response( $data );

}
