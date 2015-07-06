<?php
/*
Plugin Name: Pro-VIP
Plugin URI: http://pro-wp.ir/wp-vip
Description: Wordpress VIP Plugin
Author: Pro-WP Team
Version: 0.1
Author URI: http://pro-wp.ir
*/

if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'Pro_VIP_PLUGIN_FILE', __FILE__ );
define( 'Pro_VIP_PATH', trailingslashit( plugin_dir_path( Pro_VIP_PLUGIN_FILE ) ) );
define( 'Pro_VIP_URL', trailingslashit( plugin_dir_url( Pro_VIP_PLUGIN_FILE ) ) );

if ( ! class_exists( 'Pro_VIP' ) ) {
  require dirname( __FILE__ ) . '/class-pro-vip.php';
}

function pv(){
  return Pro_VIP::getInstance();
}
pv();





register_activation_hook( Pro_VIP_PLUGIN_FILE, 'wvActivation' );

function wvActivation() {

	global $wpdb;
	$charset_collate = $wpdb->get_charset_collate();
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );


	$table = $wpdb->prefix . 'vip_users';
	$sql   = "CREATE TABLE $table IF NOT EXISTS (
  ID bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  user_ID bigint(20) unsigned NOT NULL,
  vip_level varchar(255) CHARACTER SET utf8 DEFAULT 'vip',
  start_date datetime DEFAULT NULL,
  update_date datetime DEFAULT NULL,
  expiration_date datetime DEFAULT NULL,
  PRIMARY KEY (ID),
  KEY user_ID (user_ID)
) $charset_collate;";

	dbDelta( $sql );


	$table = $wpdb->prefix . 'vip_purchases';
	$sql   = "CREATE TABLE $table IF NOT EXISTS (
  ID bigint(20) NOT NULL AUTO_INCREMENT,
  user_ID bigint(20) unsigned NOT NULL,
  purchase_date datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  file_ID bigint(20) unsigned NOT NULL,
  file_index int(11) NOT NULL,
  ip varchar(15) NOT NULL,
  PRIMARY KEY (ID),
  KEY user_ID (user_ID),
  KEY file_ID (file_ID),
  KEY file_index (file_index)
) $charset_collate;";

	dbDelta( $sql );


}


