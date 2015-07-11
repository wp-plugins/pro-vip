<?php
/**
 * @class          Pro_VIP_Member
 * @version        1.0
 * @package        Pro-VIP
 * @category       Class
 * @author         Pro-WP Team
 */

defined( 'ABSPATH' ) or die; // Prevents direct access

class Pro_VIP_Member {

	public static $table;

	public static function instance() {
		static $instance;
		if ( ! is_object( $instance ) ) {
			$instance = new self;
		}

		return $instance;
	}

	protected function __construct() {
		global $wpdb;
		self::$table = $wpdb->prefix . 'vip_users';
		require PRO_VIP_PATH . 'inc/member/functions.php';

		if ( pvGetOption( 'delete_expired_users', 'no' ) == 'yes' ) {
			Pro_VIP_Cron::daily( array( __CLASS__, 'deleteExpiredUsers' ) );
		}
	}

	public static function isVip( $user, $level ) {
		$user = pvVipGetUser( $user );
		if ( ! $user ) {
			return - 1;
		}

		global $wpdb;

		$currentDate = current_time( 'mysql' );

		$levels = (array) $level;
		$levels = array_filter( $levels, 'is_numeric' );

		if ( empty( $levels ) ) {
			return apply_filters( 'pro_vip_is_vip', false, $user );
		}

		$levels = implode( ',', $levels );

		$sql = "SELECT EXISTS(SELECT 1 FROM " . self::$table . " WHERE user_ID = %d AND expiration_date > '{$currentDate}' AND vip_level IN ({$levels}))";
		$sql = $wpdb->prepare( $sql, $user );


		return apply_filters( 'pro_vip_is_vip_return', (bool) $wpdb->get_var( $sql ), $user );
	}

	public static function getExpirationDate( $user, $level = null ) {
		$user = pvVipGetUser( $user );
		if ( ! $user ) {
			return - 1;
		}

		if ( empty( $level ) ) {
			$level = pvGetOption( 'default_vip_level', '' );
		}

		global $wpdb;

		$query = "SELECT expiration_date FROM " . self::$table . " WHERE user_id = %d AND vip_level = '{$level}'";
		$query = apply_filters( 'pro_vip_get_expiration_date_sql_query', $query, $user );
		$query = $wpdb->prepare( $query, $user );


		return $wpdb->get_var( $query );
	}

	public static function increaseVip( $seconds, $user, $level = 'vip' ) {

		$user = pvVipGetUser( $user );
		if ( ! $user ) {
			return - 1;
		}

		global $wpdb;

		$currentDate = current_time( 'mysql' );
		$table       = self::$table;
		$seconds     = (int) $seconds;


		$expireDate = $wpdb->get_var( $wpdb->prepare( "SELECT `expiration_date` FROM {$table} WHERE user_ID = %d AND vip_level = %s", $user, $level ) );


		if ( empty( $expireDate ) ) {
			if ( 0 > $seconds ) {
				return false;
			}

			return $wpdb->insert(
				$table,
				array(
					'user_ID'         => $user,
					'vip_level'       => $level,
					'start_date'      => $currentDate,
					'expiration_date' => gmdate( 'Y-m-d H:i:s', time() + $seconds )
				)
			);

		} else if ( time() >= strtotime( $expireDate ) ) {
			if ( 0 > $seconds ) {
				return false;
			}

			$date  = gmdate( 'Y-m-d H:i:s', time() + $seconds );
			$query = "UPDATE {$table} SET expiration_date = '{$date}', update_date = '{$currentDate}' WHERE user_ID = %d AND vip_level = %s";
		} else {
			$query = "UPDATE " . self::$table . " SET expiration_date = DATE_ADD(expiration_date, INTERVAL $seconds SECOND), update_date = '{$currentDate}' WHERE user_ID = %d AND vip_level = %s";
		}


		$query = apply_filters( 'pro_vip_increase_vip_sql_query', $query, $user );
		$query = $wpdb->prepare( $query, $user, $level );

		return (bool) $wpdb->query( $query );
	}

	public static function getVipAccounts( $user ) {
		$user = pvVipGetUser( $user );
		if ( ! $user ) {
			return - 1;
		}

		global $wpdb;
		$table       = self::$table;
		$currentDate = current_time( 'mysql' );


		$sql = "SELECT * FROM {$table} WHERE user_ID = %d AND expiration_date > '{$currentDate}'";
		$sql = $wpdb->prepare( $sql, $user );

		return $wpdb->get_results( $sql );

	}

	public static function getLevelData( $level, $user = null ) {
		$user = pvVipGetUser( $user );
		if ( ! $user ) {
			return false;
		}

		global $wpdb;
		$table       = self::$table;
		$currentDate = current_time( 'mysql' );


		$sql = "SELECT * FROM {$table} WHERE user_ID = %d AND vip_level = %d AND expiration_date > '{$currentDate}'";
		$sql = $wpdb->prepare( $sql, $user, $level );

		$query = $wpdb->get_row( $sql, ARRAY_A );

		return empty( $query ) ? false : $query;
	}

	public static function deleteExpiredUsers() {

		global $wpdb;

		$currentDate = current_time( 'mysql' );
		$table       = $wpdb->prefix . 'vip_users';

		$sql = "DELETE FROM {$table} WHERE '{$currentDate}' > expiration_date";

		return $wpdb->query( $sql );

	}

}
