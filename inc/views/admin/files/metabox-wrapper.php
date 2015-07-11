<?php
/**
 * @var $post         WP_Post
 * @var $formBuilder  PV_Framework_Form_Builder
 */
$file  = Pro_VIP_File::find( $post->ID );

if( ! $file ){
	return false;
}

$files = $file->getFiles();

?>


<div class="provip-file-metabox">

	<div class="uploader big" style="display: <?= ! empty( $files ) ? 'none;' : 'block;' ?>">
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



	<?php

	Pro_VIP::loadView(
		'admin/files/files-container',
		array(
			'file' => $file,
			'filesOrder' => (array) get_post_meta( $post->ID, '_provip_files_order', true ),
			'hide' => empty( $files )
		)
	);
	Pro_VIP::loadView(
		'admin/files/file-settings',
		array(
			'hide'        => empty( $files ),
			'formBuilder' => $formBuilder
		)
	);
	?>

</div>