<?php
/**
 * @class          Pro_VIP_Currency
 * @version        1.0
 * @package        Pro-VIP
 * @category       Class
 * @author         Pro-WP Team
 */

defined( 'ABSPATH' ) or die; // Prevents direct access

class Pro_VIP_File {


	public static function find( $file, $index = null ) {
		if ( is_numeric( $file ) ) {
			$fileId = $file;
		} else if ( is_a( $file, 'WP_Post' ) && $file->post_type == Pro_VIP_Admin_Files::$postTypeId ) {
			$fileId = $file->ID;
		} else {
			throw new Exception;
		}

		if ( $cache = wp_cache_get( $fileId, 'pro_vip_file' ) ) {
			return $cache;
		}

		$post = get_post( $fileId );
		if ( empty( $post ) || $post->post_type !== Pro_VIP_Admin_Files::$postTypeId ) {
			return false;
		}

		$files = (array) get_post_meta( $post->ID, '_provip_file' );
		if ( $index ) {
			foreach ( $files as $item ) {
				if ( $item[ 'id' ] == $index ) {
					self::$_tmpFile = $item;
				}
			}
		}


		$fileObject = new self( $post, $files );
		wp_cache_add( $fileId, $fileObject, 'pro_vip_file' );

		return $fileObject;
	}

	public
		$ID,
		$singlePurchaseEnabled;

	protected static
		$_tmpFile;

	protected
		$_filePost,
		$_files = array();

	protected function __construct( $filePost, $files ) {
		$this->_filePost             = $filePost;
		$this->_files                = $files;
		$this->ID                    = $filePost->ID;
		$this->singlePurchaseEnabled = get_post_meta( $filePost->ID, '_provip_single_sale', true ) == '1';

	}

	public function getFiles() {
		return $this->_files;
	}

	public function setupFileData( $file ) {
		$this::$_tmpFile = $file;
	}

	public static function downloadUrl( $fileId = null, $fileIndex = null ) {
		if ( ! is_null( $fileId ) && ! is_null( $fileIndex ) ) {
			self::find( $fileId, $fileIndex );
		}
		if ( empty( self::$_tmpFile ) ) {
			return false;
		}

		$link = get_permalink( self::$_tmpFile[ 'post_id' ] );
		$link = add_query_arg( array( 'do-download' => '1', 'index' => self::$_tmpFile[ 'id' ] ), $link );

		return apply_filters( 'pro_vip_file_download_url', $link, self::$_tmpFile );

	}

	public static function fullPath( $fileId = null, $fileIndex = null ) {
		if ( ! is_null( $fileId ) && ! is_null( $fileIndex ) ) {
			self::find( $fileId, $fileIndex );
		}
		if ( empty( self::$_tmpFile ) ) {
			return false;
		}

		$uploadDir = wp_upload_dir();
		$uploadDir = $uploadDir[ 'basedir' ];
		$path      = $uploadDir . '/provip/' . self::$_tmpFile[ 'path' ];

		return apply_filters( 'pro_vip_file_full_path', $path, self::$_tmpFile[ 'path' ] );
	}

	public static function fileIndex() {
		if ( empty( self::$_tmpFile ) ) {
			return false;
		}

		return apply_filters( 'pro_vip_file_index', self::$_tmpFile[ 'id' ] );
	}

	public static function fileId() {
		if ( empty( self::$_tmpFile ) ) {
			return false;
		}

		return apply_filters( 'pro_vip_file_id', self::$_tmpFile[ 'post_id' ] );
	}

	public static function getFileDlName( $fileId = null, $fileIndex = null ) {
		if ( ! is_null( $fileId ) && ! is_null( $fileIndex ) ) {
			self::find( $fileId, $fileIndex );
		}
		if ( empty( self::$_tmpFile ) ) {
			return false;
		}

		return apply_filters( 'pro_vip_file_index', self::$_tmpFile[ 'dlName' ] );
	}


