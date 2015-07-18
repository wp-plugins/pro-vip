<?php
/**
 * @class          PV_Custom_Payment
 * @version        1.0
 * @package        Pro-VIP
 * @category       Class
 * @author         Pro-WP
 */

defined( 'ABSPATH' ) or die; // Prevents direct access

class PV_Single_File_Purchase {

	protected $_tmp = array();

	public static function instance() {
		static $instance;
		if ( ! is_object( $instance ) ) {
			$instance = new self;
		}

		return $instance;
	}

	protected function __construct() {
		add_action( 'wp', array( $this, 'actions' ) );
		add_action( 'pro_vip_payment_complete-single-file-purchase', array( $this, 'afterPayment' ) );
		add_action( 'provip_before_file_download', array( $this, 'handleGuestPurchaseDownload' ), 10, 2 );
	}

	public function actions() {
		$this->beforePayment();
	}

	public function beforePayment() {

		if (
			empty( $_REQUEST[ 'pv-action' ] )
			|| $_REQUEST[ 'pv-action' ] != 'single-purchase'
			|| empty( $_REQUEST[ 'fileId' ] )
			|| ! is_numeric( $_REQUEST[ 'fileId' ] )
			|| empty( $_REQUEST[ 'file-index' ] )
			|| ! is_numeric( $_REQUEST[ 'file-index' ] )
		) {
			return false;
		}


		$errors = array();

		if ( empty( $_POST[ 'pv-first-name' ] ) || ! is_string( $_POST[ 'pv-first-name' ] ) ) {
			$errors[ ] = 6;
		}

		if ( empty( $_POST[ 'pv-email-address' ] ) || ! is_string( $_POST[ 'pv-email-address' ] ) ) {
			$errors[ ] = 4;
		}

		$errors = apply_filters( 'pro_vip_single_file_purchase_errors', $errors, $_REQUEST[ 'fileId' ], $_REQUEST[ 'file-index' ] );

		if ( ! empty( $errors ) ) {

			wp_redirect(
				add_query_arg( array( 'wv-notice' => implode( ',', $errors ) ), get_permalink( $_REQUEST[ 'fileId' ] ) )
			);
			die;
		}

		try {
			$file = Pro_VIP_File::find( $_REQUEST[ 'fileId' ], $_REQUEST[ 'file-index' ] );
		} catch ( Exception $e ) {
			wp_die( $e->getMessage() );
			die;
		}

		if ( ! $file->singlePurchaseEnabled ) {
			wp_die( __( 'Single sale not enabled.', 'provip' ) );
			die;
		}

		do_action( 'pro_vip_single_file_purchase_before_payment', $_REQUEST[ 'fileId' ], $_REQUEST[ 'file-index' ] );


		$payment        = new Pro_VIP_Payment();
		$payment->price = $file::getFilePrice();
		$payment->type  = 'single-file-purchase';
		if ( ! empty( $_POST[ 'pv-gateway' ] ) ) {
			$payment->gateway = $_POST[ 'pv-gateway' ];
		}

		$payment->custom[ 'purchase-data' ] = array(
			'file_id'    => $file->ID,
			'file_index' => $file::fileIndex(),
			'user_id'    => get_current_user_id()
		);
		if ( ! is_user_logged_in() ) {
			$payment->custom[ '_guest_purchase' ] = true;
		}

		$payment->custom[ 'user-email-address' ] = $_POST[ 'pv-email-address' ];
		$payment->custom[ 'first-name' ]         = $_POST[ 'pv-first-name' ];
		if ( ! empty( $_POST[ 'pv-last-name' ] ) && is_string( $_POST[ 'pv-last-name' ] ) ) {
			$payment->custom[ 'last-name' ] = $_POST[ 'pv-last-name' ];
		}

		do_action( 'pro_vip_single_file_purchase', $_REQUEST[ 'fileId' ], $_REQUEST[ 'file-index' ], $payment );

		if ( 0 >= $payment->price ) {
			$payment->status = 'publish';
			$payment->save();
			$payment->getGateway()->paymentComplete( $payment );

			return true;
		}


		$payment->proceed();


		die;

	}


