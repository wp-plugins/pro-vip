<?php
/**
 * @class          PV_Custom_Payment
 * @version        1.0
 * @package        Pro-VIP
 * @category       Class
 * @author         Pro-WP
 */

defined( 'ABSPATH' ) or die; // Prevents direct access

class PV_Custom_Payment {


	public static function instance() {
		static $instance;
		if ( ! is_object( $instance ) ) {
			$instance = new self;
		}

		return $instance;
	}

	protected function __construct() {
		add_action( 'provip_before_payment_validated-custom-payment', array( $this, 'beforePayment' ) );
		add_action( 'pro_vip_payment_complete-custom-payment', array( $this, 'afterPayment' ) );
		add_shortcode( 'pv-payment-form', array( $this, 'paymentForm' ) );
	}

	public function beforePayment( Pro_VIP_Payment $payment ) {

		if ( apply_filters( 'provip_custom_payment_login_required', true ) == true && ! is_user_logged_in() ) {
			pvAddNotice( __( 'You need to login first.', 'provip' ) );

			return false;
		}

		$errors = array();

		if ( empty( $_REQUEST[ 'pv-amount' ] ) || ! is_numeric( $_REQUEST[ 'pv-amount' ] ) || 0 >= absint( $_REQUEST[ 'pv-amount' ] ) ) {
			$errors[ __( 'Enter a valid payment amount.', 'provip' ) ] = 'error';
		}

		$errors = apply_filters( 'provip_custom_payment_form_errors', $errors );

		if ( ! empty( $errors ) ) {
			foreach ( $errors as $error => $type ) {
				pvAddNotice( $error, $type );
			}

			return false;
		}

		$payment->price = absint( $_REQUEST[ 'pv-amount' ] );

		$payment->user = get_current_user_id();

		do_action( 'provip_custom_payment_before_payment', $payment );


		$payment->proceed();

	}


	public function afterPayment( Pro_VIP_Payment $payment ) {

		$tags = array(
			'first-name'      => $payment->custom[ 'first-name' ],
			'last-name'       => ! empty( $payment->custom[ 'last-name' ] ) && is_string( $payment->custom[ 'last-name' ] ) ? $payment->custom[ 'last-name' ] : '',
			'name'            => $payment->custom[ 'first-name' ] . ( ! empty( $payment->custom[ 'last-name' ] ) ? ( ' ' . $payment->custom[ 'last-name' ] ) : '' ),
			'payment-amount'  => Pro_VIP_Currency::priceHTML( $payment->price ),
			'payment-gateway' => $payment->getGateway()->frontendLabel,
			'payment-date'    => date_i18n( get_option( 'date_format' ), strtotime( $payment->date ) ),
		);

		do_action( 'provip_custom_payment_complete', $payment, $tags );


		Pro_VIP_Email::send(
			$payment->custom[ 'user-email-address' ],
			pvGetOption( 'email_custom_payment_subject', __( 'Successful Payment', 'provip' ) ),
			Pro_VIP_Email::template(
				apply_filters( 'provip_custom_payment_complete_email_content', pvGetOption( 'email_custom_payment_receipt', Pro_VIP_Email::getMail( 'custom-payment' ) ) ),
				$tags
			)
		);

	}


	public function paymentForm() {

		ob_start();

		if ( ! is_user_logged_in() ) {
			return pvLoginMsg();
		}

		$args = apply_filters(
			'provip_payment_shortcode_args',
			array(
				'currentUser' => wp_get_current_user()
			)
		);

		Pro_VIP_Template::load( 'shortcodes/payment-form', '', $args );

		return ob_get_clean();

	}

}
