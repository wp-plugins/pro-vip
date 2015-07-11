<?php
/**
 * @var string $method
 * @var        $url
 * @var array  $parameters
 */
ob_start();
?>

	<h3><?= __( 'Redirecting', 'provip' ) ?></h3>

	<form action="<?= $url ?>" method="<?= $method ?>">

		<button class="button" id="redirect"><?= __( 'Redirect Now', 'provip' ) ?></button>


		<?php
		foreach ( $parameters as $k => $v ) {
			echo '<input type="hidden" name="' . $k . '" value="' . $v . '"/>';
		}
		?>

	</form>

	<script type="text/javascript">
		setTimeout( function(){
			document.getElementById( 'redirect' ).click();
		}, 1500 );
	</script>

<?php
$html = ob_get_clean();
wp_die( $html, __( 'Redirecting', 'provip' ) );