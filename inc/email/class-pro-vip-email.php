<?php
/**
 * @class          Pro_VIP_Email
 * @version        1.0
 * @package        Pro-VIP
 * @category       Class
 * @author         Pro-WP Team
 */

defined( 'ABSPATH' ) or die; // Prevents direct access

class Pro_VIP_Email {

	protected static
		$_logo,
		$_emailFrom,
		$_emailFromName,
		$_rtl = false;

	public static function instance() {
		static $instance;
		if ( ! is_object( $instance ) ) {
			$instance = new self;
		}

		return $instance;
	}

	protected function __construct() {

		self::$_emailFromName = pvGetOption( 'email_from_name', get_option( 'blogname', '' ) );
		self::$_emailFrom     = pvGetOption( 'email_from', '' );
		self::$_logo          = pvGetOption( 'email_template_logo', PRO_VIP_URL . 'templates/assets/img/logo-placeholder.png' );
		if ( is_array( self::$_logo ) && ! empty( self::$_logo[ 0 ] ) ) {
			self::$_logo = self::$_logo[ 0 ];
		}
	}

	public static function send( $to, $subject, $content ) {
		add_filter( 'wp_mail_from', array( __CLASS__, '_changeEmailFrom' ) );
		add_filter( 'wp_mail_from_name', array( __CLASS__, '_changeEmailFromName' ) );

		$headers = array( 'Content-Type: text/html; charset=UTF-8' );

		$send = wp_mail(
			$to,
			$subject,
			$content,
			$headers
		);


		remove_filter( 'wp_mail_from', array( __CLASS__, '_changeEmailFrom' ) );
		remove_filter( 'wp_mail_from_name', array( __CLASS__, '_changeEmailFromName' ) );

		return $send;
	}


	public static function template( $content, $variables = array() ) {

		$variables = array_merge(
			array(
				'pageTitle'         => get_option( 'blogname' ),
				'headerDescription' => get_option( 'blogdescription' ),
				'logoSrc'           => self::$_logo,
				'siteUrl'           => site_url( '/' ),
				'content'           => $content,
				'preHeader'         => strip_tags( $content ),
				'emailFooter'       => pvGetOption( 'email_footer', '' )
			),
			$variables
		);


		$view = Pro_VIP::loadView(
			'email/template' . ( self::$_rtl ? '-rtl' : '' ),
			array(),
			true
		);

		return self::filterTags( $view, $variables );
	}

	public static function _changeEmailFrom( $old ) {
		return empty( self::$_emailFrom ) ? $old : self::$_emailFrom;
	}

	public static function _changeEmailFromName( $old ) {
		return empty( self::$_emailFromName ) ? $old : self::$_emailFromName;
	}


	public static function getMail( $mail, $default = '' ) {

		static $strings;

		if ( ! is_array( $strings ) ) {
			$strings = require dirname( __FILE__ ) . '/strings.php';
		}


		return ! empty( $strings[ $mail ] ) ? $strings[ $mail ] : $default;
	}


	public static function filterTags( $content, $tags = array() ) {

		$defaultTags = array(
			'site-name' => get_option( 'blogname' ),
			'site-url'  => site_url( '/' )
		);

		$tags = $defaultTags + $tags;

		foreach ( $tags as $tag => $value ) {
			$search  = $tag;
			$content = str_replace( '{{' . $search . '}}', $value, $content );
		}

		return $content;
	}
}
