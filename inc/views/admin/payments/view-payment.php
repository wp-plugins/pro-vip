<?php
/**
 * @var $payment Pro_VIP_Payment
 */
$hashids = new Hashids( pvGetOption( 'encryption_key', '' ) );

?>
<h2><?= __( 'Payment', 'provip' ) ?> <a class="button" href="<?= esc_url( remove_query_arg( array(
		'pv-action',
		'payment'
	) ) ) ?>"><?= __( 'Back', 'provip' ) ?></a></h2>
<br/>

<table id="provip-payment-receipt" class="widefat">

	<thead>

	<?php
	do_action( 'pro_vip_payment_receipt_header_before', $payment );
	?>


	<tr>
		<th><strong><?php _e( 'Reference Key', 'provip' ); ?>:</strong></th>
		<th style=" text-transform: none;"><code><?php echo $hashids->encode( $payment->paymentId ) ?></code></th>
	</tr>

	<?php
	do_action( 'pro_vip_payment_receipt_header_after', $payment );
	?>


	</thead>

	<tbody>


	<?php
	do_action( 'pro_vip_payment_receipt_before', $payment );
	?>



	<tr>
		<td class="payment-status">
			<strong><?php _e( 'Payment Status', 'provip' ); ?>:</strong>
		</td>
		<td class="payment-status <?php echo $payment::status( $payment->status, false ); ?>"><?php echo $payment::status( $payment->status ); ?></td>
	</tr>

	<tr>
		<td class="payment-gateway">
			<strong><?php _e( 'Gateway', 'provip' ); ?>:</strong>
		</td>
		<td class="payment-gateway"><?= $payment->getGateway()->frontendLabel ?></td>
	</tr>

	<tr>
		<td class="payment-amount">
			<strong><?php _e( 'Amount', 'provip' ); ?>:</strong>
		</td>
		<td class="payment-amount"><?= Pro_VIP_Currency::priceHTML( $payment->price ) ?></td>
	</tr>

	<tr>
		<td class="payment-date">
			<strong><?php _e( 'Date', 'provip' ); ?>:</strong>
		</td>
		<td class="payment-date"><?= date_i18n( get_option( 'date_format' ), strtotime( $payment->date ) ) ?></td>
	</tr>


	<?php
	if ( ! empty( $payment->key ) ) :
		?>
		<tr>
			<td class="payment-key">
				<strong><?php _e( 'Payment Key', 'provip' ); ?>:</strong>
			</td>
			<td class=""><?= $payment->key ?></td>
		</tr>

	<?php endif ?>

	<?php
	if ( ! empty( $payment->type ) ) :
		?>
		<tr>
			<td class="payment-type">
				<strong><?php _e( 'Payment Type', 'provip' ); ?>:</strong>
			</td>
			<td class=""><?= $payment->type ?></td>
		</tr>

	<?php endif ?>


	<tr>
		<td class="custom">
			<strong><?php _e( 'Custom Values', 'provip' ); ?>:</strong>
		</td>
		<td class=""><?php $payment::dumpCustom( $payment->custom ) ?></td>
	</tr>


	<?php
	do_action( 'pro_vip_payment_receipt_after', $payment );
	?>


	</tbody>
</table>