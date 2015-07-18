<?php
/**
 * @var $pvFile Pro_VIP_File
 */
global $pvFile;
$canDownload     = $pvFile->canUserDownloadFile();
$purchaseEnabled = $pvFile->singlePurchaseEnabled;
?>


<div class="wv-single-download <?= $canDownload ? 'downloadable' : '' ?>">
	<h3 class="title"><?= __( 'Download Files', 'provip' ) ?></h3>
	<?php if ( pvGetOption( 'single_file_guest_purchase', 'yes' ) == 'yes' || is_user_logged_in() ) { ?>
		<div class="files-list">
			<?php foreach ( $pvFile->getFiles() as $file ) :
				$pvFile->setupFileData( $file );
				?>

				<div class="file">

					<span class="file-name"><?= $pvFile::getFileDlName() ?></span>
					<a class="download button wv-btn primary small" href="<?= $pvFile::downloadUrl() ?>"><?= __( 'Download', 'provip' ) ?></a>
					<?php if ( $purchaseEnabled ) : ?>
						<a class="purchase button wv-btn primary small" href="#pv-purchase-file" rel="modal:open"><?= __( 'Purchase', 'provip' ) ?></a>
						<div class="file-data" style="display: none;">
							<p class="file-index"><?= $pvFile::fileIndex() ?></p>

							<p class="file-name"><?= $pvFile::getFileDlName() ?></p>

							<p class="file-price"><?= Pro_VIP_Currency::priceHTML( $pvFile::getFilePrice() ) ?></p>
						</div>
					<?php endif ?>

				</div>

			<?php endforeach ?>
		</div>

		<?php if ( $purchaseEnabled ) :

			$currentUser = wp_get_current_user();
			?>
			<form id="pv-purchase-file" method="post" action="<?= $pvFile::singlePurchaseUrl() ?>" style="display: none;;">

				<h2 class="title"><?= sprintf( __( 'Purchase %s', 'provip' ), '<strong class="file-name"></strong>' ) ?></h2>

				<table>

					<?php do_action( 'pro_vip_purchase_file_form_before', $pvFile ) ?>

					<tr>
						<td class="title">
							<strong><?= __( 'Price', 'provip' ) ?></strong>
						</td>
						<td class="input">
							<strong class="file-price"></strong>
						</td>
					</tr>

					<tr>
						<td class="title">
							<label for="pv-gateway">
								<strong><?= __( 'Gateway', 'provip' ) ?></strong>
								<span class="required">*</span>
							</label>
						</td>
						<td class="input">
							<?= Pro_VIP_Payment_Gateway::gatewaysListDropdown() ?>
						</td>
					</tr>

					<tr>
						<td class="title">
							<label for="pv-first-name">
								<strong><?= __( 'First Name', 'provip' ) ?></strong>
								<span class="required">*</span>
							</label>
						</td>
						<td class="input">
							<input type="text" name="pv-first-name" id="pv-first-name" value="<?= get_user_meta( $currentUser->ID, 'first_name', true ) ?>"/>
						</td>
					</tr>

					<tr>
						<td class="title">
							<label for="pv-last-name">
								<strong><?= __( 'Last Name', 'provip' ) ?></strong>
							</label>
						</td>
						<td class="input">
							<input type="text" name="pv-last-name" id="pv-last-name" value="<?= get_user_meta( $currentUser->ID, 'last_name', true ) ?>"/>
						</td>
					</tr>


					<tr>
						<td class="title">
							<label for="pv-email-address">
								<strong><?= __( 'Email Address', 'provip' ) ?></strong>
								<span class="required">*</span>
							</label>
						</td>
						<td class="input">
							<input name="pv-email-address" id="pv-email-address" value="<?= $currentUser->user_email ?>" type="email"/>
						</td>
					</tr>

					<?php do_action( 'pro_vip_purchase_file_form_after', $pvFile ) ?>


				</table>

				<p class="purchase">
					<input type="hidden" name="file-index" class="file-index" value=""/>
					<button class="wv-btn primary"><?= __( 'Purchase', 'provip' ) ?></button>
				</p>

			</form>
		<?php endif ?>

	<?php } else { ?>
		<p class="login">
			<span><?= __( 'You need to be logged in.', 'provip' ) ?></span>
			<a href="#wv-login-modal" class="button login wv-btn" rel="modal:open"><?= __( 'Login', 'provip' ) ?></a>
			<a href="#wv-registration-form" class="button login wv-btn" rel="modal:open"><?= __( 'Register', 'provip' ) ?></a>
		</p>

	<?php } ?>

</div>