<?php
/**
 * @class          Pro_VIP_Frontend_AJAX
 * @version        1.0
 * @package        Pro-VIP
 * @category       Class
 * @author         Pro-WP Team
 */

defined( 'ABSPATH' ) or die; // Prevents direct access

class Pro_VIP_Frontend_AJAX {


	public static function instance() {
		static $instance;
		if ( ! is_object( $instance ) ) {
			$instance = new self;
		}

		return $instance;
	}


	protected function __construct() {
		$ajax = Pro_VIP::$ajax;
		$ajax->on( 'frontend.calculatePlanPrice', array( $this, 'ajaxCalcPlanPrice' ) );
	}

	public function ajaxCalcPlanPrice() {

		if ( empty( $_POST[ 'plan' ] ) || ! is_numeric( $_POST[ 'plan' ] ) || empty( $_POST[ 'level' ] ) || ! is_numeric( $_POST[ 'level' ] ) ) {
			return 0;
		}

		$plan  = pvGetPlan( $_POST[ 'plan' ] );
		$level = pvGetLevel( $_POST[ 'level' ] );

		if ( empty( $plan ) ) {
			return 0;
		}

		$cost = $plan[ 'cost' ][ $_POST[ 'level' ] ];

		return array(
			'status'    => 1,
			'priceHtml' => Pro_VIP_Currency::priceHTML( $cost ),
			'price'     => $cost
		);
	}


}
