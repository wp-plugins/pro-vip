<?php
/**
 * @class          Pro_VIP_Notices
 * @version        1.0
 * @package        Pro-VIP
 * @category       Class
 * @author         Pro-WP Team
 */

defined( 'ABSPATH' ) or die; // Prevents direct access

class Pro_VIP_Notices {

	public static $notices = array();

	public static function instance() {
		static $instance;
		if ( ! is_object( $instance ) ) {
			$instance = new self;
		}

		return $instance;
	}

	protected function __construct() {
		$this->initializeDefaultNotices();
		add_action( 'init', array( $this, 'handleRequestNotices' ) );
	}

	public static function noticeType( $type, $def = 'error' ) {
		return is_bool( $type ) ? ( $type ? 'success' : 'error' ) : ( array_key_exists( $type, $types = array(
			'succeed' => 'success',
			'success' => 'success',
			'error'   => 'error',
			false     => 'error',
			0         => 'error',
			'updated' => 'success',
			true      => 'success',
			1         => 'success'
		) ) ? $types[ $type ] : $def );
	}

	protected function _getNotices( $thing = array() ) {
		if ( empty( $thing ) && ! empty( $_REQUEST[ 'wv-notice' ] ) ) {
			$thing = $_REQUEST[ 'wv-notice' ];
		}
		$notices = array();
		if ( is_array( $thing ) ) {
			foreach ( $thing as $noticeType => $notice ) {
				if ( ! isset( self::$notices[ $notice ] ) ) {
					continue;
				}
				$notices[ self::noticeType( $noticeType ) ][ ] = (int) $notice;
			}
		} else if ( is_string( $thing ) ) {
			foreach ( explode( ',', $thing ) as $notice ) {
				if ( is_numeric( $notice ) ) {
					$notices[ self::noticeType( '' ) ][ ] = (int) $notice;
				} else if ( strpos( $notice, ':' ) !== false ) {
					$parts = explode( ':', $notice );
					if ( ! is_numeric( $parts[ 0 ] ) || ! isset( $parts[ 1 ] ) ) {
						continue;
					}
					$notices[ self::noticeType( $parts[ 1 ] ) ][ ] = (int) $parts[ 0 ];
				}

			}
		}

		return $notices;
	}

	public function handleRequestNotices() {
		$isAdmin = is_admin();
		foreach ( self::_getNotices() as $noticeType => $items ) {
			foreach ( $items as $msg ) {
				if ( $isAdmin ) {
					Pro_VIP_Admin::addNotice( self::$notices[ $msg ], $noticeType );
				} else {
					pvAddNotice( self::$notices[ $msg ], $noticeType );
				}
			}
		}
	}

	public function initializeDefaultNotices() {

		self::$notices =
			self::$notices +
			array(
				1 => __( 'An error happened. Please tray again.', 'provip' ),
				2 => __( 'An error happened.', 'provip' ),
				3 => __( 'Empty email address.', 'provip' ),
				4 => __( 'Invalid email address.', 'provip' ),
				5 => __( 'Empty name.', 'provip' ),
				6 => __( 'Invalid name.', 'provip' ),
			);

	}


}
