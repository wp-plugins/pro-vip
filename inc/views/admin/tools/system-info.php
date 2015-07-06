<?php
defined( 'ABSPATH' ) or die;
/**
 * @var $data
 */
?>
<?php
foreach ( $data as $label => $rows ):
	?>
	<table class="widefat" cellspacing="0">
		<thead>
		<tr>
			<th colspan="3"><?= $label ?></th>
		</tr>
		</thead>
		<tbody>
		<?php
		foreach ( $rows as $item => $val ):
			?>
			<tr>
				<td style="width: 40%;"><?= $item ?>:</td>
				<td style="width: 60%;"><?php
					switch( gettype( $val ) ){
						default:
							echo $val;
							break;
						case 'boolean':
							echo $val ? '<img class="emoji" alt="✔" src="http://s.w.org/images/core/emoji/72x72/2714.png">' : '-';
							break;
					}
					?></td>
			</tr>
		<?php endforeach ?>
		</tbody>
	</table>
	<br/><br/>
<?php endforeach ?>