<?php
/**
 * @class          Pro_VIP_Admin
 * @version        1.0
 * @package        Pro-VIP
 * @category       Class
 * @author         Pro-WP
 */

defined( 'ABSPATH' ) or die; // Prevents direct access

class Pro_VIP_Admin_Payments {

	static
		$postTypeId = 'provip_payment',
		$menuSlug = 'provip_payments';

	protected $_postClauses = array();

	public static function instance() {
		static $instance;
		if ( ! is_object( $instance ) ) {
			$instance = new self;
		}

		return $instance;
	}

	protected function __construct() {

		if ( ! is_admin() ) {
			return false;
		}


		add_action( 'init', array( $this, 'registerPostType' ), 0 );
		add_filter( 'manage_edit-' . self::$postTypeId . '_columns', array( $this, 'filterTableColumns' ) );
		add_filter( 'manage_edit-' . self::$postTypeId . '_sortable_columns', array( $this, 'sortableColumns' ) );

		add_action( 'manage_' . self::$postTypeId . '_posts_custom_column', array( $this, 'columns' ), 10, 2 );
		add_filter( 'bulk_actions-edit-provip_payment', array( $this, 'filterBulkActions' ) );
		add_filter( 'views_edit-provip_payment', array( $this, 'filterViews' ) );
		add_action( 'admin_init', array( $this, 'filterPages' ) );

		add_filter( 'parse_query', array( $this, 'filterQuery' ) );
		add_filter( 'admin_init', array( $this, 'handleDelete' ) );
		add_filter( 'bulk_post_updated_messages', array( $this, 'viewPayment' ) );
	}


	public function adminMenu() {
		$menu = PV_Framework_Admin_Menu::make();
		$menu->pageTitle( __( 'Payments', 'provip' ) );
		$menu->menuSlug( $this::$menuSlug );
		$menu->callback( array( $this, 'menuCallback' ) );
		$menu->parentSlug( Pro_VIP_Admin::$menuSlug );
	}


	public function registerPostType() {
		$labels = array(
			'name'               => _x( 'Payments', 'Post Type General Name', 'provip' ),
			'singular_name'      => _x( 'Payment', 'Post Type Singular Name', 'provip' ),
			'menu_name'          => __( 'Payments', 'provip' ),
			'name_admin_bar'     => __( 'Payments', 'provip' ),
			'parent_item_colon'  => __( 'Parent Item:', 'provip' ),
			'all_items'          => __( 'Payments', 'provip' ),
			'add_new_item'       => __( 'Add New Payment', 'provip' ),
			'add_new'            => __( 'Add Payment', 'provip' ),
			'new_item'           => __( 'New Payment', 'provip' ),
			'edit_item'          => __( 'Edit Payment', 'provip' ),
			'update_item'        => __( 'Update Payment', 'provip' ),
			'view_item'          => __( 'View Payment', 'provip' ),
			'search_items'       => __( 'Search Payments', 'provip' ),
			'not_found'          => __( 'Not found', 'provip' ),
			'not_found_in_trash' => __( 'Not found in Trash', 'provip' ),
		);

		$args = array(
			'labels'              => $labels,
			'public'              => false,
			'publicly_queryable'  => false,
			'show_ui'             => true,
			'show_in_menu'        => Pro_VIP_Admin::$menuSlug,
			'query_var'           => true,
			'has_archive'         => true,
			'hierarchical'        => false,
			'menu_position'       => null,
			'supports'            => array(),
			'taxonomies'          => array(),
			'can_export'          => true,
			'exclude_from_search' => true,
			'capability_type'     => 'post',
			'capabilities'        => array(
				'create_posts' => false, // Removes support for the "Add New" function
			)
		);

		register_post_type( $this::$postTypeId, $args );
	}

	public function filterTableColumns( $cols ) {

		$cols[ 'price' ]   = __( 'Price', 'provip' );
		$cols[ 'user' ]    = __( 'Author', 'provip' );
		$cols[ 'ip' ]      = __( 'IP', 'provip' );
		$cols[ 'status' ]  = __( 'Status', 'provip' );
		$cols[ 'gateway' ] = __( 'Gateway', 'provip' );
		$cols[ 'actions' ] = __( 'Actions', 'provip' );

		unset( $cols[ 'cb' ] );
		unset( $cols[ 'date' ] );
		unset( $cols[ 'title' ] );

		return $cols;
	}


