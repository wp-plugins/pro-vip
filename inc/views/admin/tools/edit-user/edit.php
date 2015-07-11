<?php
defined( 'ABSPATH' ) or die;
/**
 * @var $user WP_User
 * @var $accounts
 */
?>
<div>

	<h3><?= sprintf( __( 'Edit %s', 'provip' ), $user->data->display_name ) ?></h3>

	<form action="" method="post" class="update-form">

		<p>
			<label for="update-action">Action</label>
			<select name="update-action" id="update-action">
				<option value="increase">Increase</option>
				<option value="decrease">Decrease</option>
			</select>
		</p>

		<p>
			<label for="vip-level">Level</label>
			<select name="vip-level" id="vip-level">
				<?php foreach ( pvGetLevels() as $levelId => $levelName ) {
					echo '<option value="' . $levelId . '">' . $levelName . '</option>';
				} ?>
			</select>
		</p>

		<p>
			<label for="vip-time">Time</label>
			<input type="number" id="vip-time" name="vip-time"/>
			<select name="time-type" id="time-type">
				<?php foreach (
					array(
						'min'  => __( 'Minutes', 'provip' ),
						'hour' => __( 'Hours', 'provip' ),
						'day'  => __( 'Days', 'provip' ),
						'week' => __( 'Weeks', 'provip' ),
						'year' => __( 'Years', 'provip' )
					) as $id => $name
				) {
					echo '<option value="' . $id . '">' . $name . '</option>';
				} ?>
			</select>
		</p>

		<p>
			<input type="hidden" name="wv-action" value="admin.editUserVip"/>
			<input type="hidden" name="user-id" value="<?= $user->ID ?>"/>
			<?php wp_nonce_field( 'pro_vip_user_edit' ) ?>
			<input type="submit" class="button primary-button"/>
		</p>

	</form>

	<hr/>

	<div class="accounts">

		<?php foreach ( $accounts as $account ) :

			$plan = pvGetLevel( $account->vip_level );
			if ( empty( $plan ) ) {
				continue;
			}
			?>

			<div>
				<strong><?= $plan[ 'name' ] ?></strong>:<br/>
			<span>
				<?= __( 'Started:', 'provip' ) ?>
				<strong>
					<?= date_i18n( get_option( 'date_format' ) . ' - ' . get_option( 'time_format' ), strtotime( $account->start_date ) ) ?>
					(<?= human_time_diff( strtotime( $account->start_date ), time() ) ?> <?= __( 'ago', 'provip' ) ?>
					)
				</strong>
			</span><br/>
			<span>
				<?= __( 'Last Update:', 'provip' ) ?>
				<strong>
					<?= ! empty( $account->update_date ) ? ( date_i18n( get_option( 'date_format' ) . ' - ' . get_option( 'time_format' ), strtotime( $account->update_date ) ) ) : '-' ?>
					<?= ! empty( $account->update_date ) ? ( '(' . human_time_diff( strtotime( $account->update_date ), time() ) . ' ' . __( 'ago', 'provip' ) . ')' ) : '' ?>
				</strong>
			</span><br/>
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
						echo '(' . human_time_diff( strtotime( $account->expiration_date ), time() ) . ' ' . __( 'remained', 'provip' ) . ')';
					} ?>
				</strong>
			</span><br/>
			</div>



		<?php endforeach ?>

	</div>
</div>