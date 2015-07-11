<?php
/**
 * @class          Pro_VIP_Payment_Gateway
 * @version        1.0
 * @package        Pro-VIP
 * @category       Class
 * @author         Pro-WP Team
 */

defined( 'ABSPATH' ) or die; // Prevents direct access

class Pro_VIP_Payment_Gateway {


	public
		$id,
		$settings = array(),
		$frontendLabel,
		$adminLabel;


	/**
	 * @var Pro_VIP_Payment
	 */
	public static $payment;

	private
		$_returnUrl,
		$_validatedGateway = false;

	public static function init() {
		static $inited;
		if ( $inited ) {
			return false;
		}

		$inited = true;

		require dirname( __FILE__ ) . '/payment-functions.php';
	}

	public static function getEmptyObject() {
		static $instance;
		if ( ! is_object( $instance ) ) {
			$instance                = new self;
			$instance->id            = 'default';
			$instance->frontendLabel = '-';
			$instance->adminLabel    = '-';
		}

		return $instance;
	}

	public function __construct() {
		$this->_returnUrl        = add_query_arg(
			array(
				'action'  => 'wvPaymentReturn',
				'gateway' => $this->id
			),
			trailingslashit( site_url() )
		);
		$this->_validatedGateway = true;
		$this->settings          = get_option( 'wv_' . $this->id . '_settings', array() );
	}

	public function getReturnUrl() {
		return $this->_returnUrl;
	}

	public function isValidated() {
		return $this->_validatedGateway;
	}

	public function beforePayment( Pro_VIP_Payment $payment ) {
		wp_die( __( 'No Payment Gateway.', 'provip' ) );
	}

	public function afterPayment() {
	}

	public function adminSettings( PV_Framework_Form_Builder $form ) {

	}

	public function paymentComplete( Pro_VIP_Payment $payment ) {

		Pro_VIP::set( 'payment', $payment );

		do_action( 'pro_vip_payment_complete', $payment );
		if ( ! empty( $payment->type ) ) {
			do_action( 'pro_vip_payment_complete-' . $payment->type, $payment );
		}

		$page = get_post( pvGetOption( 'success_page', 0 ) );
		if ( empty( $page ) || $page->post_status == 'trash' ) {
			$content = do_shortcode( '[pv-receipt]' );
			wp_die( $content, __( 'Payment', 'provip' ) );
			die;
		}

		global $wp_query;

		$wp_query->queried_object = $page;
		$wp_query->post           = $page;
		$wp_query->found_posts    = 1;
		$wp_query->post_count     = 1;
		$wp_query->max_num_pages  = 1;
		$wp_query->is_single      = 1;
		$wp_query->is_404         = false;
		$wp_query->is_posts_page  = 1;
		$wp_query->posts          = array( $page );
		$wp_query->page           = false;
		$wp_query->is_post        = true;
		$wp_query->is_home        = false;
		$wp_query->page           = false;

	}


	public function paymentFailed( $payment = null ) {

		/**
		 * @var $payment Pro_VIP_Payment
		 */

		Pro_VIP::set( 'payment', $payment );

		do_action( 'pro_vip_payment_failed', $payment );
		if ( ! empty( $payment ) && ! empty( $payment->type ) ) {
			do_action( 'pro_vip_payment_failed-' . $payment->type, $payment );
		}

		$page = get_post( pvGetOption( 'failed_page', 0 ) );
		if ( empty( $page ) || $page->post_status == 'trash' ) {
			$content = __( 'Your transaction failed, please try again or contact site support.', 'provip' );
			wp_die( $content, __( 'Payment', 'provip' ) );
			die;
		}

		global $wp_query;

		$wp_query->queried_object = $page;
		$wp_query->post           = $page;
		$wp_query->found_posts    = 1;
		$wp_query->post_count     = 1;
		$wp_query->max_num_pages  = 1;
		$wp_query->is_single      = 1;
		$wp_query->is_404         = false;
		$wp_query->is_posts_page  = 1;
		$wp_query->posts          = array( $page );
		$wp_query->page           = false;
		$wp_query->is_post        = true;
		$wp_query->is_home        = false;
		$wp_query->page           = false;

	}

