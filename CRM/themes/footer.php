<?php
if (!is_page_template('authentication-signup.php') && !is_page_template('authentication-signin.php') && !is_page_template('authentication-forgot-password.php') && !is_page_template('authentication-reset-password.php')):
	?>
	<footer class="page-footer">
		<p class="mb-0"><?php _e('Copyright © 2021. All right reserved.', 'rocker'); ?></p>
	</footer>
	<?php
endif;
wp_footer();
?>
</body>
</html>