	public function afterPayment( Pro_VIP_Payment $payment ) {

		pvUpdateTotalSells( $payment->price );

		if ( empty( $payment->custom[ 'last-name' ] ) ) {
			$payment->custom[ 'last-name' ] = '';
		}


		$guestPurchase = isset( $payment->custom[ '_guest_purchase' ] ) && $payment->custom[ '_guest_purchase' ];

		$file = Pro_VIP_File::find( $payment->custom[ 'purchase-data' ][ 'file_id' ], $payment->custom[ 'purchase-data' ][ 'file_index' ] );

		if ( ! $file->getFile() ) {
			pvAddNotice( __( 'File Not Found!', 'provip' ), 'provip' );

			return false;
		}

		$purchase = $file::registerFilePurchase( $payment->custom[ 'user-email-address' ] );


		if ( ! $purchase ) {
			pvAddNotice( __( 'There was a problem while registering user purchase. Please contact to site administrator.', 'provip' ) );

			return false;
		}

		$dlLink = $file::downloadUrl();

		if ( $guestPurchase ) {
			$uid = md5( uniqid( 'pv_', true ) );
			set_transient(
				'pvgfp' . $uid,
				sprintf( '%d,%d', $file->ID, $file->fileIndex() ),
				DAY_IN_SECONDS * absint( pvGetOption( 'single_file_guest_purchase_download_time', 48 ) )
			);
			$dlLink                 = add_query_arg(
				array(
					'token' => $uid
				),
				$dlLink
			);
			$this->_tmp[ 'dlLink' ] = $dlLink;
			add_action( 'pro_vip_payment_receipt_after', array( $this, 'addDownloadLinkToPaymentReceipt' ) );
		}

		$templateTags = array(
			'first-name'      => $payment->custom[ 'first-name' ],
			'last-name'       => $payment->custom[ 'last-name' ],
			'name'            => $payment->custom[ 'first-name' ] . ( ! empty( $payment->custom[ 'last-name' ] ) ? ( ' ' . $payment->custom[ 'last-name' ] ) : '' ),
			'file-name'       => $file::getFileDlName(),
			'payment-amount'  => Pro_VIP_Currency::priceHTML( $payment->price ),
			'payment-gateway' => $payment->getGateway()->frontendLabel,
			'payment-date'    => date_i18n( get_option( 'date_format' ), strtotime( $payment->date ) ),
			'download-link'   => $dlLink,
		);

		do_action( 'pro_vip_file_purchase_complete', $payment, $templateTags );

		Pro_VIP_Email::send(
			$payment->custom[ 'user-email-address' ],
			pvGetOption( 'email_purchase_file_subject', __( 'File Purchase', 'provip' ) ),
			Pro_VIP_Email::template(
				pvGetOption( 'email_file_purchase_receipt', Pro_VIP_Email::getMail( 'file-purchase' ) ),
				$templateTags
			)
		);


	}

	public function handleGuestPurchaseDownload( Pro_VIP_File $file, Pro_VIP_Downloader $handler ) {

		if ( empty( $_REQUEST[ 'token' ] ) || ! is_string( $_REQUEST[ 'token' ] ) ) {
			return false;
		}

		$token = $_REQUEST[ 'token' ];

		$trans = get_transient( 'pvgfp' . $token );

		if ( ! $trans ) {
			return false;
		}

		list( $fileId, $index ) = explode( ',', $trans );

		if ( $fileId == $file->ID && $index == $file::fileIndex() ) {
			$handler->download();
			die;
		}

	}


	public function addDownloadLinkToPaymentReceipt() {
		?>
		<tr>
			<td colspan="2"><h3 style="text-align: center;margin: 0;">
					<a href="<?= $this->_tmp[ 'dlLink' ] ?>"><?= __( 'Download File', 'provip' ) ?></a></h3></td>
		</tr>
	<?php
	}

}
