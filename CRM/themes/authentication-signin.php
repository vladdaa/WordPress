<?php
/*
Template Name: Authentication-sign-in
*/
?>

<head>
  <?php wp_head(); ?>
</head>

<body class="bg-login">
<!--wrapper-->
<div class="wrapper">
    <div class="section-authentication-signin d-flex align-items-center justify-content-center my-5 my-lg-0">
        <div class="container-fluid">
            <div class="row row-cols-1 row-cols-lg-2 row-cols-xl-3">
                <div class="col mx-auto">
                    <div class="mb-4 text-center">
                        <img src="<?php echo get_template_directory_uri(); ?>/assets/images/logo-img.png" width="180" alt="" />
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <div class="border p-4 rounded">
                                <div class="text-center">
                                    <h3 class=""><?php _e('Sign in', 'rocker'); ?></h3>
                                    <p><?php _e('Don\'t have an account yet?', 'rocker'); ?> <a href="<?php echo esc_url(get_permalink(get_page_by_path('Authentication-sign-up')));?>"><?php _e('Sign up here', 'rocker'); ?></a>
                                    </p>
                                </div>
                                <?php if (!empty($error_message)): ?>
                                    
                                    <div class="alert alert-danger text-center"><?php echo esc_html($error_message); ?></div>
                                <?php endif; ?>
                                <div class="d-grid">
                                <form method="get" action="">
                                <button  type="submit" class="btn my-4 shadow-sm btn-white" name="sign_in_google" style="width: 100%;"> 
                                    <span class="d-flex justify-content-center align-items-center">
                                        <img class="me-2" src="<?php echo get_template_directory_uri(); ?>/assets/images/icons/search.svg" width="16" alt="Image Description">
                                        <span><?php _e('Sign in with Google', 'rocker'); ?></span>
                                    </span>
                                </button>
                                
                                <button type="submit" class="btn btn-facebook" name="sign_in_facebook" style="width: 100%;"><i class="bx bxl-facebook"></i><?php _e('Sign in with Facebook', 'rocker'); ?></button>
                                    </form>
                                </div>
                                <div class="login-separater text-center mb-4"> <span><?php _e('OR SIGN IN WITH EMAIL', 'rocker'); ?></span>
                                    <hr/>
                                </div>
                                <div class="form-body">
                                    <form method="post" action="" class="row g-3">
                                        <div class="col-12">
                                            <label for="inputEmailAddress" class="form-label"><?php _e('Email Address', 'rocker'); ?></label>
                                            <input type="email" class="form-control" id="inputEmailAddress" name="email" placeholder="Email Address">
                                        </div>
                                        <div class="col-12">
                                            <label for="inputChoosePassword" class="form-label"><?php _e('Enter Password', 'rocker'); ?></label>
                                            <div class="input-group" id="show_hide_password">
                                                <input type="password" class="form-control border-end-0" id="inputChoosePassword" name="password" placeholder="Enter Password"> <a href="javascript:;" id="iPassChange" class="input-group-text bg-transparent"><i class='bx bx-hide'></i></a>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="flexSwitchCheckChecked" name="remember" value="1">
                                                <label class="form-check-label" for="flexSwitchCheckChecked"><?php _e('Remember Me', 'rocker'); ?></label>
                                            </div>
                                        </div>
                                        <div class="col-md-6 text-end">	<a href="<?php echo esc_url(get_permalink(get_page_by_path('Authentication-forgot-password')));?>"><?php _e('Forgot Password?', 'rocker'); ?></a>
                                        </div>
                                        <div class="col-12">
                                            <div class="d-grid">
                                                <button type="submit" name="auth" class="btn btn-primary"><i class="bx bxs-lock-open"></i><?php _e('Sign in', 'rocker'); ?></button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--end row-->
        </div>
    </div>
</div>
<!--end wrapper-->
<?php
    get_footer();
?>