	public static function getFilePrice( $fileId = null, $fileIndex = null ) {
		if ( ! is_null( $fileId ) && ! is_null( $fileIndex ) ) {
			self::find( $fileId, $fileIndex );
		}
		if ( empty( self::$_tmpFile ) ) {
			return false;
		}

		return apply_filters( 'pro_vip_file_index', self::$_tmpFile[ 'price' ] );
	}

	public static function getFile( $fileId = null, $fileIndex = null ) {
		if ( ! is_null( $fileId ) && ! is_null( $fileIndex ) ) {
			self::find( $fileId, $fileIndex );
		}
		if ( empty( self::$_tmpFile ) ) {
			return false;
		}

		return self::$_tmpFile;
	}

	public static function downloadsCount( $fileId = null, $fileIndex = null ) {
		if ( ! is_null( $fileId ) && ! is_null( $fileIndex ) ) {
			self::find( $fileId, $fileIndex );
		}
		if ( empty( self::$_tmpFile ) ) {
			return false;
		}

		$count = absint( get_post_meta( self::$_tmpFile[ 'post_id' ], '_provip_downloads_count_' . self::$_tmpFile[ 'id' ], true ) );

		return apply_filters( 'pro_vip_file_downloads_count', $count, self::$_tmpFile );
	}

	public static function increaseDownloadsCount( $fileId = null, $fileIndex = null ) {
		if ( ! is_null( $fileId ) && ! is_null( $fileIndex ) ) {
			self::find( $fileId, $fileIndex );
		}
		if ( empty( self::$_tmpFile ) ) {
			return false;
		}

		$count = absint( get_post_meta( self::$_tmpFile[ 'post_id' ], '_provip_downloads_count_' . self::$_tmpFile[ 'id' ], true ) );

		return update_post_meta( self::$_tmpFile[ 'post_id' ], '_provip_downloads_count_' . self::$_tmpFile[ 'id' ], ++ $count );
	}

	public static function getDownloadablePlans( $fileId = null, $fileIndex = null ) {
		if ( ! is_null( $fileId ) && ! is_null( $fileIndex ) ) {
			self::find( $fileId, $fileIndex );
		}
		if ( empty( self::$_tmpFile ) ) {
			return false;
		}

		return apply_filters(
			'pro_vip_file_downloadable_plans',
			array_unique(
				array_map( 'absint', (array) get_post_meta( self::$_tmpFile[ 'post_id' ], '_provip_plans', true ) )
			),
			self::$_tmpFile
		);
	}

	public static function singlePurchaseUrl( $fileId = null, $fileIndex = null ) {
		if ( ! is_null( $fileId ) && ! is_null( $fileIndex ) ) {
			self::find( $fileId, $fileIndex );
		}
		if ( empty( self::$_tmpFile ) ) {
			return false;
		}

		$link = trailingslashit( site_url() );
		$link = add_query_arg( array( 'pv-action' => 'single-purchase', 'fileId' => self::$_tmpFile[ 'post_id' ] ), $link );

		return apply_filters( 'pro_vip_file_single_purchase_url', $link, self::$_tmpFile );
	}


	public static function canUserDownloadFile( $user = null, $fileId = null, $fileIndex = null ) {
		if ( ! $user = self::_getUser( $user ) ) {
			return false;
		}

		if ( ! is_null( $fileId ) && ! is_null( $fileIndex ) ) {
			self::find( $fileId, $fileIndex );
		}
		if ( empty( self::$_tmpFile ) ) {
			return false;
		}

		// Administrators always can download the file
		if ( $user->has_cap( 'manage_options' ) ) {
			return apply_filters( 'pro_vip_can_user_download_file', true, $user, self::getFile() );
		}

		// If user role has access
		$intersect = array_intersect( array_flip( pvGetOption( 'default_vip_roles', array() ) ), $user->roles );
		if ( ! empty( $intersect ) ) {
			return apply_filters( 'pro_vip_can_user_download_file', true, $user, self::getFile() );
		}


		// If user has purchased this file
		if ( in_array( self::$_tmpFile[ 'file_id' ], self::getUserPurchasesIds( $user->data->email_address ) ) ) {
			return apply_filters( 'pro_vip_can_user_download_file', true, $user, self::getFile() );
		}


		// And finally if user is in the plan
		if ( Pro_VIP_Member::isVip( $user->ID, self::getDownloadablePlans() ) ) {
			return apply_filters( 'pro_vip_can_user_download_file', true, $user, self::getFile() );
		}

		return false;
	}


