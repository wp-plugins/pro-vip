<?php
/**
 * @var $file Pro_VIP_File
 */
?>
<div class="file">
		<label>
			<?= __( 'File download name:', 'provip' ) ?>
			<input type="text" class="dl-name" name="provip-file-settings[files][<?= $file::fileIndex() ?>][dl-name]" value="<?= $file::getFileDlName() ?>"/>
		</label>
		<label>
			<?= __( 'File price:', 'provip' ) ?>
			<input type="text" class="price" name="provip-file-settings[files][<?= $file::fileIndex() ?>][price]" value="<?= $file::getFilePrice() ?>"/>
		</label>

	<p class="file-actions">
		<span><?= sprintf( _n( 'Downloaded %s time', 'Downloaded %s times', $file::downloadsCount(), 'provip' ), "<strong>" . $file::downloadsCount() . "</strong>" ) ?></span>
		<a href="<?= $file::downloadUrl() ?>"><?= __( 'Download File', 'provip' ) ?></a>
		<a href="#" class="delete-file wv-confirm" data-id="<?= $file->ID ?>" data-file-index="<?= $file::fileIndex() ?>" data-nonce="<?= wp_create_nonce( 'wvDeleteFile' ) ?>"><?= __( 'Delete File', 'provip' ) ?></a>
	</p>

	<input type="hidden" name="provip-file-settings[order][<?= $file::fileIndex() ?>]"/>
</div>