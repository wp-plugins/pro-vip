<?php
/**
 * @class          Pro_VIP_Template
 * @version        1.0
 * @package        Pro-VIP
 * @category       Class
 * @author         Pro-WP Team
 */

defined( 'ABSPATH' ) or die; // Prevents direct access

class Pro_VIP_Template {

	public static $themeFolder = 'pro-vip';

	public $query = array();

	public static function instance() {
		static $instance;
		if ( ! is_object( $instance ) ) {
			$instance = new self;
		}

		return $instance;
	}


	protected function __construct() {
		require dirname( __FILE__ ) . '/template-functions.php';

		if ( is_admin() ) {
			return false;
		}
		add_action( 'template_redirect', array( $this, 'loadTemplates' ) );
		add_action( 'wp_footer', array( $this, 'footerHTML' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueueAssets' ) );
	}

	public static function getPath( $templateName, $slug = '' ) {
		$templateName = preg_replace( '/(\.php)$/', '', $templateName ) . ( ! empty( $slug ) ? '-' . $slug : '' ) . '.php';

		if ( $file = locate_template( $templateName ) ) {
			return $file;
		}

		return PRO_VIP_PATH . 'templates/' . $templateName;
	}

	public static function load( $templateName, $slug = '', $vars = array(), $requireOnce = false ) {
		if ( file_exists( $file = self::getPath( $templateName, $slug ) ) ) {
			extract( $vars );
			if ( $requireOnce ) {
				require_once $file;
			} else {
				require $file;
			}
		}
	}


	public function loadTemplates() {
		$this->downloadsSingle();
		$this->paymentSuccessPage();
		$this->paymentFailedPage();
		add_filter( 'the_content', array( $this, 'filterTheContent' ) );

		$pages = array( 'plans_page', 'success_page', 'failed_page', 'payments_page' );
		foreach ( $pages as $page ) {
			$id = pvGetOption( $page );
			if ( ! is_numeric( $id ) ) {
				continue;
			}
			if ( is_page( $id ) ) {
				Pro_VIP::set( '_is_pro_vip', true );
			}
		}

	}

	public function filterTheContent( $content ) {

		if ( is_main_query() ) {
			ob_start();
			pvPrintNotices();
			$notices = ob_get_clean();
			$content = $notices . $content;
		}

		if ( ! is_singular( Pro_VIP_Admin_Files::$postTypeId ) ) {
			return $content;
		}
		$GLOBALS[ 'pvFile' ] = Pro_VIP_File::find( get_the_ID() );
		ob_start();
		Pro_VIP_Template::load( 'content-' . ( $GLOBALS[ 'pvFile' ] ? 'single-download' : 'download-none' ) );


		return $content . ob_get_clean();
	}


	protected function downloadsSingle() {
		if ( is_singular( Pro_VIP_Admin_Files::$postTypeId ) ) {
			Pro_VIP::set( '_is_pro_vip', true );

		}
	}


	public function paymentSuccessPage() {
		if ( isset( $this->query[ 'isPaymentSuccessPage' ] ) && $this->query[ 'isPaymentSuccessPage' ] ) {
			global $wp_query;
			$wp_query->is_page = true;
			Pro_VIP::set( '_is_pro_vip', true );
			$this::load( 'payment/payment-success' );
			die;
		}
	}

	public function paymentFailedPage() {
		if ( isset( $this->query[ 'isPaymentFailedPage' ] ) && $this->query[ 'isPaymentFailedPage' ] ) {
			global $wp_query;
			$wp_query->is_page = true;
			Pro_VIP::set( '_is_pro_vip', true );
			$this::load( 'payment/payment-failed' );
			die;
		}
	}

	public function footerHTML() {
		if ( isPV() ) {
			$this::load( 'general-footer' );
		}
	}

	public function enqueueAssets() {
		wp_enqueue_style( 'pv-styles', PRO_VIP_URL . 'templates/assets/css/styles.css' );
		wp_enqueue_script(
			'pv-jquery.validate',
			PRO_VIP_URL . 'templates/assets/js/plugins/jquery.validate.min.js',
			array(
				'jquery'
			)
		);
		wp_enqueue_script(
			'pv-jquery-modal',
			PRO_VIP_URL . 'templates/assets/js/plugins/jquery.modal.min.js',
			array(
				'jquery'
			)
		);
		wp_enqueue_script(
			'pv-jquery-modal',
			PRO_VIP_URL . 'templates/assets/js/plugins/jquery.modal.min.js',
			array(
				'jquery'
			)
		);

		wp_enqueue_script( 'pv-scripts', PRO_VIP_URL . 'templates/assets/js/general.js', array( 'jquery' ) );
		wp_localize_script( 'pv-scripts', 'proVip', array(
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'l10n'    => array(
				'required_field' => __( 'This field is required.', 'provip' ),
				'valid_email'    => __( 'Please enter a valid email address.', 'provip' )
			)
		) );

	}


}
