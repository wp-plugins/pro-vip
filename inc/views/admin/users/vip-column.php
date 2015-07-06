<?php
/**
 * @var $accounts
 * @var $user_ID
 */
?>
<div class="pv-vip-column">

	<?php
	if ( empty( $accounts ) ) { ?>
		<p style="margin-bottom: 0;"><?= __( 'No VIP Plan', 'provip' ) ?></p>
	<?php
	} else {

		echo '<strong>VIP Plans:</strong><div class="plans">';

		foreach ( $accounts as $account ) {

			$plan = pvGetLevel( $account->vip_level );
			if ( empty( $plan ) ) {
				continue;
			} ?>

			<strong class="plan pv-tooltip"><?= $plan[ 'name' ] ?></strong>
			<div class="tooltip-content">
				<span>
					<?= __( 'Started:', 'provip' ) ?>
					<strong>
						<?= date_i18n( get_option( 'date_format' ) . ' - ' . get_option( 'time_format' ), strtotime( $account->start_date ) ) ?>
						(<?= human_time_diff( strtotime( $account->start_date ), time() ) ?> <?= __( 'ago', 'provip' ) ?>						)
					</strong>
				</span><br/><br/>
				<span>
					<?= __( 'Last Update:', 'provip' ) ?>
					<strong>
						<?= ! empty( $account->update_date ) ? ( date_i18n( get_option( 'date_format' ) . ' - ' . get_option( 'time_format' ), strtotime( $account->update_date ) ) ) : '-' ?>
						<?= ! empty( $account->update_date ) ? ( '(' . human_time_diff( strtotime( $account->update_date ), time() ) . ' ' . __( 'ago', 'provip' ) . ')' ) : '' ?>
					</strong>
				</span><br/><br/>
				<span>
					<?= __( 'Expiration:', 'provip' ) ?>
					<strong>
						<?php
						$timestamp = strtotime( $account->expiration_date );
						$past      = time() > $timestamp;
						if ( $past ) {
							?>
							Expired
						<?php } else {
							echo date_i18n( get_option( 'date_format' ) . ' - ' . get_option( 'time_format' ), $timestamp );
							echo ' (' . human_time_diff( strtotime( $account->expiration_date ), time() ) . ' ' . __( 'remained', 'provip' ) . ')';
						} ?>
					</strong>
				</span>
			</div>

		<?php
		}

		echo '</div>';

	} ?>

	<a href="<?= Pro_VIP_Admin_Tools_Edit_User::getEditLink( $user_ID ) ?>"><?= __( 'Edit Accounts', 'provip' ) ?></a>


</div>

<?php
return
?>
