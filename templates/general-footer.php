<div id="wv-login-modal" style="display:none;">
	<?php
	wp_login_form( array(
		'redirect' => pvCurrentPageUrl()
	) );
	?>
</div>

<div id="wv-registration-form" style="display:none"> <!-- Registration -->
	<h2 class="title">Register your Account</h2>

	<form action="<?php echo site_url( 'wp-login.php?action=register', 'login_post' ) ?>" method="post">
		<input type="text" name="user_login" value="Username" id="user_login" class="input"/>
		<input type="text" name="user_email" value="E-Mail" id="user_email" class="input"/>
		<?php do_action( 'register_form' ); ?>
		<input type="submit" value="Register" id="register"/>
		<hr/>
		<p class="statement">A password will be e-mailed to you.</p>


	</form>
</div><!-- /Registration -->