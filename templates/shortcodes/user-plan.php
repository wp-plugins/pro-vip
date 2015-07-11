<?php
/**
 * @var $level
 * @var $levelInfo
 */
if( !$level ){
	echo '<div class="pv-notices">';
	echo '<p class="notice">' . sprintf( __( "You're not a %s member. " ), $levelInfo['name'] ) . '</p>';
	echo '</div>';
	return false;
}