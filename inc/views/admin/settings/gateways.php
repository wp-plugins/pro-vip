<?php
/**
 * @var $field   PV_Framework_Multicheckbox_Field_Type
 * @var $gateway Pro_VIP_Payment_Gateway
 */
$val  = $field->get_value();
$name = $field->settings[ 'inputName' ];

$defaultGateway = ! empty( $val[ 'default-gateway' ] ) && is_string( $val[ 'default-gateway' ] ) ? $val[ 'default-gateway' ] : '';
?>
<div class="provip-plans-field">

	<table class="gateways-table widefat" cellspacing="0">
		<thead>
		<tr>
			<th class="default"><?= __( 'Default', 'provip' ) ?></th>
			<th class="name"><?= __( 'Gateway', 'provip' ) ?></th>
			<th class="id"><?= __( 'Gateway ID', 'provip' ) ?></th>
			<th class="status"><?= __( 'Enabled', 'provip' ) ?></th>
			<th class="settings"></th>
		</tr>
		</thead>
		<tbody>
		<?php
		if ( ! empty( $val[ 'order' ] ) ) {
			$gw          = array_keys( $val[ 'order' ] );
			$allGateways = Pro_VIP_Payment_Gateway::getAllGatewaysList();

			$diff = array_diff( array_keys( $allGateways ), $gw );

			foreach ( $diff as $gateway ) {
				$gw[ ] = $gateway;
			}
			if ( isset( $gateway ) ) {
				unset( $gateway );
			}
		} else {
			$gw = array();
			foreach ( Pro_VIP_Payment_Gateway::getAllGateways() as $gateway ) {
				$gw[ ] = $gateway->id;
			}
			unset( $gateway );
		}
		foreach ( $gw as $gatewayId ):
			$gateway = Pro_VIP_Payment_Gateway::getGateway( $gatewayId, false );
			if ( empty( $gateway ) ) {
				continue;
			}
			?>
			<tr class="gateway" data-id="<?= $gateway->id ?>">
				<td width="1%" class="default">
					<input type="hidden" name="<?= $name ?>[order][<?= $gateway->id ?>]"/>
					<input type="radio" name="<?= $name ?>[default-gateway]" value="<?= $gateway->id ?>" <?php checked( $defaultGateway, $gateway->id ) ?>>
				</td>
				<td class="name">
					<?= $gateway->adminLabel ?>
				</td>
				<td class="id">
					<?= $gateway->id ?>
				</td>
				<td class="status">
					<input type="checkbox" name="<?= $name ?>[<?= $gateway->id ?>][enabled]" value="1" <?= ! empty( $val[ $gateway->id ][ 'enabled' ] ) && $val[ $gateway->id ][ 'enabled' ] == 1 ? 'checked' : '' ?>/>
				</td>
				<td class="settings">
					<a class="button show-settings" href="#"><?= __( 'Settings', 'provip' ) ?></a>
				</td>
			</tr>
		<?php endforeach ?>
		</tbody>
	</table>


</div>