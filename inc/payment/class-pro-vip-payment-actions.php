<?php
/**
 * @class          Pro_VIP_Payment_Actions
 * @version        1.0
 * @package        Pro-VIP
 * @category       Class
 * @author         Pro-WP Team
 */

defined( 'ABSPATH' ) or die; // Prevents direct access

/**
 * Class Pro_VIP_Payment_Actions
 */
class Pro_VIP_Payment_Actions {

	public static function instance() {
		static $instance;
		if ( ! is_object( $instance ) ) {
			$instance = new self;
		}

		return $instance;
	}

	protected function __construct() {
		add_action( 'pro_vip_payment_complete-plan-purchase', array( $this, 'planPurchaseAfterPayment' ) );
	}

	public function planPurchaseAfterPayment( Pro_VIP_Payment $payment ) {

		pvUpdateTotalSells( $payment->price );

		if ( empty( $payment->custom[ 'last-name' ] ) ) {
			$payment->custom[ 'last-name' ] = '';
		}

		$planDays = absint( $payment->custom[ 'plan-data' ][ 'plan' ][ 'days' ] );
		$level    = $payment->custom[ 'plan-data' ][ 'level' ][ 'id' ];

		do_action( 'pro_vip_plan_purchase', $payment );

		Pro_VIP_Member::increaseVip(
			$planDays * DAY_IN_SECONDS,
			$payment->user,
			$level
		);

		$expireDate = Pro_VIP_Member::getExpirationDate( $payment->user, $payment->custom[ 'plan-data' ][ 'level' ][ 'id' ] );

		$tags = array(
			'first-name'        => $payment->custom[ 'first-name' ],
			'last-name'         => $payment->custom[ 'last-name' ],
			'name'              => $payment->custom[ 'first-name' ] . ( ! empty( $payment->custom[ 'last-name' ] ) ? ( ' ' . $payment->custom[ 'last-name' ] ) : '' ),
			'plan-name'         => $payment->custom[ 'plan-data' ][ 'plan' ][ 'name' ],
			'plan-days'         => $payment->custom[ 'plan-data' ][ 'plan' ][ 'days' ],
			'level-name'        => $payment->custom[ 'plan-data' ][ 'level' ][ 'name' ],
			'payment-amount'    => Pro_VIP_Currency::priceHTML( $payment->price ),
			'payment-gateway'   => $payment->getGateway()->frontendLabel,
			'payment-date'      => date_i18n( get_option( 'date_format' ), strtotime( $payment->date ) ),
			'plan-price'        => Pro_VIP_Currency::priceHTML( $payment->custom[ 'plan-data' ][ 'plan' ][ 'cost' ][ $payment->custom[ 'plan-data' ][ 'level' ][ 'id' ] ] ),
			'expire-date'       => $expireDate,
			'expire-human-time' => human_time_diff( time(), strtotime( $expireDate ) )
		);

		do_action( 'pro_vip_plan_purchase_complete', $payment, $tags );


		Pro_VIP_Email::send(
			$payment->custom[ 'user-email-address' ],
			pvGetOption( 'email_purchase_account_subject', __( 'Account Purchase', 'provip' ) ),
			Pro_VIP_Email::template(
				apply_filters( 'pro_vip_purchase_account_email_receipt_content', pvGetOption( 'email_purchase_account_receipt', Pro_VIP_Email::getMail( 'account-purchase' ) ) ),
				$tags
			)
		);


	}


}
