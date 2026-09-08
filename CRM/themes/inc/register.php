<?php
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register'])) {
$first_name = sanitize_text_field($_POST['first_name']);
$last_name = sanitize_text_field($_POST['last_name']);
$email = sanitize_email($_POST['email']);
$pass = $_POST['password'];
$country = sanitize_text_field($_POST['country']);

if($first_name || $last_name || $email || $pass)
{
    $error_message = 'Fill in all fields!';
}
if (!isset($_POST['terms'])) {
    $error_message = 'You must agree to the terms.';
    return;
}


if (email_exists($email)) {
    $error_message = 'This email is already registered.';
    return;
}

$user_id = wp_create_user($email, $pass, $email);

if (!is_wp_error($user_id)) {

    update_user_meta($user_id, 'first_name', $first_name);
    update_user_meta($user_id, 'last_name', $last_name);
    update_user_meta($user_id, 'country', $country);

    wp_update_user([
        'ID'           => $user_id,
        'display_name' => $first_name . ' ' . $last_name, 
    ]);

    
    wp_redirect(esc_url(get_permalink(get_page_by_path('Authentication-sign-in'))));

    exit;
} else {
    $error_message = 'Registration failed. Please try again.';
    return;
}

}