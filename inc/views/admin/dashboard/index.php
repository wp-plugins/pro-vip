<?php
defined( 'ABSPATH' ) or die;

/**
 * @var $tools
 */
?>
<div class="wrap provip-dashboard">

	<h2><?= __( 'Pro VIP', 'provip' ) ?></h2><br/>

	<div class="left">
		<div class="box">
			<h3><?= __( 'About', 'provip' ) ?></h3>

			<div class="inside">
				<p>
					<?=
					sprintf(
						__(
							'Pro-VIP wordpress advanced VIP downloads plugin. Plugin created by <a href="%s">WP-Pro Team</a>', 'provip'
						),
						Pro_VIP::config( 'creatorUrl' )
					)
					?>
				</p>
			</div>
		</div>

		<div class="box">
			<h3><?= __( 'News', 'provip' ) ?></h3>

			<div class="inside">
				<?php
				if ( empty( $news ) ) {
					echo '<p>' . __( 'Nothing found.', 'provip' ) . '</p>';
				} else {
					echo '<ul>';
					foreach ( $news as $item ) {
						echo '<li><a href="' . $item[ 'link' ] . '">' . $item[ 'title' ] . '</a></li>';
					}
					echo '</ul>';
				}
				?>
			</div>
		</div>
	</div>

	<div class="right">
		<div class="box">
			<h3>
				<?= __( 'Overview', 'provip' ) ?>
			</h3>

			<div class="inside">

				<?php
				Pro_VIP::loadView( 'admin/dashboard/statistic-table' );
				?>


			</div>

		</div>

		<br class="clear"/>

	</div>
</div>


