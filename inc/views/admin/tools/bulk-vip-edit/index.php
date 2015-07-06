<?php
defined( 'ABSPATH' ) or die;
/**
 * @var $tools
 */
?>
<div class="provip-bulk-edit">


	<form method="post" action="<?= pvCurrentPageUrl( '/', false ) ?>">

		<?php do_action( 'pro_vip_admin_bulk_vip_edit_form_before' ); ?>


		<p>
			<label>
				<?= __( 'Action', 'provip' ) ?>:<br/>
				<select name="be[action]">
					<option value="increase"><?= __( 'Increase', 'provip' ) ?></option>
					<option value="decrease"><?= __( 'Decrease', 'provip' ) ?></option>
				</select>
			</label>
		</p>

		<p>
			<label>
				<?= __( 'Time', 'provip' ) ?>:<br/>
				<input type="number" name="be[time]"/>
				<select name="be[time-t]">
					<option value="sec"><?= __( 'Seconds', 'provip' ) ?></option>
					<option value="min"><?= __( 'Minutes', 'provip' ) ?></option>
					<option value="hour"><?= __( 'Hours', 'provip' ) ?></option>
					<option value="day"><?= __( 'Days', 'provip' ) ?></option>
					<option value="week"><?= __( 'Weeks', 'provip' ) ?></option>
					<option value="year"><?= __( 'Years', 'provip' ) ?></option>
				</select>
			</label>
		</p>

		<p>
			<label>
				<?= __( 'Level', 'provip' ) ?>:<br/>
				<select name="be[level][]" style="min-width: 120px;" multiple>
					<?php
					foreach ( pvGetLevels() as $levelId => $level ) {
						echo '<option value="' . $levelId . '">' . $level . '</option>';
					}
					?>
				</select>
			</label>
		</p>


		<div class="advanced">

			<?php do_action( 'pro_vip_admin_bulk_vip_edit_form_advanced_before' ); ?>


			<p>
				<label>
					<?= __( 'Users who registered', 'provip' ) ?>
					<select name="be[register-date-operator]">
						<option value="after"><?= __( 'After', 'provip' ) ?></option>
						<option value="before"><?= __( 'Before', 'provip' ) ?></option>
					</select>
					<input type="text" name="be[register-date]" class="date-picker" placeholder="<?= __( 'Pick a date', 'provip' ) ?>"/>
				</label>
			</p>

			<p>
				<label>
					<?= __( 'Users who purchased account', 'provip' ) ?>
					<select name="be[first-purchase-operator]">
						<option value="after"><?= __( 'After', 'provip' ) ?></option>
						<option value="before"><?= __( 'Before', 'provip' ) ?></option>
					</select>
					<input type="text" name="be[first-purchase]" class="date-picker" placeholder="<?= __( 'Pick a date', 'provip' ) ?>"/>
				</label>
			</p>


			<a href="#" class="button button-small"><?= __( 'More Filters', 'provip' ) ?></a>
			<br/><br/>

			<?php do_action( 'pro_vip_admin_bulk_vip_edit_form_advanced_after' ); ?>


		</div>

		<input type="hidden" name="action" value="provip_bulk_vip_edit"/>
		<input type="hidden" name="nonce" value="<?= wp_create_nonce( 'provip_bulk_vip_edit' ); ?>"/>

		<br/><br/><input type="submit" class="button-primary button-large" value="<?= __( 'Edit', 'provip' ) ?>"/>
	</form>

	<?php do_action( 'pro_vip_admin_bulk_vip_edit_form_after' ); ?>


</div>