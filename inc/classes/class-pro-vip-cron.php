<?php
/**
 * @class          Pro_VIP_Cron
 * @version        1.0
 * @package        Pro-VIP
 * @category       Class
 * @author         Pro-WP Team
 */

defined( 'ABSPATH' ) or die; // Prevents direct access

class Pro_VIP_Cron {

	protected static $_callbacks = array(
		'daily'  => array(),
		'hourly' => array()
	);

	public static function instance() {
		static $instance;
		if ( ! is_object( $instance ) ) {
			$instance = new self;
		}

		return $instance;
	}

	protected function __construct() {
		add_action( 'wp_loaded', array( $this, '_dailyActions' ) );
		add_action( 'wp_loaded', array( $this, '_hourlyActions' ) );
	}

	public static function _registerJobs() {
		wp_schedule_event( time(), 'hourly', 'pro_vip_daily_event' );
	}

	public static function _clearJobs() {
		wp_clear_scheduled_hook( 'pro_vip_daily_event' );
	}

	public static function daily( $callback ) {
		if ( ! is_callable( $callback ) ) {
			throw new Exception( 'callback is not callable.' );
		}
		self::$_callbacks[ 'daily' ][ ] = $callback;
	}

	public static function hourly( $callback ) {
		if ( ! is_callable( $callback ) ) {
			throw new Exception( 'callback is not callable.' );
		}
		self::$_callbacks[ 'hourly' ][ ] = $callback;
	}

	public function _dailyActions() {

		if ( get_transient( '_pvDailyActions' ) !== false ) {
			return false;
		}

		set_transient( '_pvDailyActions', time(), DAY_IN_SECONDS );

		foreach ( self::$_callbacks[ 'daily' ] as $callback ) {
			call_user_func( $callback );
		}

	}

	public function _hourlyActions() {

		if ( get_transient( '_pvHourlyActions' ) !== false ) {
			return false;
		}

		set_transient( '_pvHourlyActions', time(), HOUR_IN_SECONDS );

		foreach ( self::$_callbacks[ 'hourly' ] as $callback ) {
			call_user_func( $callback );
		}

	}

}
