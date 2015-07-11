<table class="pv-statistic-table widefat">
	<tr>
		<td colspan="2">
			<strong><?= __( 'Total Sells', 'provip' ) ?></strong>
			<span><?= Pro_VIP_Currency::priceHTML( pvGetTotalSells() ) ?></span>
		</td>
		<td colspan="1">
			<strong><?= __( 'Total VIP Accounts', 'provip' ) ?></strong>
			<span><?= Pro_VIP_Statistic::getTotalVipMembers( null, false ) ?></span>
		</td>
		<td colspan="1">
			<strong><?= __( 'Total VIP Accounts', 'provip' ) ?></strong>
			<span><?= Pro_VIP_Statistic::getTotalVipMembers( null, false ) ?></span>
		</td>
	</tr>
	<tr>
		<td colspan="4">
			<strong><?= __( 'VIP Levels Users', 'provip' ) ?></strong>
			<?php
			$levels = Pro_VIP_Statistic::getLevelsUsers();

			foreach ( $levels as $level => $count ) {
				printf( '<span>%s: %d</span>', $level, $count );
			}
			?>
		</td>
	</tr>
	<tr class="top-border">
		<td colspan="2" style="width: 50%;">
			<strong><?= __( 'Total Purchases', 'provip' ) ?></strong>
								<span>
									<?=
									Pro_VIP_Statistic::getFilePurchases();
									?>
								</span>
		</td>
		<td colspan="1" style="width: 25%;">
			<strong><?= __( 'Last 24 hours purchases', 'provip' ) ?></strong>
								<span>
									<?=
									Pro_VIP_Statistic::getFilePurchases( DAY_IN_SECONDS );
									?>
								</span>
		</td>
		<td colspan="1" style="width: 25%;">
			<strong><?= __( 'Last week purchases', 'provip' ) ?></strong>
								<span>
									<?=
									Pro_VIP_Statistic::getFilePurchases( WEEK_IN_SECONDS );
									?>
								</span>
		</td>
	</tr>
	<tr class="top-border">
		<td style="width: 50%;" colspan="2">
			<strong><?= __( 'Most Purchased File', 'provip' ) ?></strong>
								<span>
									<?php
									$file = Pro_VIP_Statistic::getMostPurchasedFile();
									if ( empty( $file ) ) {
										_e( 'No purchase yet.', 'provip' );
									} else {
										printf( '<a href="%s">%s</a> (%d)', esc_attr( get_the_permalink( $file->ID ) ), esc_html( get_the_title( $file->ID ) ), $file->purchaseCount );
									}
									?>
								</span>
		</td>
		<td style="width: 50%;" colspan="2">
			<strong><?= __( 'Most Recent Purchase', 'provip' ) ?></strong>
								<span>
									<?php
									$file = Pro_VIP_Statistic::getMostRecentPurchases( 1 );
									if ( empty( $file ) ) {
										_e( 'No purchase yet.', 'provip' );
									} else {
										$file = $file[ 0 ];
										printf( '<a href="%s">%s</a>', esc_attr( get_the_permalink( $file->file_ID ) ), esc_html( get_the_title( $file->file_ID ) ) );
									}

									?>
								</span>
		</td>
	</tr>
</table>