<?php
function rocker_enqueue_assets()
{
  wp_enqueue_style('bootstrap', get_template_directory_uri() . '/assets/css/bootstrap.min.css');
  wp_enqueue_style('bootstrap-extended', get_template_directory_uri() . '/assets/css/bootstrap-extended.css');
  wp_enqueue_style('vectormap', get_template_directory_uri() . '/assets/plugins/vectormap/jquery-jvectormap-2.0.2.css');
  wp_enqueue_style('simplebar', get_template_directory_uri() . '/assets/plugins/simplebar/css/simplebar.css');
  wp_enqueue_style('perfect-scrollbar', get_template_directory_uri() . '/assets/plugins/perfect-scrollbar/css/perfect-scrollbar.css');
  wp_enqueue_style('metismenu', get_template_directory_uri() . '/assets/plugins/metismenu/css/metisMenu.min.css');
  wp_enqueue_style('pace', get_template_directory_uri() . '/assets/css/pace.min.css');
  wp_enqueue_style('app-style', get_template_directory_uri() . '/assets/css/app.css', filemtime(get_template_directory(__FILE__) . '/assets/css/app.css'));
  wp_enqueue_style('inc-style', get_template_directory_uri() . '/assets/css/inc.css', filemtime(get_template_directory(__FILE__) . '/assets/css/inc.css'));
  wp_enqueue_style('icons', get_template_directory_uri() . '/assets/css/icons.css');
  wp_enqueue_style('dark-theme', get_template_directory_uri() . '/assets/css/dark-theme.css');
  wp_enqueue_style('semi-dark', get_template_directory_uri() . '/assets/css/semi-dark.css');
  wp_enqueue_style('header-colors', get_template_directory_uri() . '/assets/css/header-colors.css');

  wp_enqueue_style('google-fonts', 'https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap', array('jquery'), null);

  add_action('wp_head', function() {
    echo '<link rel="icon" href="' . get_template_directory_uri() . '/assets/images/favicon-32x32.png" type="image/png">';
});

  wp_enqueue_script('jquery', get_template_directory_uri() . '/assets/js/jquery.min.js', array('jquery'), null, true);
  wp_enqueue_script('bootstrap', get_template_directory_uri() . '/assets/js/bootstrap.bundle.min.js', array('jquery'), null, true);
  wp_enqueue_script('pace', get_template_directory_uri() . '/assets/js/pace.min.js', array('jquery'), null, true);
  wp_enqueue_script('simplebar', get_template_directory_uri() . '/assets/plugins/simplebar/js/simplebar.min.js', array('jquery'), null, true);
  wp_enqueue_script('metismenu', get_template_directory_uri() . '/assets/plugins/metismenu/js/metisMenu.min.js', array('jquery'), null, true);
  wp_enqueue_script('perfect-scrollbar', get_template_directory_uri() . '/assets/plugins/perfect-scrollbar/js/perfect-scrollbar.js', array('jquery'), null, true);
  wp_enqueue_script('vectormap-main', get_template_directory_uri() . '/assets/plugins/vectormap/jquery-jvectormap-2.0.2.min.js', array('jquery'), null, true);
  wp_enqueue_script('vectormap-world', get_template_directory_uri() . '/assets/plugins/vectormap/jquery-jvectormap-world-mill-en.js', array('vectormap-main'), null, true);
  wp_enqueue_script('chartjs', get_template_directory_uri() . '/assets/plugins/chartjs/js/Chart.min.js', array('jquery'), null, true);
  wp_enqueue_script('chartjs-extension', get_template_directory_uri() . '/assets/plugins/chartjs/js/Chart.extension.js', array('chartjs'), null, true);
  wp_enqueue_script('index', get_template_directory_uri() . '/assets/js/index.js', array('jquery'), null, true);
  wp_enqueue_script('app-js', get_template_directory_uri() . '/assets/js/app.js', array('jquery'),  filemtime(get_template_directory() . '/assets/js/app.js'), true);
  wp_enqueue_script('reg-js', get_template_directory_uri() . '/assets/js/reg.js', array('jquery'),  filemtime(get_template_directory() . '/assets/js/reg.js'), true);
}

add_action('wp_enqueue_scripts', 'rocker_enqueue_assets');

add_action('init', 'custom_logout_redirect');

add_action('template_redirect', 'check_login_user');

function check_login_user() {
    $allowed_pages = array('authentication-sign-in', 'authentication-sign-up','authentication-reset-password', 'authentication-forgot-password');

    if (is_page($allowed_pages)) {
  
        return;
    }

    if (!is_user_logged_in() && !get_google_user_info()) {
        wp_redirect(home_url('/authentication-sign-in/')); 
      
        exit;
    }
}

function rocker_load_textdomain() {
  load_theme_textdomain('rocker', get_template_directory() . '/languages');
}
add_action('after_setup_theme', 'rocker_load_textdomain');


require_once get_template_directory() . '/inc/register.php';

require_once get_template_directory() . '/inc/auth.php';

require_once get_template_directory() . '/inc/API/api-auth.php';



session_start();
function custom_logout_redirect() {
    if (isset($_GET['logout'])) {
 
            unset($_SESSION['google_access_token']);
            session_destroy();
            session_write_close();
            // wp_redirect(home_url()); 

            // exit;
            wp_redirect(home_url('/authentication-sign-in/')); 
            exit;
    }
}


add_action('admin_init', function() {
  if (!current_user_can('administrator') && !wp_doing_ajax()) {
      wp_redirect(home_url());
  }
});

add_action('wp_head', function() {
  if (!current_user_can('administrator')) {
      echo '<style>
          #wpadminbar {
              display: none !important;
          }
      </style>';
  } else {
    echo '<style>
          .topbar, .sidebar-wrapper, .sidebar-header {
              top:32px;
          }  
      </style>';
  }
});

add_filter( 'wpcf7_validate_configuration', '__return_false' );

//Блокуємо перевірку Contact Form
add_filter(
  'wpcf7_config_validator_available_error_codes',
  function ( $error_codes, $contact_form ) {

      $error_codes_to_disable = array(
          'unsafe_email_without_protection',
      );

      $error_codes = array_diff( $error_codes, $error_codes_to_disable );

      return $error_codes;
  },
  10, 2
);

//Перевіряємо email на існування в бд
add_filter('wpcf7_validate_email*', 'custom_validate_email', 10, 2);

function custom_validate_email($result, $tag) {
    $email = isset($_POST['your-email']) ? sanitize_email($_POST['your-email']) : '';

    if ($email) {
        $user = get_user_by('email', $email);
        
        if (!$user) {
            $result->invalidate($tag, 'No user with this email was found');
        } else {
            $reset_token = bin2hex(random_bytes(32)); 

            update_user_meta($user->ID, 'reset_password_token', $reset_token);

            $reset_url = add_query_arg([
                'action' => 'reset_password',
                'token' => $reset_token,
                'user' => $user->ID
            ], home_url('authentication-reset-password')); 

            wp_mail($email, 'Password Reset Request', 'Click here to reset your password: ' . $reset_url);
        }
    }

    return $result;
}





