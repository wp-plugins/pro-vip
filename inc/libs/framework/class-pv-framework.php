<?php
/**
 * @class          Pro_VIP_Framework
 * @version        1
 * @package        Phoenix Framework
 * @category       Class
 * @author         Vahidd
 */

defined( 'ABSPATH' ) or die; // Prevents direct access

class Pro_VIP_Framework {

	public static function instance() {
		static $instance;
		if ( ! is_object( $instance ) ) {
			$instance = new self;
		}

		return $instance;
	}

	protected function __construct() {
		PV_Framework_Admin_Menu::init();
		PV_Framework_Action::init();
		PV_Framework_Panel_Factory::init();
	}

}