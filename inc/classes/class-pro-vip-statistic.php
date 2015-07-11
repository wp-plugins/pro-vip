<?php
/**
 * @class          Pro_VIP_Statistic
 * @version        1.0
 * @package        Pro-VIP
 * @category       Class
 * @author         Pro-WP Team
 */

defined( 'ABSPATH' ) or die; // Prevents direct access

class Pro_VIP_Statistic {

	protected function __construct() {
	}

	protected static function _cAdd( $key, $value, $args = null ) {
		$key .= ! empty ( $args ) ? maybe_serialize( $args ) : '';
		$key = md5( $key );

		return wp_cache_add( $key, $value, 'pro_vip_statistic' );
	}

	protected static function _cGet( $key, $args = null ) {
		$key .= ! empty ( $args ) ? maybe_serialize( $args ) : '';
		$key = md5( $key );

		return wp_cache_get( $key, 'pro_vip_statistic' );
	}

	public static function getTotalVipMembers( $level = null, $distinct = true ) {

		if ( $cache = self::_cGet( __FUNCTION__, func_get_args() ) ) {
			return $cache;
		}

		global $wpdb;
		$table = $wpdb->prefix . 'vip_users';
		$sql   = "Select
     COUNT(" . ( $distinct ? 'DISTINCT ' : '' ) . "user_ID) As usersCount
 From $table";

		if ( is_numeric( $level ) ) {
			$sql .= $wpdb->prepare( ' WHERE vip_level = %d', $level );
		}

		$count = absint( $wpdb->get_var( $sql ) );


		self::_cAdd( __FUNCTION__, $count, func_get_args() );

		return $count;
	}

	public static function getFilePurchases( $from = null ) {

		if ( $cache = self::_cGet( __FUNCTION__, func_get_args() ) ) {
			return $cache;
		}

		global $wpdb;
		$table = $wpdb->prefix . 'vip_purchases';

		$sql = "SELECT COUNT(ID) FROM $table";

		if ( $from ) {
			$date = gmdate( 'Y-m-d H:i:s', time() - $from + ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS ) );
			$sql .= " WHERE purchase_date > '$date'";
		}

		return (int) $wpdb->get_var( $sql );

	}

	public static function getMostPurchasedFile() {

		if ( $cache = self::_cGet( __FUNCTION__ ) ) {
			return $cache;
		}

		global $wpdb;
		$table = $wpdb->prefix . 'vip_purchases';

		$sql = "SELECT file_ID as ID, COUNT(file_ID) AS purchaseCount
FROM `$table`
GROUP BY ID
ORDER BY purchaseCount DESC
LIMIT 1";

		return $wpdb->get_row( $sql );
	}

	public static function getMostRecentPurchases( $limit = 10 ) {
		if ( $cache = self::_cGet( __FUNCTION__ ) ) {
			return $cache;
		}
		global $wpdb;
		$table = $wpdb->prefix . 'vip_purchases';

		$sql = "SELECT *
FROM $table
ORDER BY purchase_date DESC";

		if ( $limit && is_numeric( $limit ) ) {
			$sql .= $wpdb->prepare( ' LIMIT %d', $limit );
		}

		return $wpdb->get_results( $sql );
	}

	public static function getLevelsUsers() {

		if ( $cache = self::_cGet( __FUNCTION__, func_get_args() ) ) {
			return $cache;
		}

		global $wpdb;
		$table = $wpdb->prefix . 'vip_users';


		$sql = "SELECT ";
		$sum = array();
		foreach ( pvGetLevels() as $levelId => $level ) {
			$sum[ ] = $wpdb->prepare( 'SUM( vip_level = %d ) AS %s', $levelId, $level );
		}

		if ( empty( $sum ) ) {
			return array();
		}


		$sql .= implode( ',', $sum ) . " ";
		$sql .= "FROM " . $table;

		$q = $wpdb->get_row( $sql, ARRAY_A );

		return ! empty( $q ) ? $q : array();
	}


	static function format( $number ) {
		$prefixes = 'kMGTPEZY';
		if ( $number >= 1000 ) {
			for ( $i = - 1; $number >= 1000; ++ $i ) {
				$number /= 1000;
			}

			return floor( $number ) . $prefixes[ $i ];
		}

		return $number;
	}
}
