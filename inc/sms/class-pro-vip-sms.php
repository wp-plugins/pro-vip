<?php

if ( ! defined( 'WPINC' ) ) {
	die;
}

final class Pro_VIP_SMS {


	public static function getInstance() {
		static $instance;
		if ( empty( $instance ) ) {
			$instance = new self;
		}

		return $instance;
	}

	protected function __construct() {
		if ( ! in_array( 'wp-sms/wp-sms.php', get_option( 'active_plugins', array() ) ) && isset( $_GET[ 'page' ] ) && $_GET[ 'page' ] === 'provip_settings' ) {
			Pro_VIP_Admin::addNotice( sprintf( __( '<a href="%s">WP SMS</a> plugin is not installed. to use sms feature install this plugin.', 'provip' ), 'https://wordpress.org/plugins/wp-sms/' ) );

			return false;
		}
		Pro_VIP_SMS_Actions::instance();
	}


}
