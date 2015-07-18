<?php
/**
 * @class          Pro_VIP_Actions
 * @version        1.0
 * @package        Pro-VIP
 * @category       Class
 * @author         Pro-WP Team
 */

defined( 'ABSPATH' ) or die; // Prevents direct access

class Pro_VIP_Actions {

	public static function instance() {
		static $instance;
		if ( ! is_object( $instance ) ) {
			$instance = new self;
		}

		return $instance;
	}

	protected function __construct() {
		add_action( 'wp', array( $this, 'proceed' ) );

	}

	public function proceed() {
		$this->actionFileDownload();
		$this->purchasePlanBeforePayment();
		$this->paymentReturn();
		$this->paymentBefore();
	}

	protected function actionFileDownload() {
		if ( is_single() ) {
			global $post;
			if ( $post->post_type == 'vip-files' && isset( $_REQUEST[ 'do-download' ] ) && isset( $_GET[ 'index' ] ) && is_numeric( $_GET[ 'index' ] ) ) {
				$id = get_the_ID();

				try {
					$file = Pro_VIP_File::find( $id, $_GET[ 'index' ] );
				} catch ( Exception $e ) {
					wp_die( $e->getMessage() );
					die;
				}

				$handler = new Pro_VIP_Downloader();
				$handler->filePath( $file::fullPath() );
				$handler->dlFileName = $file::getFileDlName();

				do_action( 'provip_before_file_download', $file, $handler );

				if ( ! is_user_logged_in() ) {

					$username = null;
					$password = null;
					if ( isset( $_SERVER[ 'PHP_AUTH_USER' ] ) ) {
						$username = $_SERVER[ 'PHP_AUTH_USER' ];
						$password = $_SERVER[ 'PHP_AUTH_PW' ];
					} elseif ( isset( $_SERVER[ 'HTTP_AUTHORIZATION' ] ) ) {

						if ( strpos( strtolower( $_SERVER[ 'HTTP_AUTHORIZATION' ] ), 'basic' ) === 0 ) {
							list( $username, $password ) = explode( ':', base64_decode( substr( $_SERVER[ 'HTTP_AUTHORIZATION' ], 6 ) ) );
						}

					}

					if ( is_null( $username ) ) {
						header( 'WWW-Authenticate: Basic realm="My Realm"' );
						header( 'HTTP/1.0 401 Unauthorized' );
						echo __( 'Login Failed.', 'provip' );
						die;
					} else {
						$creds                    = array();
						$creds[ 'user_login' ]    = $username;
						$creds[ 'user_password' ] = $password;
						$creds[ 'remember' ]      = true;
						$user                     = wp_signon( $creds, false );
						if ( is_wp_error( $user ) ) {
							header( 'WWW-Authenticate: Basic realm="My Realm"' );
							header( 'HTTP/1.0 401 Unauthorized' );
							echo __( 'Login Failed.', 'provip' );
							die;
						}
					}
					die;
				}

				if ( ! $file::canUserDownloadFile() ) {
					wp_die( sprintf( __( 'Oops. You cannot download this file. <a href="%s">Return to site</a>', 'provip' ), site_url() ) );
					die;
				}


				$file::increaseDownloadsCount();
				do_action( 'pro_vip_file_downloaded', $file, $handler );

				$handler->download();

				die;
			}
		}
	}

