<?php

function pvVipGetUser( $user = null ) {
	if ( empty( $user ) && did_action( 'plugins_loaded' ) ) {
		return get_current_user_id();
	}

	if ( $user instanceof WP_User ) {
		return $user->ID;
	}

	if ( is_numeric( $user ) ) {
		return $user;
	}


	return 0;
}
