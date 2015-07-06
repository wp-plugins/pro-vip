<?php
/**
 * @class          Pro_VIP_Admin_Files_Metabox
 * @version        1.0
 * @package        Pro-VIP
 * @category       Class
 * @author         Pro-WP Team
 */

defined( 'ABSPATH' ) or die; // Prevents direct access

class Pro_VIP_Admin_Files_Metabox {

	/**
	 * @var PV_Framework_Form_Builder
	 */
	public $metaboxFormBuilder;

	public static function instance() {
		static $instance;
		if ( ! is_object( $instance ) ) {
			$instance = new self;
		}

		return $instance;
	}

	protected function __construct() {
		PV_Framework_Metabox::make( 'provip_file', array( $this, 'metabox' ) );
		add_action( 'pv_framework_metabox_save-provip_file', array( $this, 'onMetaboxSave' ) );
	}

	public function metabox( PV_Framework_Metabox $metabox ) {
		$metabox->post_types     = Pro_VIP_Admin_Files::$postTypeId;
		$metabox->title          = __( 'File Settings', 'provip' );
		$metabox->save_in_single = false;
		$fields                  = $metabox->form_builder;

		global $post;

		if ( $post->post_type !== Pro_VIP_Admin_Files::$postTypeId ) {
			return false;
		}

		$this->metaboxFormBuilder = $fields;
		$this->metaboxFields( $fields );

		$fields->html(
			array( $this, 'metaboxDisplayFields' )
		);

	}


	public function metaboxFields( PV_Framework_Form_Builder $formBuilder ) {
		$defaultLevels = array();
		foreach ( pvGetOption( 'vip_levels', array() ) as $id => $level ) {
			if ( $level[ 'check-by-default' ] == 'yes' ) {
				$defaultLevels[ ] = $id;
			}
		}

		$formBuilder->multicheckbox( '_provip_plans', pvGetLevels() )->std_val( $defaultLevels )->label( __( 'Levels', 'provip' ) )->hide();

		$formBuilder->checkbox( '_provip_single_sale' )->hide()->inputClasses( 'single-sale' );

		$formBuilder->textfield( '_provip_file_price' )->hide()->label( __( 'Price', 'provip' ) );

	}


	public function metaboxDisplayFields() {
		global $post;

		return Pro_VIP::loadView(
			'admin/files/metabox-wrapper',
			array(
				'post'        => $post,
				'formBuilder' => $this->metaboxFormBuilder
			),
			true
		);
	}

	public function onMetaboxSave() {
		global $post;
		if ( empty( $_POST[ 'provip-file-settings' ] ) || ! is_array( $_POST[ 'provip-file-settings' ] ) ) {
			return false;
		}
		if ( ! empty( $_POST[ 'provip-file-settings' ][ 'order' ] ) && is_array( $_POST[ 'provip-file-settings' ][ 'order' ] ) ) {
			update_post_meta( $post->ID, '_provip_files_order', array_keys( $_POST[ 'provip-file-settings' ][ 'order' ] ) );
		}

		if ( ! empty( $_POST[ 'provip-file-settings' ][ 'files' ] ) && is_array( $_POST[ 'provip-file-settings' ][ 'files' ] ) ) {
			foreach ( $_POST[ 'provip-file-settings' ][ 'files' ] as $fileIndex => $fileData ) {
				Pro_VIP_File::updateFileDlName( $fileData[ 'dl-name' ], $post->ID, $fileIndex );
				Pro_VIP_File::updateFilePrice( $fileData[ 'price' ], $post->ID, $fileIndex );
			}
		}
	}

}
