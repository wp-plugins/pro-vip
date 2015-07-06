<?php
/**
 * @class          PV_Framework_Action
 * @version        1.0
 * @package        Phoenix Framework
 * @category       Class
 * @author         Vahidd
 */

defined( 'ABSPATH' ) or die; // Prevents direct access

class PV_Framework_Action {

	protected static $_actions = array();


	static function init() {
		add_action( 'init', array( __CLASS__, 'check_action' ), 9999 * 999 );
	}

	static function check_action() {
		if (
			array_key_exists( (string) PV_Framework_Request::post_get( 'pv_action' ), self::$_actions )
			&& ! empty( $_REQUEST[ '_wpnonce' ] )
			&& is_string( $_REQUEST[ '_wpnonce' ] )
			&& wp_verify_nonce( $_REQUEST[ '_wpnonce' ], "pv_action_{$_REQUEST['pv_action']}" )
		) {
			$action = self::$_actions[ $_REQUEST[ 'pv_action' ] ];
			if ( $action[ 'post_only' ] && strtolower( $_SERVER[ 'REQUEST_METHOD' ] ) !== 'post' ) {
				return;
			}
			if ( $action[ 'admin_only' ] && ! is_admin() ) {
				return;
			}

			call_user_func( $action[ 'callback' ] );
		}
	}

	/**
	 * @param          $action
	 * @param callback $callback
	 * @param array    $settings
	 *
	 * @return $this
	 */
	static function make( $action, $callback, Array $settings = array() ) {
		self::$_actions[ $action ] = array_merge(
			array(
				'callback'   => $callback,
				'post_only'  => false,
				'admin_only' => false
			),
			$settings
		);
	}

	static function action_exists( $action_id ) {
		return array_key_exists( $action_id, self::$_actions );
	}

	static function action_url( $action_id, $admin = false, $custom_params = array() ) {
		return add_query_arg(
			array_merge(
				$custom_params,
				array(
					'pv_action' => $action_id,
					'_wpnonce'  => wp_create_nonce( 'pv_action_' . $action_id )
				)
			),
			trailingslashit( $admin ? admin_url() : site_url() )
		);
	}

	public static function hidden_inputs( $action_id ) {

		if ( ! did_action( 'plugins_loaded' ) ) {
			_doing_it_wrong( __FUNCTION__, 'Should be called in plugins_loaded or init action.', '1.0' );

			return '';
		}

		$html = sprintf( '<input type="hidden" name="%s" value="%s"/>', 'pv_action', $action_id );
		$html .= sprintf( '<input type="hidden" name="%s" value="%s"/>', '_wpnonce', wp_create_nonce( 'pv_action_' . $action_id ) );

		return $html;
	}

}
