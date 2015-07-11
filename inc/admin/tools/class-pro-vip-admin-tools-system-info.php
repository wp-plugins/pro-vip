<?php
/**
 * @class          Pro_VIP_Admin_Tools_System_Info
 * @version        1.0
 * @package        Pro-VIP
 * @category       Class
 * @author         Pro-WP
 */

defined( 'ABSPATH' ) or die; // Prevents direct access

class Pro_VIP_Admin_Tools_System_Info {


	public static function instance() {
		static $instance;
		if ( ! is_object( $instance ) ) {
			$instance = new self;
		}

		return $instance;
	}


	protected function __construct() {
		Pro_VIP_Admin_Tools::add(
			__( 'System Info', 'provip' ),
			array( $this, 'callback' )
		);
	}

	public function callback() {
		Pro_VIP::loadView(
			'admin/tools/system-info',
			array(
				'data' => $this->getData()
			)
		);
	}

	public function getData() {

		$output = array();

		$output[ __( 'Wordpress', 'provip' ) ] = array(

			__( 'Site URL', 'provip' )          => get_option( 'siteurl' ),
			__( 'Home URL', 'provip' )          => get_option( 'home' ),
			__( 'Admin Email', 'provip' )       => get_option( 'admin_email' ),
			__( 'Wordpress Version', 'provip' ) => $GLOBALS[ 'wp_version' ],
			__( 'Debug Mode', 'provip' )        => WP_DEBUG,
			__( 'Language', 'provip' )          => get_locale(),

		);


		$output[ __( 'User Browser', 'provip' ) ] = array(
			__( 'User Agent String', 'provip' ) => $_SERVER[ 'HTTP_USER_AGENT' ]

		);


		return $output;
	}


}