	public function sortableColumns( $columns ) {
		$columns[ 'price' ]   = 'price';
		$columns[ 'ip' ]      = 'ip';
		$columns[ 'user' ]    = 'user';
		$columns[ 'status' ]  = 'status';
		$columns[ 'gateway' ] = 'gateway';

		return $columns;
	}

	public function filterPostClauses( $c ) {
		foreach ( $this->_postClauses as $key => $options ) {
			$sql = $options[ 'sql' ];
			switch ( $options[ 'action' ] ) {
				case 'append':
					$c[ $key ] .= $sql;
					break;
				case 'prepend':
					$c[ $key ] = $sql . $c[ $key ];
					break;
				case 'replace':
					$c[ $key ] = $sql;
					break;
			}
		}

		return $c;
	}

	public function filterQuery( $query ) {

		global $current_screen;

		if (
			! is_admin()
			|| empty( $current_screen ) || self::$postTypeId !== $current_screen->post_type
		) {
			return $query;
		}

		add_filter( 'posts_clauses', array( $this, 'filterPostClauses' ) );


		$query = $this->_prepareOrderQuery( $query );
		$query = $this->_prepareSearchQuery( $query );


		return $query;
	}

	protected function _prepareSearchQuery( $query ) {
		if ( ! empty( $_REQUEST[ 's' ] ) && is_string( $_REQUEST[ 's' ] ) ) {
			$search  = $_REQUEST[ 's' ];
			$hashids = new Hashids( pvGetOption( 'encryption_key', '' ) );
			$id      = $hashids->decode( $search );
			if ( is_array( $id ) && ! empty( $id[ 0 ] ) && is_numeric( $id[ 0 ] ) ) {
				$this->_postClauses[ 'where' ] = array(
					'action' => 'append',
					'sql'    => ' OR ID = ' . $id[ 0 ]
				);
			}
		}

		return $query;
	}

	protected function _prepareOrderQuery( $query ) {
		$orderItems = array( 'price', 'ip', 'user', 'status', 'gateway' );


		if ( empty( $_REQUEST[ 'orderby' ] ) || ! in_array( $_REQUEST[ 'orderby' ], $orderItems ) ) {
			return $query;
		}

		switch ( $_REQUEST[ 'orderby' ] ) {

			case 'price':
			case 'ip':
			case 'gateway':
				$query->query_vars[ 'orderby' ]  = 'meta_value';
				$query->query_vars[ 'meta_key' ] = '_provip_' . $_REQUEST[ 'orderby' ];
				break;

			case 'user':
				$query->query_vars[ 'orderby' ] = 'post_author';
				break;
			case 'status':
				$this->_postClauses[ 'orderby' ] = array(
					'action' => 'replace',
					'sql'    => 'post_status ' . strtoupper( ! empty( $_REQUEST[ 'order' ] ) && in_array( strtolower( $_REQUEST[ 'order' ] ), array(
							'asc',
							'desc'
						) ) ? $_REQUEST[ 'order' ] : 'asc' )
				);
				break;

		}
		$query->query_vars[ 'order' ] = ! empty( $_REQUEST[ 'order' ] ) && in_array( strtolower( $_REQUEST[ 'order' ] ), array(
			'asc',
			'desc'
		) ) ? $_REQUEST[ 'order' ] : 'asc';

		return $query;
	}