	public static function getUserPurchases( $email ) {
		global $wpdb;
		$table = $wpdb->prefix . 'vip_purchases';


		return (array) $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table WHERE user_email = %s", $email ), ARRAY_A );
	}

	public static function getUserPurchasesIds( $email = null ) {
		global $wpdb;
		$table = $wpdb->prefix . 'vip_purchases';

		return $wpdb->get_col( $wpdb->prepare( "SELECT file_ID FROM $table WHERE user_email = %s", $email ), ARRAY_A );
	}

	public static function registerFilePurchase( $email, $file_id = null, $file_index = null, $data = array() ) {
		if ( ! is_null( $file_id ) && ! is_null( $file_index ) ) {
			self::find( $file_id, $file_index );
		}
		if ( empty( self::$_tmpFile ) ) {
			return false;
		}

		global $wpdb;
		$table = $wpdb->prefix . 'vip_purchases';

		$data = array_merge(
			array(
				'purchase_date' => current_time( 'mysql' ),
				'ip'            => pvGetIP()
			),
			$data
		);

		$insert = $wpdb->insert(
			$table,
			array(
				'user_email'    => $email,
				'purchase_date' => $data[ 'purchase_date' ],
				'file_ID'       => self::fileId(),
				'file_index'    => self::fileIndex(),
				'ip'            => $data[ 'ip' ]
			)
		);

		if ( $insert ) {
			return $wpdb->insert_id;
		}

		return false;
	}


	protected static function _getUser( $user = null ) {
		if ( is_numeric( $user ) ) {
			$userId = $user;
		} else if ( is_null( $user ) && did_action( 'plugins_loaded' ) ) {
			$userId = get_current_user_id();
		} else {
			return false;
		}

		return get_userdata( $userId );
	}


	public static function updateFileDlName( $dlName, $fileId, $fileIndex ) {
		$meta     = (array) get_post_meta( $fileId, '_provip_file' );
		$toUpdate = array();
		$prevVal  = null;
		foreach ( $meta as $item ) {
			if ( $item[ 'id' ] == $fileIndex ) {
				$toUpdate             = $item;
				$prevVal              = $item;
				$toUpdate[ 'dlName' ] = $dlName;
			}
		}
		if ( empty( $toUpdate ) ) {
			return false;
		}

		return update_post_meta( $fileId, '_provip_file', $toUpdate, $prevVal );
	}

	public static function updateFilePrice( $dlName, $fileId, $fileIndex ) {
		$meta     = (array) get_post_meta( $fileId, '_provip_file' );
		$toUpdate = array();
		$prevVal  = null;
		foreach ( $meta as $item ) {
			if ( $item[ 'id' ] == $fileIndex ) {
				$toUpdate            = $item;
				$prevVal             = $item;
				$toUpdate[ 'price' ] = $dlName;
			}
		}
		if ( empty( $toUpdate ) ) {
			return false;
		}

		return update_post_meta( $fileId, '_provip_file', $toUpdate, $prevVal );
	}

	public static function deleteFile( $fileId = null, $fileIndex = null ) {

		$file = self::getFile( $fileId, $fileIndex );

		if ( ! $file ) {
			return false;
		}

		if ( ! delete_post_meta( $file[ 'post_id' ], '_provip_file', $file ) ) {
			return false;
		}

		return @unlink( self::fullPath() );

	}
}

