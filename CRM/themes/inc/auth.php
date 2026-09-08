<?php 

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['auth'])) {

    $email = sanitize_email($_POST['email']);
    $password = $_POST['password'];
    $remember = isset($_POST['remember']) ? true : false;

    $user = get_user_by('email', $email);
    
    if (!$user) {
        $error_message = 'User not found!';
        return;
    }


    $user_id = $user->ID;
    if ( !wp_check_password($password, $user->user_pass, $user_id) ) {
        $error_message = 'Incorrect password!';
        return;
    }


    wp_set_current_user($user_id);
    wp_set_auth_cookie($user_id, $remember);
    
    wp_redirect(home_url());
    exit;
}


if (isset($_GET['action']) && $_GET['action'] == 'reset_password' && isset($_GET['token']) && isset($_GET['user'])) {
    $user_id = $_GET['user'];
    $reset_token = $_GET['token'];
    $user = get_user_by('id', $user_id);

    $stored_token = get_user_meta($user_id, 'reset_password_token', true);

    if ($stored_token === $reset_token) {
        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['new-pass'])) {
            $newPassword = $_POST['new-pass'];
            $confirmPassword = $_POST['confirm-pass'];

            if ($newPassword && $confirmPassword && $newPassword === $confirmPassword) {
                wp_set_password($newPassword, $user_id); 
                delete_user_meta($user_id, 'reset_password_token'); 
                $error_message = 'Password reset successfully!';
            } else {
                $error_message = 'Passwords do not match.';
            }
           
        }
        
    }
}