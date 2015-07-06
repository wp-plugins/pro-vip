<?php
defined( 'ABSPATH' ) or die;
/**
 * @var $tools
 */
?>
<div class="wrap provip-tools">
	<h1><?= __( 'Tools', 'provip' ) ?></h1>
	<ul>
		<?php
		foreach ( $tools as $toolId => $tool ) {
			?>
			<li><a href="<?= add_query_arg( array( 'tool' => $toolId ) ) ?>"><?= $tool[ 'label' ] ?></a></li>
		<?php
		}
		?>
	</ul>
</div>
