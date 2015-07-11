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

		add_action( 'wp_enqueue_scripts', array( $this, 'enqueueAssets' ) );

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

	public function enqueueAssets() {

//		if ( ! isPV() ) {
//			return false;
//		}

		wp_enqueue_style( 'wv-styles', PRO_VIP_URL . 'templates/assets/css/styles.css' );
		wp_enqueue_script(
			'wv-jquery-modal',
			PRO_VIP_URL . 'templates/assets/js/plugins/jquery.modal.min.js',
			array(
				'jquery'
			)
		);

		wp_enqueue_script( 'wv-scripts', PRO_VIP_URL . 'templates/assets/js/general.js', array(
			'jquery'
		) );
		wp_localize_script( 'wv-scripts', 'proVip', array(
			'ajaxurl' => admin_url( 'admin-ajax.php' )
		) );

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
}