	public function redirect( $url, $parameters = array(), $method = 'post' ) {
		Pro_VIP_Template::load(
			'payment/payment-redirect',
			'',
			array(
				'url'        => $url,
				'parameters' => $parameters,
				'method'     => ( in_array( strtolower( $method ), array(
					'post',
					'get'
				) ) ? strtolower( $method ) : 'post' )
			)
		);
	}

	/**
	 * @param $id
	 *
	 * @return bool|Pro_VIP_Payment_Gateway
	 *
	 */
	public static function getGateway( $id = null, $returnEmptyObject = true ) {
		$allGateways   = Pro_VIP::get( $id, 'gateways' );
		$panelGateways = pvGetOption( 'gateways', array() );


		// Return default gateway
		if ( is_null( $id ) ) {
			$defaultGateway = ! empty( $panelGateways[ 'default-gateway' ] ) ? $panelGateways[ 'default-gateway' ] : false;
			if ( ! $defaultGateway || ! ( $gateway = self::getGateway( $defaultGateway ) ) ) {
				return $returnEmptyObject ? self::getEmptyObject() : false;
			}

			return $gateway;
		}

		if ( ! is_array( $allGateways ) ) {
			return $returnEmptyObject ? self::getEmptyObject() : false;
		}

		return $allGateways[ 'object' ];
	}

	/**
	 * @param string $class
	 *
	 * @return bool
	 */
	public static function registerGateway( $class ) {
		/**
		 * @var $object $this
		 */
		$object = new $class;
		Pro_VIP::set(
			$object->id,
			array(
				'id'     => $object->id,
				'object' => $object
			),
			'gateways'
		);

		return true;
	}

	public static function getAllGateways() {
		$g        = Pro_VIP::get( null, 'gateways', array() );
		$gateways = array();
		foreach ( $g as $gateway ) {
			$gateways[ ] = $gateway[ 'object' ];
		}

		return $gateways;
	}

	public static function getAllGatewaysList() {
		$gw = array();
		foreach ( Pro_VIP_Payment_Gateway::getAllGateways() as $gateway ) {
			$gw[ $gateway->id ] = is_admin() ? $gateway->adminLabel : $gateway->frontendLabel;
		}

		return $gw;
	}

	public static function getGatewaysList() {
		$val  = pvGetOption( 'gateways', array() );
		$list = array();
		if ( ! empty( $val[ 'order' ] ) ) {
			$gw = array_keys( $val[ 'order' ] );
		} else {
			$gw = array();
			foreach ( Pro_VIP_Payment_Gateway::getAllGateways() as $gateway ) {
				$gw[ ] = $gateway->id;
			}
			unset( $gateway );
		}
		foreach ( $gw as $gatewayId ):
			$gateway = Pro_VIP_Payment_Gateway::getGateway( $gatewayId );
			if ( empty( $val[ $gatewayId ] ) || $val[ $gatewayId ][ 'enabled' ] != 1 ) {
				continue;
			}
			$list[ $gatewayId ] = $gateway->frontendLabel;
		endforeach;

		return $list;
	}

	public static function gatewaysListDropdown() {
		$html = '<select name="pv-gateway" class="pv-gateway" id="pv-gateway">';
		/**
		 * @var $gateway $this
		 */
		foreach ( self::getGatewaysList() as $gatewayId => $name ) {
			$gateway = self::getGateway( $gatewayId );
			$html .= '<option value="' . $gateway->id . '" ' . selected( self::getDefaultGatewayId(), $gatewayId, false ) . '>' . ( is_admin() ? $gateway->adminLabel : $gateway->frontendLabel ) . '</option>';
		}
		$html .= '</select>';

		return $html;
	}

	public static function isGatewayEnabled( $id ) {
		if ( ! $gateway = self::getGateway( $id ) ) {
			return false;
		}
		$val = pvGetOption( 'gateways', array() );

		return ! empty( $val[ $id ] ) && ! empty( $val[ $id ][ 'enabled' ] ) && $val[ $id ][ 'enabled' ] == 1;
	}

	public static function getDefaultGatewayId() {
		$gateways = pvGetOption( 'gateways', array() );

		return ! empty( $gateways[ 'default-gateway' ] ) ? $gateways[ 'default-gateway' ] : '';
	}

}
