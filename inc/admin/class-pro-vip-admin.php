<?php
/**
 * @class          Pro_VIP_Admin
 * @version        1.0
 * @package        Pro-VIP
 * @category       Class
 * @author         Pro-WP
 */

defined( 'ABSPATH' ) or die; // Prevents direct access

class Pro_VIP_Admin {

	protected static $_notices = array();

	static $menuSlug = 'edit.php?post_type=vip-files';

	public static function instance() {
		static $instance;
		if ( ! is_object( $instance ) ) {
			$instance = new self;
		}

		return $instance;
	}

	protected function __construct() {

		$this->adminMenu();
		add_action( 'admin_menu', array( $this, 'changeMenuOrder' ), 999999 );
		add_filter( 'custom_menu_order', array( $this, 'changeSubmenuOrder' ) );

		add_filter( 'wp_dashboard_widgets', array( $this, 'addDashboardWidgets' ) );

		Pro_VIP_Admin_Files::init();

		Pro_VIP_Admin_Payments::instance();
		Pro_VIP_Admin_Tools::instance();
		Pro_VIP_Admin_Settings::init();

		Pro_VIP_Admin_Users_Column::instance();

		Pro_VIP::$ajax->before( 'admin', array( $this, 'filterAdminAjaxRequests' ) );


		add_filter( 'views_users', array( $this, 'addView' ) );
		add_filter( 'pre_user_query', array( $this, 'filterAdminVipUsersList' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'loadAssets' ) );
		add_action( 'admin_notices', array( $this, 'adminNotices' ) );
	}

	public function changeMenuOrder() {
		global $menu;

		$menu[ "58.4" ] = array(
			'',
			'read',
			'separator-' . $this::$menuSlug,
			'',
			'wp-menu-separator ' . $this::$menuSlug
		);

		foreach ( $menu as $order => $item ) {

			if ( ! empty( $item[ 2 ] ) && $item[ 2 ] === self::$menuSlug ) {

				$menuItem = $item;
				unset( $menu[ $order ] );

				$menu[ '58.5' ] = $menuItem;

			}

		}


	}

	public function changeSubmenuOrder( $menu_ord ) {

		global $submenu;


		if ( empty( $submenu[ 'edit.php?post_type=vip-files' ] ) ) {
			return $menu_ord;
		}

		$arr    = array();
		$arr[ ] = $submenu[ 'edit.php?post_type=vip-files' ][ 18 ];
		$arr[ ] = $submenu[ 'edit.php?post_type=vip-files' ][ 5 ];
		$arr[ ] = $submenu[ 'edit.php?post_type=vip-files' ][ 10 ];
		$arr[ ] = $submenu[ 'edit.php?post_type=vip-files' ][ 15 ];
		$arr[ ] = $submenu[ 'edit.php?post_type=vip-files' ][ 16 ];
		$arr[ ] = $submenu[ 'edit.php?post_type=vip-files' ][ 17 ];
		$arr[ ] = $submenu[ 'edit.php?post_type=vip-files' ][ 19 ];
		$arr[ ] = $submenu[ 'edit.php?post_type=vip-files' ][ 20 ];

		$submenu[ 'edit.php?post_type=vip-files' ] = $arr;

		return $menu_ord;
	}

	public function adminMenu() {
		PV_Framework_Admin_Menu::make()
		                       ->pageTitle( __( 'Dashboard', 'provip' ) )
		                       ->menuSlug( 'provip_dashboard' )
		                       ->menuTitle( __( 'Dashboard', 'provip' ) )
		                       ->callback( array( $this, 'dashboardMenu' ) )
		                       ->parentSlug( $this::$menuSlug );
	}

	public function filterAdminAjaxRequests() {
		if ( ! is_admin() || ! is_user_logged_in() || ! current_user_can( 'manage_options' ) ) {
			return 0;
		}
	}

	public function addView( $views ) {
		$button = '<a href="users.php?filter=vip_users" class="' . ( ! empty( $_REQUEST[ 'filter' ] ) && $_REQUEST[ 'filter' ] == 'vip_users' ? 'current' : '' ) . '">';
		$button .= __( 'VIP Members', 'provip' );
		$button .= '</a>';


		$views[ 'my' ] = $button;

		return $views;
	}