	protected function purchasePlanBeforePayment() {

		if ( apply_filters( 'pro_vip_account_purchase_login_required', true ) && ! is_user_logged_in() ) {
			return false;
		}

		if ( ! empty( $_POST[ 'action' ] ) && $_POST[ 'action' ] === 'pvPurchasePlan' ) {

			$errors = array();

			if ( empty( $_POST[ 'pv-plan' ] ) || ! is_numeric( $_POST[ 'pv-plan' ] ) || empty( $_POST[ 'pv-plan-level' ] ) || ! is_numeric( $_POST[ 'pv-plan-level' ] ) ) {
				$errors[ __( 'Invalid Plan or plan level.', 'provip' ) ] = 'error';
			}

			if ( empty( $_POST[ 'pv-email-address' ] ) || ! is_string( $_POST[ 'pv-email-address' ] ) || ! is_email( $_POST[ 'pv-email-address' ] ) ) {
				$errors[ __( 'Invalid email address.', 'provip' ) ] = 'error';
			}

			if ( empty( $_POST[ 'pv-first-name' ] ) || ! is_string( $_POST[ 'pv-first-name' ] ) ) {
				$errors[ __( 'Empty first name.', 'provip' ) ] = 'error';
			}

			$errors = apply_filters( 'pro_vip_plan_purchase_form_errors', $errors );

			if ( ! empty( $errors ) ) {
				foreach ( $errors as $msg => $errorType ) {
					pvAddNotice( $msg, $errorType );
				}

				return false;
			}

			$plan  = pvGetPlan( $_POST[ 'pv-plan' ] );
			$level = pvGetLevel( $_POST[ 'pv-plan-level' ] );

			$cost = (int) $plan[ 'cost' ][ $_POST[ 'pv-plan-level' ] ];


			$payment        = new Pro_VIP_Payment();
			$payment->price = $cost;
			$payment->user  = get_current_user_id();
			$payment->type  = 'plan-purchase';
			if ( ! empty( $_POST[ 'pv-gateway' ] ) ) {
				$payment->gateway = $_POST[ 'pv-gateway' ];
			}

			$payment->custom[ 'plan-data' ]          = array(
				'plan'  => $plan,
				'level' => $level
			);
			$payment->custom[ 'user-email-address' ] = $_POST[ 'pv-email-address' ];
			$payment->custom[ 'first-name' ]         = $_POST[ 'pv-first-name' ];
			if ( ! empty( $_POST[ 'pv-last-name' ] ) && is_string( $_POST[ 'pv-last-name' ] ) ) {
				$payment->custom[ 'last-name' ] = $_POST[ 'pv-last-name' ];
			}

			do_action( 'pro_vip_plan_purchase_before', $plan, $level, $payment );


			if ( 0 >= $payment->price ) {
				$payment->status = 'publish';
				$payment->save();
				$payment->getGateway()->paymentComplete( $payment );

				return true;
			}


			$payment->proceed();

		}
	}

	public function paymentReturn() {
		if ( ! empty( $_GET[ 'action' ] ) && $_GET[ 'action' ] == 'wvPaymentReturn' && ! empty( $_GET[ 'gateway' ] ) && $gateway = wvGetGateway( $_GET[ 'gateway' ] ) ) {
			$gateway->afterPayment();
		}
	}


	public function paymentBefore() {
		if (
			empty( $_REQUEST[ 'pv-action' ] )
			|| $_REQUEST[ 'pv-action' ] !== 'do-payment'
			|| empty( $_REQUEST[ 'pv-payment-type' ] )
			|| ! is_string( $_REQUEST[ 'pv-payment-type' ] )
			|| ! isset( $_REQUEST[ 'pv-amount' ] )
		) {
			return false;
		}

		do_action( 'provip_before_payment' );
		do_action( 'provip_before_payment-' . $_REQUEST[ 'pv-payment-type' ] );

		$errors = array();
		if ( empty( $_REQUEST[ 'pv-email-address' ] ) || ! is_string( $_REQUEST[ 'pv-email-address' ] ) || ! is_email( $_REQUEST[ 'pv-email-address' ] ) ) {
			$errors[ __( 'Invalid email address.', 'provip' ) ] = 'error';
		}

		if ( empty( $_REQUEST[ 'pv-first-name' ] ) || ! is_string( $_REQUEST[ 'pv-first-name' ] ) ) {
			$errors[ __( 'Empty first name.', 'provip' ) ] = 'error';
		}

		if ( ! empty( $errors ) ) {
			foreach ( $errors as $error => $type ) {
				pvAddNotice( $error, $type );
			}

			return false;
		}

		$payment = new Pro_VIP_Payment();

		$payment->custom[ 'first-name' ]         = $_REQUEST[ 'pv-first-name' ];
		$payment->custom[ 'user-email-address' ] = $_REQUEST[ 'pv-email-address' ];
		if ( ! empty( $_REQUEST[ 'pv-last-name' ] ) && is_string( $_REQUEST[ 'pv-last-name' ] ) ) {
			$payment->custom[ 'last-name' ] = $_REQUEST[ 'pv-last-name' ];
		}

		do_action( 'provip_before_payment_validated', $payment );
		do_action( 'provip_before_payment_validated-' . $_REQUEST[ 'pv-payment-type' ], $payment );

	}
}
