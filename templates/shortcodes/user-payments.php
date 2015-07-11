<?php
/**
 * @var $query WP_Query
 */

if ( ! $query->have_posts() ) {
	echo __( 'No payment', 'provip' );

	return;
}

$hashids = new Hashids( pvGetOption( 'encryption_key', '' ) );
?>


<table class="table pv-user-payments">

	<thead>
	<tr>
		<th class="id" style="width: 19%"><?php _e( 'Reference Key', 'provip' ) ?></th>
		<th class="payment-status"><?php _e( 'Payment Status', 'provip' ); ?></th>
		<th class="gateway"><?php _e( 'Gateway', 'provip' ); ?></th>
		<th class="amount"><?php _e( 'Amount', 'provip' ); ?></th>
		<th class="date"><?php _e( 'Date', 'provip' ); ?></th>
	</tr>
	</thead>

	<tbody>
	<?php
	while ( $query->have_posts() ) {
		$query->the_post();
		$payment = new Pro_VIP_Payment( get_the_ID() );
		?>
		<tr>
			<td class="id"><code><?php echo $hashids->encode( $payment->paymentId ) ?></code></td>
			<td class="payment-status <?php echo $payment::status( $payment->status, false ); ?>"><?php echo $payment::status( $payment->status ); ?></td>
			<td class="gateway"><?= $payment->getGateway()->frontendLabel ?></td>
			<td class="amount"><?= Pro_VIP_Currency::priceHTML( $payment->price ) ?></td>
			<td class="date"><?= date_i18n( get_option( 'date_format' ), strtotime( $payment->date ) ) ?></td>
		</tr>
	<?php
	}
	?>
	</tbody>

	<tfoot>
	<tr>
		<th class="id" style="width: 19%"><?php _e( 'Reference Key', 'provip' ) ?></th>
		<th class="payment-status"><?php _e( 'Payment Status', 'provip' ); ?></th>
		<th class="gateway"><?php _e( 'Gateway', 'provip' ); ?></th>
		<th class="amount"><?php _e( 'Amount', 'provip' ); ?></th>
		<th class="date"><?php _e( 'Date', 'provip' ); ?></th>
	</tr>
	</tfoot>

</table>
