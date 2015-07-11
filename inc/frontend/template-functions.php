<?php
defined( 'ABSPATH' ) or die; // Prevents direct access

function pvAddNotice( $msg, $type = 'error' ) {
	$notices = Pro_VIP::get( '_notices', '', array() );
	if ( $type !== 'success' && $type !== 'error' ) {
		$type = 'error';
	}
	$notices[ $type ][ ] = $msg;
	Pro_VIP::set( '_notices', $notices );
}

function pvPrintNotices() {
	$notices = Pro_VIP::get( '_notices', '', array() );

	if ( empty( $notices ) ) {
		return false;
	}

	echo '<div class="pv-notices">';

	foreach ( $notices as $notice_type => $notices ) {
		foreach ( $notices as $msg ) {
			Pro_VIP_Template::load( 'notice', $notice_type, array( 'message' => $msg ) );
		}
	}

	echo '</div>';

}
