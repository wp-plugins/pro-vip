<?php
/**
 * @var $file        Pro_VIP_File
 * @var $hide
 * @var $filesOrder  array
 */
global $post;
?>
<div class="files-container" style="<?= $hide ? 'display:none;' : '' ?>">


	<span class="tip"><?= __( 'Items are sortable.', 'provip' ) ?></span>

	<div class="files">
		<?php
		$orderd = pvSortArray( $file->getFiles(), $filesOrder, 'id' );
		foreach ( $orderd as $downloadFile ) {
			$file->setupFileData( $downloadFile );
			Pro_VIP::loadView( 'admin/files/file-item', array( 'file' => $file ) );

		} ?>
	</div>

	<div class="uploader">
		<label class="button">
			<span><?= __( 'Choose File', 'provip' ) ?></span>
			<span class="progress-bar"></span>
			<input
				class="provip-file-uploader"
				name="file[]"
				data-url="<?= admin_url( 'admin-ajax.php' ) ?>"
				data-postid="<?= $post->ID ?>"
				data-nonce="<?= wp_create_nonce( 'provip_ajax_upload_file' ) ?>"
				multiple
				type="file"/>
		</label>
	</div>


</div>