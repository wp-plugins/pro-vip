<?php
/**
 * @class          Pro_VIP_Admin_Files
 * @version        1.0
 * @package        Pro-VIP
 * @category       Class
 * @author         Pro-WP Team
 */

defined( 'ABSPATH' ) or die; // Prevents direct access

class Pro_VIP_Admin_Files {

	public static $postTypeId = 'vip-files';


	public static function init() {
		return new self;
	}

	protected function __construct() {
		add_action( 'init', array( $this, 'registerPostType' ), 0 );
		Pro_VIP_Admin_Files_Metabox::instance();
		Pro_VIP_Admin_Files_AJAX_Actions::instance();
	}


	public function registerPostType() {
		$labels = array(
			'name'               => _x( 'Files', 'Post Type General Name', 'provip' ),
			'singular_name'      => _x( 'File', 'Post Type Singular Name', 'provip' ),
			'menu_name'          => __( 'Pro-VIP', 'provip' ),
			'name_admin_bar'     => __( 'Files', 'provip' ),
			'parent_item_colon'  => __( 'Parent Item:', 'provip' ),
			'all_items'          => __( 'Files', 'provip' ),
			'add_new_item'       => __( 'Add New File', 'provip' ),
			'add_new'            => __( 'Add File', 'provip' ),
			'new_item'           => __( 'New File', 'provip' ),
			'edit_item'          => __( 'Edit File', 'provip' ),
			'update_item'        => __( 'Update File', 'provip' ),
			'view_item'          => __( 'View File', 'provip' ),
			'search_items'       => __( 'Search Files', 'provip' ),
			'not_found'          => __( 'Not found', 'provip' ),
			'not_found_in_trash' => __( 'Not found in Trash', 'provip' ),
		);

		$args = array(
			'labels'              => $labels,
			'public'              => true,
			'publicly_queryable'  => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'query_var'           => true,
			'rewrite'             => array( 'slug' => 'downloads' ),
			'has_archive'         => true,
			'hierarchical'        => false,
			'menu_icon'           => PRO_VIP_URL . 'inc/admin/assets/img/menu-icon.png',
			'supports'            => array( 'title', 'editor', 'thumbnail' ),
			'taxonomies'          => array( 'category', 'post_tag' ),
			'can_export'          => true,
			'exclude_from_search' => true,
			'capability_type'     => 'post'
		);

		register_post_type( $this::$postTypeId, $args );
	}

	public static function getNewFileIndex( $postId ) {
		$index = (int) get_post_meta( $postId, '_provip_file_index', true );
		$index ++;

		if ( ! update_post_meta( $postId, '_provip_file_index', $index ) ) {
			return false;
		}

		return $index;

	}

}
