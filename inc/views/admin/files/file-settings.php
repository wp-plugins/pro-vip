<?php
/**
 * @var $formBuilder PV_Framework_Form_Builder
 * @var $hide
 */
$singleSaleField = $formBuilder->getField( '_provip_single_sale' );

?>
<div class="settings" style="<?= $hide ? 'display:none;' : '' ?>">
	<br/>

	<div class="general-sale-settings">
		<h3><?= __( 'General Settings', 'provip' ) ?></h3>
		<?php
		$field = $formBuilder->getField( '_provip_plans' );
		?>
		<p>
			<label><?= $singleSaleField->display(); ?> <?= __( 'Enable Single Sale', 'provip' ) ?></label>
		</p>

		<p>
			<label>
				<?= $field->settings[ 'label' ] ?><br/>
				<?= $field->display() ?>
			</label>
		</p>
	</div>


	<div class="single-sale-settings" style="<?= $singleSaleField->get_value() == 1 ? 'display: block;' : '' ?>">
		<h3><?= __( 'Single Sale Settings', 'provip' ) ?></h3>
		<?php
		$field = $formBuilder->getField( '_provip_file_price' );
		?>
		<p>
			<label>
				<?= $field->settings[ 'label' ] ?><br/>
				<?= $field->display() ?>
			</label>
		</p>


	</div>


</div>
