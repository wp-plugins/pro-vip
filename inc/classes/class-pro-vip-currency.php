<?php
/**
 * @class          Pro_VIP_Currency
 * @version        1.0
 * @package        Pro-VIP
 * @category       Class
 * @author         Pro-WP Team
 */

defined( 'ABSPATH' ) or die; // Prevents direct access

class Pro_VIP_Currency {

	protected static $_currencies;

	public static function instance() {
		static $instance;
		if( !is_object( $instance ) ){
			$instance = new self;
		}
		return $instance;
	}

	protected function __construct(){
		self::$_currencies = self::_getDefaultCurrencies();

	}

	public static function getAll() {
		return apply_filters( 'pro_vip_currencies_list', self::$_currencies );
	}

	public static function getCurrencySymbol( $currency ) {
//		if( empty( self::$_currencies[$currency] ) )
	}

	public static function getCurrency( $id ) {
		$list = self::getAll();
		foreach ( $list as $cId => $currency ) {
			if ( $cId === $id ) {
				return $currency;
			}
		}

		return false;
	}

	protected static function _getDefaultCurrencies() {
		$list = array(
			'USD' => array(
				'name'   => __( 'United States dollar', 'provip' ),
				'symbol' => '$'
			),
			'EUR' => array(
				'name'   => __( 'Euro', 'provip' ),
				'symbol' => '€'
			),
			'JPY' => array(
				'name'   => __( 'Japanese yen', 'provip' ),
				'symbol' => '¥'
			),
			'GBP' => array(
				'name'   => __( 'Pound sterling', 'provip' ),
				'symbol' => '£'
			),
			'AUD' => array(
				'name'   => __( 'Australian dollar', 'provip' ),
				'symbol' => '$'
			),
			'CHF' => array(
				'name'   => __( 'Swiss franc', 'provip' ),
				'symbol' => 'Fr'
			),
			'CAD' => array(
				'name'   => __( 'Canadian dollar', 'provip' ),
				'symbol' => '$'
			),
			'MXN' => array(
				'name'   => __( 'Mexican peso', 'provip' ),
				'symbol' => '$'
			),
			'CNY' => array(
				'name'   => __( 'Chinese yuan', 'provip' ),
				'symbol' => '¥'
			),
			'NZD' => array(
				'name'   => __( 'New Zealand dollar', 'provip' ),
				'symbol' => '$'
			),
		);

		return $list;
	}

	public static function priceFormat() {
		$currency_pos = pvGetOption( 'currency_pos', 'left' );

		switch ( $currency_pos ) {
			default :
			case 'left' :
				$format = '%1$s%2$s';
				break;
			case 'right' :
				$format = '%2$s%1$s';
				break;
			case 'left_space' :
				$format = '%1$s&nbsp;%2$s';
				break;
			case 'right_space' :
				$format = '%2$s&nbsp;%1$s';
				break;
		}

		return apply_filters( 'pro_vip_price_format', $format, $currency_pos );
	}

	public static function priceHTML( $price, $args = array() ) {

		$settings = array_merge(
			array(
				'currency'           => pvGetOption( 'currency', 'USD' ),
				'decimal_separator'  => wp_specialchars_decode( stripslashes( pvGetOption( 'price_decimal_sep', '.' ) ), ENT_QUOTES ),
				'thousand_separator' => wp_specialchars_decode( stripslashes( pvGetOption( 'price_thousand_sep', ',' ) ), ENT_QUOTES ),
				'decimals'           => absint( pvGetOption( 'price_num_decimals', 2 ) ),
				'price_format'       => self::priceFormat()
			),
			$args
		);

		$currency = self::getCurrency( $settings[ 'currency' ] );

		$negative = $price < 0;
		$price    = floatval( $negative ? $price * - 1 : $price );
		$price    = number_format( $price, $settings[ 'decimals' ], $settings[ 'decimal_separator' ], $settings[ 'thousand_separator' ] );


		$formatted_price = ( $negative ? '-' : '' ) . sprintf( $settings[ 'price_format' ], $currency[ 'symbol' ], $price );


		$return          = '<span class="amount">' . $formatted_price . '</span>';


		return apply_filters( 'pro_vip_price_html', $return, $price, $args );

	}
}
