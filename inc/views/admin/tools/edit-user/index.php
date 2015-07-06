<?php
defined( 'ABSPATH' ) or die;
/**
 * @var $tools
 */
?>
<form method="get" action="">

	<p>
		<?= __( 'User ID:', 'provip' ) ?>
		<input type="text" name="user"/>
	</p>

	<p>
		<input type="hidden" name="page" value="<?= @$_REQUEST['page']; ?>"/>
		<input type="hidden" name="tool" value="<?= @$_REQUEST['tool']; ?>"/>
		<input type="hidden" name="post_type" value="<?= @$_REQUEST['post_type']; ?>"/>
		<input type="submit" class="button button-primary" value="<?= __( 'Edit User', 'provip' ) ?>">
	</p>

</form>