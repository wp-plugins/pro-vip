<?php
/**
 * @class          Pro_VIP_Admin_Users_Column
 * @version        1.0
 * @package        Pro-VIP
 * @category       Class
 * @author         Pro-WP
 */

defined( 'ABSPATH' ) or die; // Prevents direct access

class Pro_VIP_Admin_Users_Column {


	public static function instance() {
		static $instance;
		if ( ! is_object( $instance ) ) {
			$instance = new self;
		}

		return $instance;
	}

	protected function __construct() {
		add_filter( 'manage_users_columns', array( $this, 'addColumn' ) );
		add_action( 'manage_users_custom_column', array( $this, 'displayColumn' ), 10, 3 );
		add_filter( 'pre_user_query', array( $this, 'filterUsersQuery' ) );
		add_action( 'admin_head', array( $this, 'styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'loadAssets' ) );
	}

	public function addColumn( $columns ) {
		$columns[ 'provip' ] = __( 'VIP Data', 'provip' );

		return $columns;
	}

	public function displayColumn( $value, $column_name, $user_id ) {
		if ( 'provip' == $column_name ) {

			$accounts = Pro_VIP_Member::getVipAccounts( $user_id );

			return Pro_VIP::loadView(
				'admin/users/vip-column',
				array(
					'accounts' => $accounts,
					'user_ID'  => $user_id
				),
				true
			);

		}

		return $value;
	}

	public function filterUsersQuery( WP_User_Query $query ) {

	}

	public function loadAssets() {
		global $pagenow;
		if ( $pagenow !== 'users.php' ) {
			return false;
		}

		wp_enqueue_style( 'pv-tooltip', PRO_VIP_URL . 'inc/admin/assets/plugins/tooltipster/tooltipster.css' );
		wp_enqueue_script( 'pv-tooltip', PRO_VIP_URL . 'inc/admin/assets/plugins/tooltipster/jquery.tooltipster.min.js' );
	}

	public function styles() {
		global $pagenow;
		if ( $pagenow !== 'users.php' ) {
			return false;
		}
		?>
		<style type="text/css">
			table.users .column-provip {
				width: 320px;
			}
		</style>
		<script type="text/javascript">
			jQuery( function( $ ){
				$( '.pv-tooltip' ).each( function(){
					var $this = $( this );
					if( $this.next( '.tooltip-content' ).length )
						$( this ).tooltipster( {
							contentAsHTML: true,
							content      : $this.next( '.tooltip-content' ).html(),
							interactive  : true,
							animation    : 'fade'
						} );
				} );
			} );
		</script>
	<?php
	}

}
