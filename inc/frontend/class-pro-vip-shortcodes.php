<?php
/**
 * @class          Pro_VIP_Shortcodes
 * @version        1.0
 * @package        Pro-VIP
 * @category       Class
 * @author         Pro-WP Team
 */

defined( 'ABSPATH' ) or die; // Prevents direct access

class Pro_VIP_Shortcodes {

	public static function instance() {
		static $instance;
		if ( ! is_object( $instance ) ) {
			$instance = new self;
		}

		return $instance;
	}


	protected function __construct() {
		add_shortcode( 'pv-plans-form', array( $this, 'plansForm' ) );
		add_shortcode( 'pv-receipt', array( $this, 'receipt' ) );
		add_shortcode( 'pv-user-payments', array( $this, 'userPayments' ) );
		add_shortcode( 'pv-user-all-plans', array( $this, 'userPlans' ) );
		add_shortcode( 'pv-user-plan', array( $this, 'userPlan' ) );
		add_shortcode( 'pv-login-form', array( $this, 'loginForm' ) );
	}


	public function plansForm() {
		ob_start();

		if ( ! is_user_logged_in() ) {
			return pvLoginMsg();
		}


		Pro_VIP_Template::load( 'plans' );

		return ob_get_clean();
	}

	public function receipt() {
		ob_start();

		Pro_VIP_Template::load( 'shortcodes/receipt', '', array( 'payment' => Pro_VIP::get( 'payment' ) ) );

		return ob_get_clean();
	}


	public function userPayments() {
		ob_start();

		if ( ! is_user_logged_in() ) {
			return pvLoginMsg();
		}

		$query = new WP_Query(
			array(
				'post_type'      => 'provip_payment',
				'posts_per_page' => 15,
				'author'         => get_current_user_id()
			)
		);

		Pro_VIP_Template::load( 'shortcodes/user-payments', '', array( 'query' => $query ) );

		wp_reset_postdata();

		return ob_get_clean();
	}

	public function userPlans() {

	}

	public function userPlan( $args = array() ) {

		$settings = array_merge(
			array(
				'level' => pvGetOption( 'default_vip_level' )
			),
			( ! empty( $args ) && is_array( $args ) ? $args : array() )
		);

		if ( ! is_user_logged_in() ) {
			return pvLoginMsg();
		}

		ob_start();

		Pro_VIP_Template::load( 'shortcodes/user-plan', '', array(
			'level'     => Pro_VIP_Member::getLevelData( $settings[ 'level' ] ),
			'levelInfo' => pvGetLevel( (int) $settings[ 'level' ] )
		) );

		return ob_get_clean();

	}

	public function loginForm() {
		return wp_login_form( array(
			'echo' => false
		) );
	}

}
