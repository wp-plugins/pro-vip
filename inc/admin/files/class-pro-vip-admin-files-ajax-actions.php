<?php
/**
 * @class          Pro_VIP_Admin_Files_AJAX_Actions
 * @version        1.0
 * @package        Pro-VIP
 * @category       Class
 * @author         Pro-WP Team
 */

defined( 'ABSPATH' ) or die; // Prevents direct access

class Pro_VIP_Admin_Files_AJAX_Actions {


	public static function instance() {
		static $Instance;
		if ( ! is_object( $Instance ) ) {
			$Instance = new self;
		}

		return $Instance;
	}

	protected function __construct() {
		Pro_VIP::$ajax->on( 'admin.uploadFile', array( $this, 'ajaxUploadFile' ) );
		Pro_VIP::$ajax->on( 'admin.deleteFile', array( $this, 'ajaxDeleteFile' ) );
	}

	public function ajaxDeleteFile() {

		if ( empty( $_POST[ 'nonce' ] ) || ! is_string( $_POST[ 'nonce' ] ) || ! wp_verify_nonce( $_POST[ 'nonce' ], 'wvDeleteFile' ) ) {
			return 0;
		}

		if ( empty( $_POST[ 'fileId' ] ) || ! is_numeric( $_POST[ 'fileId' ] ) || @get_post( $_POST[ 'fileId' ] )->post_type !== Pro_VIP_Admin_Files::$postTypeId ) {
			return 0;
		}

		if ( empty( $_POST[ 'fileIndex' ] ) || ! is_numeric( $_POST[ 'fileIndex' ] ) ) {
			return 0;
		}


		return (int) Pro_VIP_File::deleteFile( $_POST[ 'fileId' ], $_POST[ 'fileIndex' ] );

	}

	public function ajaxUploadFile() {

		if ( ! current_user_can( 'edit_posts' ) ) {
			return 0;
		}

		if ( empty( $_FILES[ 'file' ] ) ) {
			return 0;
		}

		if ( empty( $_POST[ 'nonce' ] ) || ! is_string( $_POST[ 'nonce' ] ) || ! wp_verify_nonce( $_POST[ 'nonce' ], 'provip_ajax_upload_file' ) ) {
			return 0;
		}

		if ( empty( $_POST[ 'postId' ] ) || ! is_numeric( $_POST[ 'postId' ] ) || @get_post( $_POST[ 'postId' ] )->post_type !== Pro_VIP_Admin_Files::$postTypeId ) {
			return 0;
		}

		if (
			! isset( $_FILES[ 'file' ][ 'error' ][ 0 ] ) ||
			is_array( $_FILES[ 'file' ][ 'error' ][ 0 ] )
		) {
			return 0;
		}

		switch ( $_FILES[ 'file' ][ 'error' ][ 0 ] ) {
			case UPLOAD_ERR_OK:
				break;
			case UPLOAD_ERR_NO_FILE:
				return 0;
			case UPLOAD_ERR_INI_SIZE:
			case UPLOAD_ERR_FORM_SIZE:
				return 0;
			default:
				return 0;
		}


		$wp_filetype     = wp_check_filetype_and_ext( $_FILES[ 'file' ][ 'tmp_name' ][ 0 ], $_FILES[ 'file' ][ 'name' ][ 0 ] );
		$ext             = empty( $wp_filetype[ 'ext' ] ) ? '' : $wp_filetype[ 'ext' ];
		$type            = empty( $wp_filetype[ 'type' ] ) ? '' : $wp_filetype[ 'type' ];
		$proper_filename = empty( $wp_filetype[ 'proper_filename' ] ) ? '' : $wp_filetype[ 'proper_filename' ];

		// Check to see if wp_check_filetype_and_ext() determined the filename was incorrect
		if ( $proper_filename ) {
			$file[ 'name' ] = $proper_filename;
		}
		if ( ( ! $type || ! $ext ) && ! current_user_can( 'unfiltered_upload' ) ) {
			return array(
				'status' => 0,
				'msg'    => __( 'Sorry, this file type is not permitted for security reasons.', 'provip' )
			);
		}

		if ( ! $upFolder = pvUploadFolders() ) {
			return array(
				'status' => 0,
				'msg'    => __( 'Error in creating upload folders.', 'provip' )
			);
		}

		$targetPath = $upFolder . '/' . md5( uniqid() );

		if ( ! move_uploaded_file( $_FILES[ 'file' ][ 'tmp_name' ][ 0 ], $targetPath ) ) {
			return array(
				'status' => 0,
				'msg'    => 'Failed to move uploaded file.'
			);
		}

		$uploadDir = wp_upload_dir();
		$uploadDir = $uploadDir[ 'basedir' ] . '/provip/';
		$fileIndex = Pro_VIP_Admin_Files::getNewFileIndex( $_POST[ 'postId' ] );
		add_post_meta(
			$_POST[ 'postId' ],
			'_provip_file', array(
				'post_id' => $_POST[ 'postId' ],
				'price'   => 0,
				'id'      => $fileIndex,
				'path'    => ltrim( $targetPath, $uploadDir ),
				'size'    => filesize( $targetPath ),
				'dlName'  => $_FILES[ 'file' ][ 'name' ][ 0 ]
			)
		);

		$files = Pro_VIP_File::find( $_POST[ 'postId' ], $fileIndex );

		return array(
			'status' => 1,
			'file'   => Pro_VIP::loadView(
				'admin/files/file-item',
				array(
					'file' => $files
				),
				true
			)
		);
	}


}
