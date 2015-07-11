<?php
/**
 * @class          Pro_VIP_Filter_Content
 * @version        1.0
 * @package        Pro-VIP
 * @category       Class
 * @author         Pro-WP
 */

defined( 'ABSPATH' ) or die; // Prevents direct access

class Pro_VIP_Filter_Content {

	public static function instance() {
		static $instance;
		if ( ! $instance instanceof self ) {
			$instance = new self;
		}

		return $instance;
	}

	protected function __construct() {
//		add_action('admin_init', function(){
//			global $post_type;
			PV_Framework_Metabox::make( 'pv_filter_content', array( $this, 'metabox' ) );
//		});
		add_filter( 'the_content', array( $this, 'filterContent' ) );

	}

	public function filterContent( $content ) {
		global $post;
		$meta = get_post_meta( $post->ID, 'pv_filter_content', true );

		$meta = array_merge(
			array(
				'filter_content'  => 'no',
				'levels'          => array(),
				'login_msg'       => __( 'To see the post login first.', 'provip' ),
				'restriction_msg' => __( 'Only %s users can see the post.', 'provip' )
			),
			is_array( $meta ) ? $meta : array()
		);

		if ( $meta[ 'filter_content' ] == 'yes' ) {

			if ( ! is_user_logged_in() ) {
				return $meta[ 'login_msg' ];
			}

			$user = wp_get_current_user();

			if ( $user->has_cap( 'manage_options' ) ) {
				return $content;
			}

			$intersect = array_intersect( array_flip( pvGetOption( 'default_vip_roles', array() ) ), $user->roles );
			if ( ! empty( $intersect ) ) {
				return $content;
			}


			if ( Pro_VIP_Member::isVip( $user->ID, $meta[ 'levels' ] ) ) {
				return $content;
			}

			$levels = array();
			foreach ( $meta[ 'levels' ] as $level ) {
				$l         = pvGetLevel( $level );
				$levels[ ] = $l[ 'name' ];
			}

			return sprintf( $meta[ 'restriction_msg' ], implode( ',', $levels ) );

		}

		return $content;
	}

	public function metabox( PV_Framework_Metabox $metabox ) {

		$metabox->title      = __( 'Pro VIP', 'provip' );
		$metabox->post_types = pvGetOption( 'filter_content_post_types_metabox', array( 'post' ) );
		$fields              = $metabox->form_builder;

		$fields
			->dropdown( 'filter_content', array( 'no' => __( 'No', 'provip' ), 'yes' => __( 'Yes', 'provip' ) ) )
			->label( __( 'Filter Content', 'provip' ) );

		$fields->multicheckbox( 'levels', pvGetLevels() )->std_val( array() )->label( __( 'Levels', 'provip' ) );

		$fields
			->wpEditor( 'login_msg' )
			->std_val( __( 'To see the post login first.', 'provip' ) )
			->label( __( 'Login Message', 'provip' ) );

		$fields
			->wpEditor( 'restriction_msg' )
			->std_val( __( 'Only %s users can see the post.', 'provip' ) )
			->label( __( 'Restriction Message', 'provip' ) );
	}

}
