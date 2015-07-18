<?php
/*
Plugin Name: Pro-VIP
Plugin URI: http://pro-wp.ir/wp-vip
Description: Wordpress VIP Plugin
Author: Pro-WP Team
Version: 0.1.4
Author URI: http://pro-wp.ir
*/

if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'PRO_VIP_PLUGIN_FILE', __FILE__ );
define( 'PRO_VIP_PATH', trailingslashit( plugin_dir_path( PRO_VIP_PLUGIN_FILE ) ) );
define( 'PRO_VIP_URL', trailingslashit( plugin_dir_url( PRO_VIP_PLUGIN_FILE ) ) );

if ( ! class_exists( 'Pro_VIP' ) ) {
	require dirname( __FILE__ ) . '/class-pro-vip.php';
}

function pv() {
	return Pro_VIP::getInstance();
}

pv();


register_activation_hook( PRO_VIP_PLUGIN_FILE, 'pvActivation' );

function pvActivation() {
	PV_Activation::instance();
	add_option( 'pv_flush_rules', 'true' );
}


register_deactivation_hook( PRO_VIP_PLUGIN_FILE, 'pvDeactivation' );

function pvDeactivation() {
	delete_option( 'pv_flush_rules' );
}