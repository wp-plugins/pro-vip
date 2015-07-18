<?php
/**
 * @class          PV_Activation
 * @package        Pro-VIP
 * @category       Class
 * @author         Pro-WP Team
 */

defined( 'ABSPATH' ) or die; // Prevents direct access

class PV_Activation {

	public static function instance() {
		static $instance;
		if ( ! is_object( $instance ) ) {
			$instance = new self;
		}

		return $instance;
	}

	protected function __construct() {
		$this->createTables();
	}


	protected function createTables() {

		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		$table = $wpdb->prefix . 'vip_users';
		$sql   = "CREATE TABLE IF NOT EXISTS $table (
  ID bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  user_ID bigint(20) unsigned NOT NULL,
  vip_level varchar(255) DEFAULT '0',
  start_date datetime DEFAULT NULL,
  update_date datetime DEFAULT NULL,
  expiration_date datetime DEFAULT NULL,
  PRIMARY KEY (ID),
  KEY user_ID (user_ID)
) $charset_collate";

		dbDelta( $sql );


		$table = $wpdb->prefix . 'vip_purchases';
		$sql   = "CREATE TABLE IF NOT EXISTS $table (
  ID            BIGINT(20)  NOT NULL AUTO_INCREMENT,
  user_email    varchar(100) NOT NULL,
  purchase_date DATETIME    NOT NULL DEFAULT '0000-00-00 00:00:00',
  file_ID       BIGINT(20) UNSIGNED NOT NULL,
  file_index    INT(11)     NOT NULL,
  ip            VARCHAR(15) NOT NULL,
  PRIMARY KEY (ID),
  KEY user_email(user_email),
  KEY file_ID (file_ID),
  KEY file_index (file_index)
  ) $charset_collate";

		dbDelta( $sql );

	}


}
