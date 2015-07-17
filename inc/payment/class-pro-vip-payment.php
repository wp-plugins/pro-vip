<?php
/**
 * @class          Pro_VIP_Payment
 * @version        1.0
 * @package        Pro-VIP
 * @category       Class
 * @author         Pro-WP Team
 */

defined( 'ABSPATH' ) or die; // Prevents direct access

/**
 * Class Pro_VIP_Payment
 *
 * @var $type , $key, $gateway, $paymentId, $status, $custom, $user, $userIp, $price
 */
class Pro_VIP_Payment {

	public
		$type,
		$key,
		$gateway,
		$paymentId,
		$status = 'pending',
		$custom = array(),
		$user,
		$date,
		$userIp,
		$price;

	protected
		$_wpPost;


	public static $customLabels = array();

	public function __construct( $id = null ) {
		$this->paymentId = $id;
		$this->_setupPaymentData();
		Pro_VIP_Payment_Actions::instance();
	}

	private function _setupPaymentData() {
		if ( ! is_numeric( $this->paymentId ) || ! empty( $this->_wpPost ) ) {
			return false;
		}
		$post = get_post( $this->paymentId );

		if ( empty( $post ) ) {
			throw new Exception( __( 'Payment not found.', 'provip' ) );
		}

		$this->status  = $post->post_status;
		$this->user    = $post->post_author;
		$this->date    = $post->post_date;
		$this->_wpPost = $post;
		foreach ( get_object_vars( $this ) as $var => $value ) {
			if ( strpos( $var, '_' ) === 0 || in_array( $var, array( 'paymentId', 'status', 'user', 'date' ) ) ) {
				continue;
			}
			$this->$var = get_post_meta( $this->paymentId, '_provip_' . $var, true );
		}
	}

	public function save() {
		$paymentId = $this->paymentId;
		if ( ! $paymentId ) {
			$post = wp_insert_post( array(
				'post_title'  => __( 'WP VIP Payment', 'provip' ) . ' - ' . time(),
				'post_status' => $this->status,
				'post_author' => $this->user,
				'post_date'   => current_time( 'mysql' ),
				'post_type'   => 'provip_payment'
			) );
			if ( ! $post ) {
				throw new Exception( "Post not saved." );
			}
			$this->paymentId = $post;
			$paymentId       = $post;
			$this->_wpPost   = get_post( $post );
		} else {
			wp_update_post( array(
				'ID'          => $paymentId,
				'post_status' => $this->status,
				'post_author' => $this->user
			) );

		}

		if ( ! $this->userIp ) {
			$this->userIp = pvGetIP();
		}

		foreach ( get_object_vars( $this ) as $var => $value ) {
			if ( strpos( $var, '_' ) === 0 || in_array( $var, array( 'paymentId', 'status', 'user', 'date' ) ) ) {
				continue;
			}
			update_post_meta( $paymentId, '_provip_' . $var, $value );
		}
	}

	public function getGateway() {
		if ( is_null( $this->gateway ) ) {
			if ( ! empty( $_REQUEST[ 'pv-gateway' ] ) && is_string( $_REQUEST[ 'pv-gateway' ] ) ) {
				$this->gateway = $_REQUEST[ 'pv-gateway' ];
			}
		}

		return Pro_VIP_Payment_Gateway::getGateway( $this->gateway );
	}

	public function proceed() {
		if( !empty( $_REQUEST['pv-payment-type'] ) && is_string( $_REQUEST[ 'pv-payment-type' ] ) ){
			$this->type = $_REQUEST[ 'pv-payment-type' ];
		}
		$this->save();
		$this->getGateway()->beforePayment( $this );
	}


	public static function getPaymentIdFromKey( $key ) {
		global $wpdb;
		$id = $wpdb->get_var( $wpdb->prepare( "SELECT `post_id` FROM $wpdb->postmeta WHERE meta_key = '_provip_key' AND meta_value = %s", $key ) );
		if ( empty( $id ) || ! is_numeric( $id ) ) {
			throw new Exception( __( 'Payment not found.', 'provip' ) );
		}

		return (int) $id;
	}

	public static function status( $s, $translate = true, $def = '' ) {
		$statuses = array(
			'pending'   => array(
				__( 'Pending', 'provip' ),
				'pending'
			),
			'publish'   => array(
				__( 'Complete', 'provip' ),
				'complete'
			),
			'refunded'  => array(
				__( 'Refunded', 'provip' ),
				'refunded'
			),
			'failed'    => array(
				__( 'Failed', 'provip' ),
				'failed'
			),
			'abandoned' => array(
				__( 'Abandoned', 'provip' ),
				'abandoned'
			),
			'trash'     => array(
				__( 'Trash', 'provip' ),
				'trash'
			),
			'revoked'   => array(
				__( 'Revoked', 'provip' ),
				'revoked'
			)
		);

		return array_key_exists( $s, $statuses ) ? ( $translate ? $statuses[ $s ][ 0 ] : $statuses[ $s ][ 1 ] ) : $def;
	}

	public function delete() {
		foreach ( get_object_vars( $this ) as $var => $value ) {
			if ( strpos( $var, '_' ) === 0 || in_array( $var, array( 'paymentId', 'status', 'user', 'date' ) ) ) {
				continue;
			}
			delete_post_meta( $this->paymentId, '_provip_' . $var );
		}

		return wp_delete_post( $this->paymentId, true ) !== false;
	}

	public static function dumpCustom( $customs, $group = false ) {

		foreach ( (array) $customs as $key => $value ) {

			echo '<p>' . self::custom( $key ) . ': ';
			if ( is_array( $value ) || is_object( $value ) ) {

				$d = function_exists( 'is_rtl' ) && is_rtl() ? 'right' : 'left';
				echo '<div style="margin-' . $d . ': 30px;background: rgba(0, 0, 0, 0.03);padding: 10px;">';
				self::dumpCustom( $value, $key );
				echo '</div>';
			} else {
				if ( is_bool( $value ) ) {
					echo $value ? '<img class="emoji" alt="✔" src="http://s.w.org/images/core/emoji/72x72/2714.png">' : '-';
				} else {
					echo $value;
				}
			}
			echo '</p>';


		}

	}

	public static function custom( $custom ) {

		static $defaultCustoms;

		$defaultCustoms = array(
			'first-name'         => __( 'First Name', 'provip' ),
			'level'              => __( 'Level', 'provip' ),
			'id'                 => __( 'ID', 'provip' ),
			'name'               => __( 'Name', 'provip' ),
			'days'               => __( 'Days', 'provip' ),
			'cost'               => __( 'Cost', 'provip' ),
			'plan'               => __( 'Plan', 'provip' ),
			'plan-data'          => __( 'Plan Data', 'provip' ),
			'user-email-address' => __( 'Email Address', 'provip' )
		);

		$customs = array_merge(
			$defaultCustoms,
			self::$customLabels
		);

		return isset( $customs[ $custom ] ) ? $customs[ $custom ] : $custom;
	}

}
