<?php
$currentUser = wp_get_current_user();
?>
<form method="post" action="" class="pv-plans-form">

	<?php do_action( 'pro_vip_plans_form_before_table' ); ?>


	<table>

		<?php do_action( 'pro_vip_plans_form_table_begin' ); ?>

		<tr>
			<td class="title">
				<label for="pv-plan">
					<?= __( 'Plan', 'provip' ) ?>
				</label>
			</td>
			<td class="input">
				<select id="pv-plan" name="pv-plan">
					<?php foreach ( pvGetPlans() as $planId => $plan ) : ?>
						<option value="<?= $planId ?>"><?= $plan ?></option>
					<?php endforeach ?>
				</select>
			</td>
		</tr>

		<tr>
			<td class="title">
				<label for="pv-plan-level">
					<?= __( 'Plan Type', 'provip' ) ?>
				</label>
			</td>
			<td class="input">
				<select id="pv-plan-level" name="pv-plan-level">
					<?php foreach ( pvGetLevels() as $levelId => $level ) : ?>
						<option value="<?= $levelId ?>" <?php selected( (int) pvGetOption( 'default_vip_level', 0 ), $levelId ) ?>><?= $level ?></option>
					<?php endforeach ?>
				</select>
			</td>
		</tr>

		<tr>
			<td class="title">
				<label for="pv-gateway">
					<?= __( 'Gateway', 'provip' ) ?>
				</label>
			</td>
			<td class="input">
				<?= Pro_VIP_Payment_Gateway::gatewaysListDropdown(); ?>
			</td>
		</tr>


		<tr>
			<td class="title">
				<label for="pv-email-address">
					<?= __( 'Email Address', 'provip' ) ?>
					<span class="required"> *</span>
				</label>
			</td>
			<td class="input">
				<input name="pv-email-address" id="pv-email-address" value="<?= isset( $_POST[ 'pv-email-address' ] ) ? $_POST[ 'pv-email-address' ] : $currentUser->user_email ?>" type="email"/>
			</td>
		</tr>


		<tr>
			<td>
				<label for="pv-first-name">
					<?= __( 'First Name', 'provip' ) ?>
					<span class="required"> *</span>
				</label>
			</td>
			<td class="input">
				<input id="pv-first-name" name="pv-first-name" value="<?= isset( $_POST[ 'pv-first-name' ] ) ? $_POST[ 'pv-first-name' ] : get_user_meta( $currentUser->ID, 'first_name', true ) ?>" type="text"/>
			</td>
		</tr>

		<tr>
			<td class="title">
				<label for="pv-last-name">
					<?= __( 'Last Name', 'provip' ) ?>
				</label>
			</td>
			<td class="input">
				<input id="pv-last-name" name="pv-last-name" value="<?= isset( $_POST[ 'pv-last-name' ] ) ? $_POST[ 'pv-last-name' ] : get_user_meta( $currentUser->ID, 'last_name', true ) ?>" type="text"/>
			</td>
		</tr>

		<?php do_action( 'pro_vip_plans_form_table_end' ); ?>


		<tr>
			<td class="title">
				<strong><?= __( 'Price:', 'provip' ) ?></strong>
			</td>
			<td class="input">
				<span class="cost">0</span>
			</td>
		</tr>



		<tr>
			<td colspan="2">
				<input type="submit" class="button submit" value="<?= __( 'Purchase', 'provip' ) ?>"/>
				<input type="hidden" name="action" value="pvPurchasePlan"/>
			</td>
		</tr>



	</table>

	<?php do_action( 'pro_vip_plans_form_after_table' ); ?>

	<div class="preloader"></div>
</form>