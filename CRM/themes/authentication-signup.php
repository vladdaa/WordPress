<?php
/*
Template Name: Authentication-sign-up
*/

?>
<head>
  <?php wp_head();?>
</head>

<body class="bg-login">
<!--wrapper-->
<div class="wrapper">
    <div class="d-flex align-items-center justify-content-center my-5 my-lg-0">
        <div class="container">
            <div class="row row-cols-1 row-cols-lg-2 row-cols-xl-2">
                <div class="col mx-auto">
                    <div class="my-4 text-center">
                        <img src="<?php echo get_template_directory_uri(); ?>/assets/images/logo-img.png" width="180" alt="">
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <div class="border p-4 rounded">
                                <div class="text-center">
                                    <h3 class=""><?php _e('Sign Up', 'rocker'); ?></h3>
                                    <p><?php _e('Already have an account?', 'rocker'); ?> <a href="<?php echo esc_url(get_permalink(get_page_by_path('Authentication-sign-in')));?>"<?php _e('Sign in here', 'rocker'); ?>></a>
                                    </p>
                                </div>
                                <?php
                                if (!empty($error_message)): ?>
                                    <div class="alert alert-danger text-center"><?php echo esc_html($error_message); ?></div>
                                <?php endif; ?>
                                <div class="d-grid">
                                    <a class="btn my-4 shadow-sm btn-white" href="javascript:;"> <span class="d-flex justify-content-center align-items-center">
                                    <img class="me-2" src="<?php echo get_template_directory_uri(); ?>/assets/images/icons/search.svg" width="16" alt="Image Description">
                                    <span><?php _e('Sign Up with Google', 'rocker'); ?></span>
                                        </span>
                                    </a> <a href="javascript:;" class="btn btn-facebook"><i class="bx bxl-facebook"></i><?php _e('Sign Up with Facebook', 'rocker'); ?></a>
                                </div>
                                <div class="login-separater text-center mb-4"> <span><?php _e('OR SIGN UP WITH EMAIL', 'rocker'); ?></span>
                                    <hr/>
                                </div>
                                <div class="form-body">
                                    <form  method="post" action="" class="row g-3">
                                        <div class="col-sm-6">
                                            <label for="inputFirstName" class="form-label"><?php _e('First Name', 'rocker'); ?></label>
                                            <input type="text" class="form-control" id="inputFirstName" name="first_name" placeholder="Jhon">
                                        </div>
                                        <div class="col-sm-6">
                                            <label for="inputLastName" class="form-label"><?php _e('Last Name', 'rocker'); ?></label>
                                            <input type="text" class="form-control" id="inputLastName" name="last_name" placeholder="Deo">
                                        </div>
                                        <div class="col-12">
                                            <label for="inputEmailAddress" class="form-label"><?php _e('Email Address', 'rocker'); ?></label>
                                            <input type="email" class="form-control" id="inputEmailAddress" name="email" placeholder="example@user.com">
                                        </div>
                                        <div class="col-12">
                                            <label for="inputChoosePassword" class="form-label"><?php _e('Password', 'rocker'); ?></label>
                                            <div class="input-group" id="show_hide_password">
                                                <input type="password" class="form-control border-end-0" id="inputChoosePassword"  name="password" placeholder="Enter Password"> <a href="javascript:;" id="iPassChange" class="input-group-text bg-transparent"><i class='bx bx-hide'></i></a>
                                            </div>
                                            <div id="password-requirements" class="mt-3" style="display:none; color: red; font-size: 14px;">
                                                <p><?php _e('Password must have:', 'rocker'); ?></p>
                                                <ul class="list-pass">
                                                    <li ><span id="lengthCheck">❌</span><?php _e('Minimum 8 characters', 'rocker'); ?></li>
                                                    <li><span id="uppercaseCheck">❌</span><?php _e('At least one capital letter', 'rocker'); ?></li>
                                                    <li><span id="lowercaseCheck">❌</span><?php _e('At least one small letter', 'rocker'); ?></li>
                                                    <li><span id="numberCheck">❌</span><?php _e('At least one digit', 'rocker'); ?></li>
                                                    <li><span id="specialCharCheck">❌</span><?php _e('At least one special character (!@#$%^&*)', 'rocker'); ?></li>
                                                </ul>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <label for="inputSelectCountry" class="form-label"><?php _e('Country', 'rocker'); ?></label>
                                            <select class="form-select" id="inputSelectCountry" aria-label="Default select example" name="country">
                                                <option selected><?php _e('India', 'rocker'); ?></option>
                                                <option value="1"><?php _e('United Kingdom', 'rocker'); ?></option>
                                                <option value="2"><?php _e('America', 'rocker'); ?></option>
                                                <option value="3"><?php _e('Dubai', 'rocker'); ?></option>
                                            </select>
                                        </div>
                                        <div class="col-12">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" name="terms" id="flexSwitchCheckChecked">
                                                <label class="form-check-label" for="flexSwitchCheckChecked"><?php _e('I read and agree to Terms & Conditions', 'rocker'); ?></label>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="d-grid">
                                                <button type="submit" id="submitPasswordForm" name="register" class="btn btn-primary"><i class='bx bx-user'></i><?php _e('Sign up', 'rocker'); ?></button>
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
