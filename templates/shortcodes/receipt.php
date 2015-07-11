<?php

/**
 * @var $payment Pro_VIP_Payment
 */


if ( empty( $payment ) ) {
	echo __( 'Payment not found.', 'provip' );

	return;
}

$hashids = new Hashids( pvGetOption( 'encryption_key', '' ) );

?>

<table id="provip-payment-receipt">

	<thead>

	<?php
	do_action( 'pro_vip_payment_receipt_header_before', $payment );
	?>


	<tr>
		<th><strong><?php _e( 'Reference Key', 'provip' ); ?>:</strong></th>
		<th style=" text-transform: none;"><?php echo $hashids->encode( $payment->paymentId ) ?></th>
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
	do_action( 'pro_vip_payment_receipt_after', $payment );
	?>


	</tbody>
</table>