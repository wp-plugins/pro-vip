<?php
function pvSendJson( $msg = null, $status = 0 ) {
	$args         = func_get_args();
	$json_results = array( 'status' => 0 );
	if ( count( $args ) === 0 ) {
		die( json_encode( $json_results ) );
	}
	if ( count( $args ) === 1 ) {
		if ( is_array( $args[ 0 ] ) ) {
			$json_results = array_merge(
				$json_results,
				$args[ 0 ]
			);
			die( json_encode( $json_results ) );
		}
		if ( is_numeric( $args[ 0 ] ) ) {
			die( json_encode( array( 'status' => (int) $args[ 0 ] ) ) );
		}
	}
	if ( ! empty( $msg ) || is_string( $msg ) ) {
		die( json_encode(
			array(
				'msg'    => $msg,
				'status' => (int) $status
			)
		) );
	}
}