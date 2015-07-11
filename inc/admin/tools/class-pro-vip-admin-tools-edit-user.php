<?php
/**
 * @class          Pro_VIP_Admin_Tools_Edit_User
 * @version        1.0
 * @package        Pro VIP
 * @category       Class
 * @author         Pro-WP
 */

defined( 'ABSPATH' ) or die; // Prevents direct access

class Pro_VIP_Admin_Tools_Edit_User {


	public static function instance() {
		static $instance;
		if ( ! is_object( $instance ) ) {
			$instance = new self;
		}

		return $instance;
	}

	public static function getEditLink( $userId ) {
		return add_query_arg(
			array(
				'tool' => md5( maybe_serialize( array( self::instance(), 'callback' ) ) ),
				'page' => 'provip_tools',
				'user' => $userId
			),
			admin_url( Pro_VIP_Admin::$menuSlug )
		);
	}

	protected function __construct() {
		Pro_VIP_Admin_Tools::add(
			__( 'Edit User', 'provip' ),
			array( $this, 'callback' )
		);
		add_action( 'admin_init', array( $this, 'handleEdit' ) );
	}

	public function callback() {


		$user = null;
		if ( ! empty( $_REQUEST[ 'user' ] ) && is_numeric( $_REQUEST[ 'user' ] ) ) {
			$user = get_userdata( $_REQUEST[ 'user' ] );
		}

		if ( ! empty( $user ) ) {
			Pro_VIP::loadView(
				'admin/tools/edit-user/edit',
				array(
					'user'     => $user,
					'accounts' => Pro_VIP_Member::getVipAccounts( $user )
				)
			);
		} else {
			if( isset( $_REQUEST['user'] ) ){
				echo '<p>'.__( 'User not found', 'provip' ).'</p>';
			}
			Pro_VIP::loadView(
				'admin/tools/edit-user/index',
				array()
			);
		}
	}

	public function handleEdit() {
		if (
			! isset( $_POST[ 'wv-action' ] ) || $_POST[ 'wv-action' ] !== 'admin.editUserVip'
			|| empty( $_POST[ '_wpnonce' ] ) || ! wp_verify_nonce( $_POST[ '_wpnonce' ], 'pro_vip_user_edit' )
		) {
			return false;
		}

		$errors = array();
		if ( empty( $_POST[ 'vip-time' ] ) || ! is_numeric( $_POST[ 'vip-time' ] ) ) {
			$errors[ ] = __( 'Enter a valid vip time.', 'provip' );
		}
		if ( empty( $_POST[ 'vip-level' ] ) || ! is_numeric( $_POST[ 'vip-level' ] ) || ! $level = pvGetLevel( $_POST[ 'vip-level' ] ) ) {
			$errors[ ] = __( 'Selected level does not exists.', 'provip' );
		}
		if ( empty( $_POST[ 'user-id' ] ) || ! is_numeric( $_POST[ 'user-id' ] ) || ! $user = get_userdata( $_POST[ 'user-id' ] ) ) {
			$errors[ ] = __( 'User not found.', 'provip' );
		}

		if ( ! empty( $errors ) ) {
			foreach ( $errors as $error ) {
				Pro_VIP_Admin::addNotice( $error, 'error' );
			}

			return false;
		}

		$updateSeconds = absint( $_POST[ 'vip-time' ] );
		$m             = 1;
		switch ( empty( $_POST[ 'time-type' ] ) ? '' : $_POST[ 'time-type' ] ) {
			case 'min':
				$m = MINUTE_IN_SECONDS;
				break;
			case 'hour':
				$m = HOUR_IN_SECONDS;
				break;
			case 'day':
				$m = DAY_IN_SECONDS;
				break;
			case 'week':
				$m = WEEK_IN_SECONDS;
				break;
			case 'year':
				$m = YEAR_IN_SECONDS;
				break;
		}
		$updateSeconds *= $m;
		unset( $m );

		if ( isset( $_POST[ 'update-action' ] ) && $_POST[ 'update-action' ] == 'decrease' ) {
			$updateSeconds = - $updateSeconds;
		}

		$edit = Pro_VIP_Member::increaseVip( $updateSeconds, $user->ID, $level[ 'id' ] );


		Pro_VIP_Admin::addNotice(
			$edit ? __( 'User updated.', 'provip' ) : __( 'An error happened. Please try again', 'provip' ),
			$edit ? 'succeed' : 'error'
		);
	}

}
