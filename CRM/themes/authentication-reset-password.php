<?php
/*
Template Name: Authentication-reset-password
*/
?>

<head>
  <?php wp_head();?>
</head>

<body>
	<!-- wrapper -->
	<div class="wrapper">
		<div class="authentication-reset-password d-flex align-items-center justify-content-center">
			<div class="row">
				<div class="col-12 col-lg-10 mx-auto">
					<div class="card">
						<div class="row g-0">
							<div class="col-lg-5 border-end">
								<div class="card-body">
									<div class="p-5">
										<div class="text-start">
											<img src="<?php echo get_template_directory_uri(); ?>/assets/images/logo-img.png" width="180" alt="">
										</div>
										<h4 class="mt-5 font-weight-bold"><?php _e('Generate New Password', 'rocker') ?></h4>
                                        <?php if (!empty($error_message)): ?>
                                            <div class="alert alert-danger text-center"><?php echo esc_html($error_message); ?></div>
                                        <?php endif; ?>
                                        <form method="post" action="">
                                            <p class="text-muted"><?php _e('We received your reset password request. Please enter your new password!', 'rocker') ?></p>
                                            <div class="mb-3 mt-5">
                                                <label class="form-label"><?php _e('New Password', 'rocker') ?></label>
                                                <div class="input-group" id="show_hide_password">
                                                <input type="password" class="form-control" placeholder="<?php _e('Enter new password', 'rocker') ?>" id="new-pass" name="new-pass" /><a href="javascript:;" id="iNewPass" class="input-group-text bg-transparent"><i class='bx bx-hide'></i></a>
                                                </div>
                                            </div>
                                        
                                            <div class="mb-3">
                                                <label class="form-label"><?php _e('Confirm Password', 'rocker') ?></label>
                                                <div class="input-group" id="show_hide_password">
                                                <input type="password" class="form-control" placeholder="<?php _e('Confirm Password', 'rocker') ?>" id="confirm-pass" name="confirm-pass" /><a href="javascript:;" id="iConfirmPass" class="input-group-text bg-transparent"><i class='bx bx-hide'></i></a>
                                                </div>
                                            </div> 
                                            <div id="password-requirements" class="mt-3 mb-3" style="display:none; color: red; font-size: 14px;">
                                                <p><?php _e('Password must have:', 'rocker'); ?></p>
                                                <ul class="list-pass">
                                                    <li ><span id="lengthCheck">❌</span><?php _e('Minimum 8 characters', 'rocker'); ?></li>
                                                    <li><span id="uppercaseCheck">❌</span><?php _e('At least one capital letter', 'rocker'); ?></li>
                                                    <li><span id="lowercaseCheck">❌</span><?php _e('At least one small letter', 'rocker'); ?></li>
                                                    <li><span id="numberCheck">❌</span><?php _e('At least one digit', 'rocker'); ?></li>
                                                    <li><span id="specialCharCheck">❌</span><?php _e('At least one special character (!@#$%^&*)', 'rocker'); ?></li>
                                                </ul>
                                            </div>
                                            <div class="d-grid gap-2">
                                                <button type="submit" id="submitPasswordForm" class="btn btn-primary" name="change-pass"><?php _e('Change Password', 'rocker') ?></button> <a href="<?php echo esc_url(get_permalink(get_page_by_path('Authentication-sign-in')));?>" class="btn btn-light"><?php _e('Back to Login', 'rocker') ?></a>
                                            </div>
                                        </form> 
									</div>
								</div>
							</div>
							<div class="col-lg-7">
								<img src="<?php echo get_template_directory_uri(); ?>/assets/images/login-images/forgot-password-frent-img.jpg" class="card-img login-img h-100" alt="...">
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<!-- end wrapper -->
</body>

<?php
    get_footer();
?>
