<?php

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * @param      $option
 * @param bool $default
 *
 * @return mixed
 */
function pvGetOption( $option, $default = false ) {
	$options = get_option( 'wpVIP', array() );

	return isset( $options[ $option ] ) ? $options[ $option ] : $default;
}

function pvGetPlans() {
	$option = pvGetOption( 'plans', array() );
	$plans  = array();
	foreach ( $option as $i => $plan ) {
		$plans[ $i ] = $plan[ 'name' ];
	}

	return $plans;
}

function pvGetPlan( $planId ) {
	$option = pvGetOption( 'plans', array() );
	foreach ( $option as $id => $plan ) {
		if ( $planId == $id ) {
			return $plan;
		}
	}

	return false;
}


function pvGetLevels() {
	$option = pvGetOption( 'vip_levels', array() );
	$levels = array();
	if ( empty( $option ) ) {
		return array();
	}
	foreach ( $option as $index => $plan ) {
		$levels[ $index ] = $plan[ 'name' ];
	}

	return $levels;
}


function pvGetLevel( $level ) {
	$levels = pvGetLevels();
	$output = false;
	foreach ( $levels as $k => $v ) {
		if ( $k == $level ) {
			$output = array(
				'id'   => $k,
				'name' => $v
			);
		}
	}

	return $output;
}


function pvGetFileDownloadsCount( $postId ) {
	$post = get_post( $postId );
	if ( empty( $post ) ) {
		return - 1;
	}
	$meta = get_post_meta( $post->ID, '_provip_downloads_count', true );

	return absint( $meta );
}

function pvIncreaseFileDownloadsCount( $postId ) {
	$post = get_post( $postId );
	if ( empty( $post ) ) {
		return - 1;
	}

	return update_post_meta( $post->ID, '_provip_downloads_count', pvGetFileDownloadsCount( $post->ID ) + 1 );
}


function pvUploadFolders() {

	$uploadDir = wp_upload_dir();
	$uploadDir = $uploadDir[ 'basedir' ];


	$dirParts = array( $uploadDir );


	$dirParts[ ] = 'provip';

	$time = gmdate( 'Y-m-d', ( time() + ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS ) ) );
	$time = explode( '-', $time );

	$dirParts[ ] = $time[ 0 ];
	$dirParts[ ] = $time[ 1 ];
	$dirParts[ ] = $time[ 2 ];

	$dirParts = implode( '/', $dirParts );

	$makeDir = wp_mkdir_p( $dirParts );


	@file_put_contents( $uploadDir . '/provip/.htaccess', 'Order deny,allow
Deny from all' );

	return $makeDir ? $dirParts : false;

}


function pvGetIP() {

	$ip = '127.0.0.1';

	if ( ! empty( $_SERVER[ 'HTTP_CLIENT_IP' ] ) ) {
		//check ip from share internet
		$ip = $_SERVER[ 'HTTP_CLIENT_IP' ];
	} elseif ( ! empty( $_SERVER[ 'HTTP_X_FORWARDED_FOR' ] ) ) {
		//to check ip is pass from proxy
		$ip = $_SERVER[ 'HTTP_X_FORWARDED_FOR' ];
	} elseif ( ! empty( $_SERVER[ 'REMOTE_ADDR' ] ) ) {
		$ip = $_SERVER[ 'REMOTE_ADDR' ];
	}

	return apply_filters( 'pro_vip_get_ip', $ip );
}


function pvCurrentPageUrl( $path = '', $withWpObject = true ) {
	global $wp;
	if ( $withWpObject && is_object( $wp ) ) {
		return home_url( add_query_arg( array(), $wp->request ), $path );
	} else {
		$pageURL = 'http';
		if ( is_ssl() ) {
			$pageURL .= "s";
		}
		$pageURL .= "://";
		if ( $_SERVER[ "SERVER_PORT" ] != "80" ) {
			$pageURL .= $_SERVER[ "SERVER_NAME" ] . ":" . $_SERVER[ "SERVER_PORT" ] . $_SERVER[ "REQUEST_URI" ];
		} else {
			$pageURL .= $_SERVER[ "SERVER_NAME" ] . $_SERVER[ "REQUEST_URI" ];
		}

		return $pageURL;
	}
}


function isPV() {
	return Pro_VIP::get( '_is_pro_vip' );
}


function pvSortArray( $arr1, $arr2, $searchKey = null ) {

	$ordered = array();
	foreach ( (array) $arr2 as $key ) {
		if ( ! empty( $searchKey ) ) {
			foreach ( $arr1 as $k => $v ) {
				if ( ! empty( $v[ $searchKey ] ) && $v[ $searchKey ] == $key ) {
					$ordered[ $k ] = $v;
					unset( $arr1[ $k ] );
				}
			}
		} else {
			if ( array_key_exists( $key, $arr1 ) ) {
				$ordered[ $key ] = $arr1[ $key ];
				unset( $arr1[ $key ] );
			}
		}
	}

	return $ordered + $arr1;
}

function pvGetTotalSells( $def = 0 ) {
	return get_option( 'pv_total_sells', 0 );
}

function pvUpdateTotalSells( $increase ) {
	$increase = (int) $increase;
	$sells    = pvGetTotalSells() + $increase;

	return update_option( 'pv_total_sells', $sells );
}

function pvLoginMsg() {
	return do_shortcode( pvGetOption( 'login_msg', __( 'You have to login first.<br/>[pv-login-form]', 'provip' ) ) );
}


function pvDbVersion() {
	return ! empty( pv()->dbVersion ) ? pv()->dbVersion : '10';
}