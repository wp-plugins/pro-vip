<?php

function getProVipApiUrl( $path){
	$version = defined( 'PV_API_REQUEST_VERSION' ) ? PV_API_REQUEST_VERSION : PV_API::VERSION;

	$url = get_home_url( null, "pv-api/v{$version}/", is_ssl() ? 'https' : 'http' );

	if ( ! empty( $path ) && is_string( $path ) ) {
		$url .= ltrim( $path, '/' );
	}

	return $url;
}