	public function filterAdminVipUsersList( $query ) {
		global $pagenow, $wp_query;

		if ( is_admin() && $pagenow == 'users.php' && ! empty( $_REQUEST[ 'filter' ] ) && $_REQUEST[ 'filter' ] == 'vip_users' ) {

			global $wpdb;

			$table = Pro_VIP_Member::$table;

			$query->query_from .= " INNER JOIN {$table} ON {$wpdb->users}.ID = {$table}.user_ID ";

			$currentDate = current_time( 'mysql' );
			$query->query_where .= " AND {$table}.expiration_date > '{$currentDate}' ";
		}
	}

	public function menuCallback() {

	}

	public function loadAssets() {

		global $post, $pagenow, $post_type;

		$pvPage = '';

		$load = false;

		if ( $pagenow == 'post-new.php' && ! empty( $_REQUEST[ 'post_type' ] ) && $_REQUEST[ 'post_type' ] == 'vip-files' ) {
			$load = true;
		}
		if ( $pagenow == 'edit.php' && ! empty( $_REQUEST[ 'post_type' ] ) && $_REQUEST[ 'post_type' ] == 'provip_payment' ) {
			$load = true;
		}
		if ( $pagenow == 'post.php' && ! empty( $post ) && $post->post_type == 'vip-files' ) {
			$load = true;
		}

		if ( $pagenow == 'index.php' ) {
			$load = true;
		}

		if ( $pagenow == 'users.php' ) {
			$load = true;
		}

		if ( in_array( $post_type, pvGetOption( 'filter_content_post_types_metabox', array( 'post' ) ) ) ) {
			$load = true;
		}

		if (
			isset( $_REQUEST[ 'page' ] ) &&
			in_array( $_REQUEST[ 'page' ], array( 'provip_settings', 'provip_tools', 'provip_dashboard' ) )
		) {
			$load   = true;
			$pvPage = str_replace( 'provip_', '', $_REQUEST[ 'page' ] );
		}

		if ( ! $load ) {
			return false;
		}

		wp_enqueue_script( 'jquery-ui-sortable' );
		wp_enqueue_script(
			'wp-vip-jquery-file-upload-transport',
			PRO_VIP_URL . 'inc/admin/assets/js/plugins/jquery.iframe-transport.js'
		);
		wp_enqueue_script(
			'wp-vip-jquery-file-upload',
			PRO_VIP_URL . 'inc/admin/assets/js/plugins/jquery.fileupload.js',
			array(
				'jquery-ui-core',
				'jquery-ui-widget'
			)
		);


		if ( $pvPage == 'tools' ) {
			wp_enqueue_style( 'jquery-ui-css', 'http://code.jquery.com/ui/1.9.1/themes/base/jquery-ui.css' );
			wp_enqueue_script( 'jquery-ui-datepicker' );
		}


		wp_enqueue_script(
			'wp-vip-general',
			PRO_VIP_URL . 'inc/admin/assets/js/general.js',
			array()
		);
		wp_localize_script(
			'wp-vip-general',
			'wpVip',
			array(
				'l10n' => array(
					'confirm'        => __( 'Are you sure?', 'provip' ),
					'error_happened' => __( 'An error happened. Please try again.', 'provip' ),
					'reference_key'  => __( 'Reference Key', 'provip' )
				)
			)
		);

		wp_enqueue_style(
			'wp-vip-style',
			PRO_VIP_URL . 'inc/admin/assets/css/admin.css'
		);
//		wp_enqueue_style(
//			'jquery-ui-css',
//			'//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css'
//		);
	}

	public static function addNotice( $msg, $type = 'error' ) {
		self::$_notices[ Pro_VIP_Notices::noticeType( $type ) == 'success' ? 'updated' : 'error' ][ ] = $msg;
	}

	public function adminNotices() {
		foreach ( self::$_notices as $type => $notices ) {
			foreach ( $notices as $notice ) {
				echo '<div class="' . $type . '">
				<p>' . $notice . '</p>
			</div> ';
			}
		}
	}

	public function dashboardMenu() {
		Pro_VIP::loadView( 'admin/dashboard/index' );
	}

	public function addDashboardWidgets( $widgets ) {
		if ( current_user_can( 'manage_options' ) ) {

			wp_add_dashboard_widget(
				'pro-vip-overview',
				__( 'Pro VIP Overview', 'provip' ),
				array(
					$this,
					'dashboardOverviewWidget'
				)
			);
		}

		return $widgets;
	}

	public function dashboardOverviewWidget() {
		Pro_VIP::loadView( 'admin/dashboard/statistic-table' );
	}
}
