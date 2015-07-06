<?php
/**
 * @var $field PV_Framework_Multicheckbox_Field_Type
 */
$value = $field->get_value() != '' ? (array) $field->get_value() : array();
$name  = $field->settings[ 'inputName' ];
?>
<div class="provip-levels-field wv-repeater" data-id="levels">

	<h3><?= __( 'VIP Level', 'provip' ) ?></h3>

	<div class="levels items sortable">
		<?php
		if ( ! empty( $value ) ) {
			foreach ( $value as $index => $level ) {
				?>
				<div class="level item">
					<span class="handle"></span>

					<p>
						<label>
							<?= __( 'Name', 'provip' ) ?>:<br/>
							<input type="text" value="<?= $level[ 'name' ] ?>" name="<?= $name ?>[<?= $index ?>][name]"/>
						</label>
					</p>

					<p>
						<label>
							<?= __( 'Check by default', 'provip' ) ?>:<br/>
							<select name="<?= $name ?>[<?= $index ?>][check-by-default]" style="min-width: 90px;">
								<option value="no" <?= checked( $level[ 'check-by-default' ], 'no', false ) ?>><?= __( 'No', 'provip' ) ?></option>
								<option value="yes" <?= selected( $level[ 'check-by-default' ], 'yes', false ) ?>><?= __( 'Yes', 'provip' ) ?></option>
							</select>
						</label>
					</p>

					<a href="javascript:void(0)" class="remove"><?= __( 'Remove Plan', 'provip' ) ?></a>
				</div>
			<?php
			}
		}
		?>
	</div>

	<a href="javascript:void(0)" class="add button"><?= __( 'Add Level', 'provip' ) ?></a>


	<script type="text/html" class="template">
		<div class="level item">
			<span class="handle"></span>


			<p>
				<label>
					<?= __( 'Name', 'provip' ) ?>:<br/>
					<input type="text" value="" data-name="<?= $name ?>[{{index}}][name]"/>
				</label>
			</p>

			<p>
				<label>
					<?= __( 'Check by default', 'provip' ) ?>:<br/>
					<select data-name="<?= $name ?>[{{index}}][check-by-default]" style="min-width: 90px;">
						<option value="no"><?= __( 'No', 'provip' ) ?></option>
						<option value="yes"><?= __( 'Yes', 'provip' ) ?></option>
					</select>
				</label>
			</p>


			<a href="javascript:void(0)" class="remove"><?= __( 'Remove Plan', 'provip' ) ?></a>
		</div>
	</script>
</div>