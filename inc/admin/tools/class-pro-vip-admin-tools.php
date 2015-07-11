<?php
/**
 * @class          Pro_VIP_Admin
 * @version        1.0
 * @package        Pro-VIP
 * @category       Class
 * @author         Pro-WP
 */

defined( 'ABSPATH' ) or die; // Prevents direct access

class Pro_VIP_Admin_Tools {

	static
		$menuSlug = 'provip_tools';

	protected static $_tools = array();

	public static function instance() {
		static $instance;
		if ( ! is_object( $instance ) ) {
			$instance = new self;
		}

		return $instance;
	}

	public static function add( $name, $callback ) {
		self::$_tools[ md5( maybe_serialize( $callback ) ) ] = array(
			'label'    => $name,
			'callback' => $callback
		);
	}

	protected function __construct() {

		if ( ! is_admin() ) {
			return false;
		}

		$menu = PV_Framework_Admin_Menu::make();

		$menu->pageTitle( __( 'Tools', 'provip' ) )
		     ->callback( array( $this, 'menu' ) )
		     ->menuSlug( $this::$menuSlug )
		     ->menuTitle( __( 'Tools', 'provip' ) )
		     ->parentSlug( Pro_VIP_Admin::$menuSlug );


		Pro_VIP_Admin_Tools_VIP_Bulk_Edit::instance();
		Pro_VIP_Admin_Tools_System_Info::instance();
		Pro_VIP_Admin_Tools_Edit_User::instance();
	}

	public function menu() {

		$tool = false;

		if ( isset( $_GET[ 'tool' ] ) && is_string( $_GET[ 'tool' ] ) ) {
			if ( isset( self::$_tools[ $_GET[ 'tool' ] ] ) ) {
				$tool = self::$_tools[ $_GET[ 'tool' ] ];
			}
		}


		if ( $tool ) {
			echo '<div class="wrap">';
			echo '<h2>' . $tool[ 'label' ] . ' <a href="' . remove_query_arg( 'tool' ) . '" class="button">' . __( 'Back', 'provip' ) . '</a></h2>';
			echo '<div style="margin-top: 20px;">';
			call_user_func( $tool[ 'callback' ] );
			echo '</div>';
			echo '</div>';
		} else {
			Pro_VIP::loadView( 'admin/tools/index', array( 'tools' => self::$_tools ) );
		}

	}

}
