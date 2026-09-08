<?php
/*
Template Name: Authentication-forgot-password
*/
?>

<head>
  <?php wp_head();?>
</head>

<body class="bg-forgot">
<!-- wrapper -->
<div class="wrapper">
    <div class="authentication-forgot d-flex align-items-center justify-content-center">
        <div class="card forgot-box">
            <div class="card-body">
                <div class="p-4 rounded  border">
                    <div class="text-center">
                        <img src="<?php echo get_template_directory_uri(); ?>/assets/images/icons/forgot-2.png" width="120" alt="" />
                    </div>
                    <h4 class="mt-5 font-weight-bold"><?php _e('Forgot Password?', 'rocker'); ?></h4>
                    <p class="text-muted"><?php _e('Enter your registered email ID to reset the password', 'rocker'); ?></p>
                        <label class="form-label"><?php _e('Email id', 'rocker'); ?></label>
                    <?php echo do_shortcode('[contact-form-7 id="6170b5a" title="Контактна форма 1"]'); ?>
                    <div class="d-grid gap-2">
                        <button type="button" id="submitButton" class="btn btn-primary btn-lg"><?php _e('Send', 'rocker'); ?></button> 
                        <a href="<?php echo esc_url(get_permalink(get_page_by_path('Authentication-sign-in')));?>" class="btn btn-light btn-lg"><i class='bx bx-arrow-back me-1'></i>Back to Login</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- end wrapper -->

<?php
    get_footer();
?>