	public function columns( $column_name, $id ) {
		$payment = new Pro_VIP_Payment( $id );
		switch ( $column_name ) {
			case 'id':
				echo $id;
				break;
			case 'price':
				echo Pro_VIP_Currency::priceHTML( $payment->price );
				break;
			case 'user':
				$data = get_userdata( $payment->user );
				if ( empty( $data ) ) {
					echo $payment->custom[ 'first-name' ];

					return;
				}
				$data = $data->data;
				echo ! empty( $data->display_name ) ? $data->display_name : ( ! empty( $data->user_nicename ) ? $data->user_nicename : $data->user_login );
				break;
			case 'ip':
				echo $payment->userIp;
				break;
			case 'gateway':
				echo Pro_VIP_Payment_Gateway::getGateway( $payment->gateway )->adminLabel;
				break;
			case 'status':
				echo Pro_VIP_Payment::status( $payment->status );
				break;
			case 'actions':
				echo '<a class="pv-confirm" href="' . esc_url( add_query_arg( array(
						'pv-action' => 'delete-payment',
						'_wpnonce'  => wp_create_nonce( 'pv-payment-delete' ),
						'payment'   => $payment->paymentId
					) ) ) . '">' . __( 'Delete', 'provip' ) . '</a> - ';
				echo '<a href="' . esc_url( add_query_arg( array(
						'pv-action' => 'view',
						'payment'   => $payment->paymentId
					) ) ) . '">' . __( 'View', 'provip' ) . '</a>';
				break;
			default:
				break;
		}
	}

	public function filterBulkActions( $actions ) {
		unset( $actions[ 'edit' ] );

		return $actions;
	}

	public function filterViews( $views ) {

		unset( $views[ 'publish' ] );
		unset( $views[ 'pending' ] );

		return $views;
	}

	public function filterPages() {

		global $pagenow;

		$stop = false;

		if ( $pagenow === 'post-new.php' && isset( $_GET[ 'post_type' ] ) && $_GET[ 'post_type' ] === 'provip_payment' ) {
			$stop = true;
		}

		if ( $pagenow === 'post.php' && isset( $_GET[ 'post' ] ) && is_numeric( $_GET[ 'post' ] ) && ( $post = get_post( $_GET[ 'post' ] ) ) && $post->post_type == 'provip_payment' ) {
			$stop = true;
		}


		if ( $stop ) {
			wp_die( __( "You don't have access to this page.", 'provip' ) );
			die;
		}

	}

	public function viewPayment( $m ) {


		if (
			! isset( $_REQUEST[ 'pv-action' ] )
			|| $_REQUEST[ 'pv-action' ] !== 'view'
			|| ! isset( $_REQUEST[ 'payment' ] )
			|| ! is_numeric( $_REQUEST[ 'payment' ] )
		) {
			return $m;
		}

		try {
			$payment = new Pro_VIP_Payment( $_REQUEST[ 'payment' ] );
		} catch ( Exception $e ) {
			Pro_VIP_Admin::addNotice( $e->getMessage() );

			return $m;
		}


		include( ABSPATH . 'wp-admin/admin-header.php' );

		echo '<div class="wrap">';

		Pro_VIP::loadView( 'admin/payments/view-payment', array( 'payment' => $payment ) );

		echo '</div>';

		include( ABSPATH . 'wp-admin/admin-footer.php' );


		die;


	}

	public function handleDelete() {

		global $pagenow;

		if (
			$pagenow === 'edit.php'
			&& isset( $_REQUEST[ 'post_type' ] )
			&& $_REQUEST[ 'post_type' ] == 'provip_payment'
			&& isset( $_REQUEST[ 'pv-action' ] )
			&& is_string( $_REQUEST[ 'pv-action' ] )
			&& $_REQUEST[ 'pv-action' ] == 'delete-payment'
			&& isset( $_REQUEST[ 'payment' ] )
			&& is_numeric( $_REQUEST[ 'payment' ] )
			&& isset( $_REQUEST[ '_wpnonce' ] )
			&& is_string( $_REQUEST[ '_wpnonce' ] )
			&& wp_verify_nonce( $_REQUEST[ '_wpnonce' ], 'pv-payment-delete' )
		) {
			try {
				$payment = new Pro_VIP_Payment( $_REQUEST[ 'payment' ] );
			} catch ( Exception $e ) {
				Pro_VIP_Admin::addNotice( $e->getMessage() );

				return false;
			}
			$delete = $payment->delete();
			Pro_VIP_Admin::addNotice( $delete ? __( 'Payment Deleted', 'provip' ) : __( 'An error happened. Please try again.', 'provip' ), $delete );
		}

	}

}
