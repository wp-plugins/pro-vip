<?php
/**
 * @var $currentUser WP_User
 */
?>
<form id="pv-payment-form" method="post" action="">

	<table>

		<?php do_action( 'provip_custom_payment_form_before' ) ?>

		<tr>
			<td>
				<label for="pv-amount">
					<strong><?= __( 'Amount', 'provip' ) ?></strong>
					<span class="required">*</span>
				</label>
			</td>
			<td>
				<input type="text" name="pv-amount" id="pv-amount" value="<?= !empty( $_REQUEST['pv-amount'] ) && is_string( $_REQUEST[ 'pv-amount' ]) ? $_REQUEST[ 'pv-amount' ] : '' ?>"/>
			</td>
		</tr>

		<tr>
			<td class="title">
				<label for="pv-gateway">
					<strong><?= __( 'Gateway', 'provip' ) ?></strong>
					<span class="required">*</span>
				</label>
			</td>
			<td class="input">
				<?=
				Pro_VIP_Payment_Gateway::gatewaysListDropdown()
				?>
			</td>
		</tr>

		<tr>
			<td class="title">
				<label for="pv-first-name">
					<strong><?= __( 'First Name', 'provip' ) ?></strong>
					<span class="required">*</span>
				</label>
			</td>
			<td class="input">
				<input type="text" name="pv-first-name" id="pv-first-name" value="<?= get_user_meta( $currentUser->ID, 'first_name', true ) ?>"/>
			</td>
		</tr>

		<tr>
			<td class="title">
				<label for="pv-last-name">
					<strong><?= __( 'Last Name', 'provip' ) ?></strong>
				</label>
			</td>
			<td class="input">
				<input type="text" name="pv-last-name" id="pv-last-name" value="<?= get_user_meta( $currentUser->ID, 'last_name', true ) ?>"/>
			</td>
		</tr>


		<tr>
			<td class="title">
				<label for="pv-email-address">
					<strong><?= __( 'Email Address', 'provip' ) ?></strong>
					<span class="required">*</span>
				</label>
			</td>
			<td class="input">
				<input name="pv-email-address" id="pv-email-address" value="<?= $currentUser->user_email ?>" type="email"/>
			</td>
		</tr>

		<?php do_action( 'provip_custom_payment_form_after' ) ?>


	</table>

	<p class="purchase">
		<input type="hidden" name="pv-action" value="do-payment"/>
		<input type="hidden" name="pv-payment-type" value="custom-payment"/>
		<button class="wv-btn primary"><?= __( 'Pay', 'provip' ) ?></button>
	</p>

</form>
