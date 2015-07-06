<?php
/**
 * @var $field PV_Framework_Multicheckbox_Field_Type
 */
$value = $field->get_value() != '' ? (array) $field->get_value() : array();
$name  = $field->settings[ 'inputName' ];
?>
<div class="provip-plans-field wv-repeater" data-id="plans">

	<h3><?= __( 'Plans', 'provip' ) ?></h3>

	<div class="plans items sortable">
		<?php
		if ( ! empty( $value) ) {
			foreach ( $value as $index => $plan ) {
				?>
				<div class="plan item">
					<span class="handle"></span>

					<p>
						<label>
							<?= __( 'ID', 'provip' ) ?>:<br/>
							<input type="text" value="<?= $index ?>" style="max-width: 40px;" disabled/>
						</label>
					</p>

					<p>
						<label>
							<?= __( 'Name', 'provip' ) ?>:<br/>
							<input type="text" value="<?= $plan['name'] ?>" name="<?= $name ?>[<?= $index ?>][name]"/>
						</label>
					</p>

					<p>
						<label>
							<?= __( 'Time (in days)', 'provip' ) ?>:<br/>
							<input type="text" value="<?= $plan[ 'days' ] ?>" name="<?= $name ?>[<?= $index ?>][days]"/>
						</label>
					</p>

					<p class="cost">
						<label>
							<?= __( 'Cost', 'provip' ) ?>:<br/>
							<?php
							foreach ( pvGetLevels() as $levelId => $levelName ):
								$val = isset( $plan[ 'cost' ][ $levelId ] ) ? $plan[ 'cost' ][ $levelId ] : '';
								?>
								<label>
									<span><?= $levelName ?></span>
									<input type="text" value="<?= $val ?>" name="<?= $name ?>[<?= $index ?>][cost][<?= $levelId ?>]"/>
								</label>
							<?php endforeach ?>
						</label>
					</p>
					<a href="javascript:void(0)" class="remove"><?= __( 'Remove Plan', 'provip' ) ?></a>
				</div>
			<?php
			}
		}
		?>
	</div>

	<a href="javascript:void(0)" class="add button"><?= __( 'Add Plan', 'provip' ) ?></a>


	<script type="text/html" class="template">
		<div class="plan item">
			<span class="handle"></span>

			<p>
				<label>
					<?= __( 'ID', 'provip' ) ?>:<br/>
					<input type="text" value="" style="max-width: 40px;" class="index" disabled/>
				</label>
			</p>

			<p>
				<label>
					<?= __( 'Name', 'provip' ) ?>:<br/>
					<input type="text" value="" data-name="<?= $name ?>[{{index}}][name]"/>
				</label>
			</p>

			<p>
				<label>
					<?= __( 'Time (in days)', 'provip' ) ?>:<br/>
					<input type="text" value="" data-name="<?= $name ?>[{{index}}][days]"/>
				</label>
			</p>

			<p class="cost">
				<label>
					<?= __( 'Cost', 'provip' ) ?>:<br/>
					<?php
					foreach ( pvGetLevels() as $levelId => $levelName ):
						?>
						<label>
							<span><?= $levelName ?></span>
							<input type="text" data-name="<?= $name ?>[{{index}}][cost][<?= $levelId ?>]"/>
						</label>
					<?php endforeach ?>
				</label>
			</p>
			<a href="javascript:void(0)" class="remove"><?= __( 'Remove Plan', 'provip' ) ?></a>
		</div>
	</script>
</div>
