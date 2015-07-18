<?php

if ( ! defined( 'WPINC' ) ) {
	die;
}

final class Pro_VIP {

	protected
		$_classesList;

	protected static
		$config,
		$_registry = array();

	/**
	 * @var PV_Framework_AJAX
	 */
	public static $ajax;

	public static $session;

	/**
	 * @var PV_API
	 */
	public $api;

	public
		$version = '0.1.4',
		$dbVersion = '20';

	public static function getInstance() {
		static $instance;
		if ( empty( $instance ) ) {
			$instance = new self;
		}

		return $instance;
	}

	protected function __construct() {

		$this::$config = require PRO_VIP_PATH . '/inc/config.php';

		load_plugin_textdomain( 'provip', false, dirname( plugin_basename( PRO_VIP_PLUGIN_FILE ) ) . '/languages' );


		require PRO_VIP_PATH . '/inc/functions.php';
		require PRO_VIP_PATH . '/inc/libs/framework/functions.php';

		$this->_classesList = require dirname( __FILE__ ) . '/inc/classes-list.php';
		spl_autoload_register( array( $this, 'splCallback' ) );

		$this::$ajax = new PV_Framework_AJAX( 'pro_vip' );


		$this->initSession();

		Pro_VIP_Framework::instance();

		Pro_VIP_Cron::instance();
		Pro_VIP_Currency::instance();
		Pro_VIP_Member::instance();
		Pro_VIP_Actions::instance();
		Pro_VIP_Payment_Gateway::init();
		Pro_VIP_Admin::instance();
		Pro_VIP_Email::instance();
		Pro_VIP_Notices::instance();
		Pro_VIP_Frontend_AJAX::instance();
		Pro_VIP_Filter_Content::instance();
		Pro_VIP_Shortcodes::instance();
		Pro_VIP_Template::instance();
		Pro_VIP_SMS::getInstance();
		PV_Custom_Payment::instance();
		PV_Single_File_Purchase::instance();

		if ( pvGetOption( 'enable_api', 'no' ) == 'yes' ) {
			$this->api = PV_API::instance();
		}


		add_action( 'init', array( $this, 'flushRewriteRules' ), 9999 );


		do_action( 'pro_vip_init', $this );

//		pvAddNotice( 'Hello World :D' );


	}

	public function splCallback( $class ) {
		if ( array_key_exists( $class, $this->_classesList ) ) {
			require dirname( __FILE__ ) . '/inc/' . $this->_classesList[ $class ];
		}
	}

	public static function set( $var, $value, $group = '' ) {
		self::$_registry[ $group ][ $var ] = $value;
	}

	public static function get( $var, $group = '', $default = false ) {
		return is_null( $var ) ? ( ! empty( self::$_registry[ $group ] ) ? self::$_registry[ $group ] : $default ) : ( isset( self::$_registry[ $group ][ $var ] ) ? self::$_registry[ $group ][ $var ] : $default );
	}

	public static function config( $key, $default = false ) {
		return apply_filters( 'pro_vip_config', isset( self::$config[ $key ] ) ? self::$config[ $key ] : $default, $key );
	}


	/**
	 * @param       $filename
	 * @param array $vars
	 *
	 * @param bool  $capture_buffer
	 *
	 * @return $this|mixed
	 */
	public static function loadView( $filename, $vars = array(), $capture_buffer = false ) {

		$folder = PRO_VIP_PATH . '/inc/views/';

		$view_name = preg_replace( '/\.php$/', '', $filename ) . '.php';

		if ( file_exists( $folder . $view_name ) ) {
			extract( $vars );
			global $post, $wp_query, $wp;

			if ( $capture_buffer ) {
				ob_start();
				include $folder . $view_name;

				return ob_get_clean();
			}

			return include $folder . $view_name;
		}

		return false;

	}

	protected function initSession() {
		if ( ! defined( 'WP_SESSION_COOKIE' ) ) {
			define( 'WP_SESSION_COOKIE', 'pv_wp_session' );
		}

		if ( ! class_exists( 'Recursive_ArrayAccess' ) ) {
			require_once PRO_VIP_PATH . 'inc/libs/recursive-arrayaccess.php';
		}

		if ( ! class_exists( 'WP_Session' ) ) {
			require_once PRO_VIP_PATH . 'inc/libs/wp-session/wp-session.php';
			require_once PRO_VIP_PATH . 'inc/libs/wp-session/wp-session-functions.php';
		}
		$this::$session = WP_Session::get_instance();
	}

	public function flushRewriteRules() {
		if ( get_option( 'pv_flush_rules' ) == 'true' ) {
			flush_rewrite_rules();
			delete_option( 'pv_flush_rules' );
		}
	}

}






