<?php
session_start();

/**
 * Авторизація користувача Google
 */

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['sign_in_google'])) {
    $auth_url= get_google_auth_url();
    wp_redirect($auth_url); 
   
    exit; 
}

if (isset($_GET['code'])) {
    $client_id = GOOGLE_CLIENT_ID;
    $redirectUri = GOOGLE_REDIRECT_URI;
    $client_secret = GOOGLE_CLIENT_SECRETS;
    $code = $_GET['code'];

    $token_url = 'https://oauth2.googleapis.com/token';
    
    $data = [
        'code' => $code,
        'client_id' => $client_id,
        'client_secret' => $client_secret,
        'redirect_uri' => $redirectUri,
        'grant_type' => 'authorization_code'
    ];

    $response = wp_remote_post($token_url, [
        'body' => $data,
        'timeout' => 15,
    ]);

    if (is_wp_error($response)) {
        echo 'Error: ' . $response->get_error_message();
        exit;
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    if (!isset($data['access_token'])) {
        echo 'Error: Access token not received';
        exit;
    }

    $_SESSION['google_access_token'] = $data['access_token'];

    wp_redirect(home_url());
    exit;

}

function get_google_auth_url() {
        $client_id = GOOGLE_CLIENT_ID;
        $redirectUri = GOOGLE_REDIRECT_URI;
        return "https://accounts.google.com/o/oauth2/v2/auth?response_type=code&client_id={$client_id}&redirect_uri={$redirectUri}&scope=openid%20email%20profile";
    }

/**
 * Отримання інформації про користувача Google
 */
function get_google_user_info() {
    if (!isset($_SESSION['google_access_token'])) {
        return null;
    }

    $access_token = $_SESSION['google_access_token'];
    $url = 'https://www.googleapis.com/oauth2/v3/userinfo';

    $response = wp_remote_get($url, [
        'headers' => [
            'Authorization' => 'Bearer ' . $access_token
        ]
    ]);

    if (is_wp_error($response)) {
        return null;
    }

    $body = wp_remote_retrieve_body($response);
    $user_info = json_decode($body, true);

    if (!isset($user_info['sub'])) {

        return null;
    }

    return [
        'name' => $user_info['name'] ?? '',
        'email' => $user_info['email'] ?? '',
        'picture' => $user_info['picture'] ?? '',
    ];
}
