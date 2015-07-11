<?php
/**
 * @class          Pro_VIP_Admin_Tools_VIP_Bulk_Edit
 * @version        1.0
 * @package        Pro-VIP
 * @category       Class
 * @author         Pro-WP
 */

defined( 'ABSPATH' ) or die; // Prevents direct access

class Pro_VIP_Admin_Tools_VIP_Bulk_Edit {

	public static $errors = array();

	public static function instance() {
		static $instance;
		if ( ! is_object( $instance ) ) {
			$instance = new self;
		}

		return $instance;
	}


	protected function __construct() {
		Pro_VIP_Admin_Tools::add(
			__( 'VIP Bulk Edit', 'provip' ),
			array( $this, 'callback' )
		);
		add_action( 'admin_init', array( $this, 'doEditAction' ) );
	}

	public function callback() {
		Pro_VIP::loadView( 'admin/tools/bulk-vip-edit/index' );
	}

	public function doEditAction() {

		if ( ! isset( $_POST[ 'action' ] ) || $_POST[ 'action' ] !== 'provip_bulk_vip_edit' || ! isset( $_POST[ 'nonce' ] ) || ! wp_verify_nonce( $_POST[ 'nonce' ], 'provip_bulk_vip_edit' ) || empty( $_POST[ 'be' ] ) || ! is_array( $_POST[ 'be' ] ) ) {
			return false;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return false;
		}

		$settings = $_POST[ 'be' ];
		$hasError = false;

		if ( empty( $settings[ 'action' ] ) || ! in_array( $settings[ 'action' ], array( 'increase', 'decrease' ) ) ) {
			return false;
		}

		if ( empty( $settings[ 'time' ] ) || ! is_numeric( $settings[ 'time' ] ) ) {
			Pro_VIP_Admin::addNotice( __( '"Time" field must be a valid integer.', 'provip' ) );
			$hasError = true;
		}

		if ( empty( $settings[ 'level' ] ) || ! is_array( $settings[ 'level' ] ) ) {
			Pro_VIP_Admin::addNotice( __( 'You must select at least one level to update.', 'provip' ) );
			$hasError = true;
		}

		if ( $hasError ) {
			return false;
		}


		if ( empty( $settings[ 'time-t' ] ) || ! is_string( $settings[ 'time-t' ] ) ) {
			return false;
		}

		$updateSeconds = absint( $settings[ 'time' ] );

		$m = 1;
		switch ( $settings[ 'time-t' ] ) {
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

		global $wpdb;

		$clauses   = array(
			'join'  => array(),
			'set'   => array(),
			'where' => array( '1 = 1' )
		);
		$usersJoin = false;


		$sql                 = 'UPDATE ' . $wpdb->prefix . 'vip_users as vip_users ';
		$clauses[ 'set' ][ ] = $wpdb->prepare( 'expiration_date = ' . ( $settings[ 'action' ] == 'decrease' ? 'DATE_SUB' : 'DATE_ADD' ) . '(expiration_date,INTERVAL %d SECOND)', $updateSeconds );

		$where = 'AND `vip_level` IN (';
		$ids   = array();
		foreach ( $settings[ 'level' ] as $level ) {
			$levelData = pvGetLevel( $level );
			if ( ! $levelData ) {
				continue;
			}
			$ids[ ] = $levelData[ 'id' ];
		}
		if ( empty( $ids ) ) {
			return false;
		}

		$where .= implode( ',', array_filter( $ids, 'is_numeric' ) );
		$where .= ')';


		$clauses[ 'where' ][ ] = $where;
		unset( $where, $ids );


		// Advanced Settings
		if (
			! empty( $settings[ 'register-date-operator' ] )
			&& ( $settings[ 'register-date-operator' ] == 'before' || $settings[ 'register-date-operator' ] == 'after' )
			&& ! empty( $settings[ 'register-date' ] )
			&& $registerDate = DateTime::createFromFormat( 'm/d/Y', $settings[ 'register-date' ] )
		) {
			$clauses[ 'where' ][ ] = 'AND users.user_registered ' . ( $settings[ 'register-date-operator' ] == 'after' ? '<' : '>' ) . " '" . $registerDate->format( 'Y-m-d H:i:s' ) . "'";
			$usersJoin             = true;
		}

		if (
			! empty( $settings[ 'first-purchase-operator' ] )
			&& ( $settings[ 'first-purchase-operator' ] == 'before' || $settings[ 'first-purchase-operator' ] == 'after' )
			&& ! empty( $settings[ 'first-purchase' ] )
			&& $registerDate = DateTime::createFromFormat( 'm/d/Y', $settings[ 'first-purchase' ] )
		) {
			$clauses[ 'where' ][ ] = 'AND vip_users.start_date ' . ( $settings[ 'first-purchase-operator' ] == 'after' ? '<' : '>' ) . " '" . $registerDate->format( 'Y-m-d H:i:s' ) . "'";
			$usersJoin             = true;
		}


		if ( $usersJoin ) {
			$clauses[ 'join' ][ ] = 'INNER JOIN ' . $wpdb->users . ' as users ON vip_users.user_ID = users.ID';
		}

		$clauses = apply_filters( 'pro_vip_bulk_vip_edit_update_query', $clauses );

		$sql .= implode( ' ', array(
			implode( ' ', $clauses[ 'join' ] ),
			'SET ' . implode( ' ', $clauses[ 'set' ] ),
			'WHERE ' . implode( ' ', $clauses[ 'where' ] )
		) );


		$updatedRows = (int) $wpdb->query( $sql );


		Pro_VIP_Admin::addNotice(
			sprintf( _n( '%d user updated.', '%d users updated.', $updatedRows, 'provip' ), $updatedRows ),
			( $updatedRows > 0 ? 'succeed' : 'error' )
		);
	}

}
