<?php
require_once 'config.php';

if (isset($_GET['code'])) {
    $code = $_GET['code'];
    
    // Exchange code for access token
    $token_url = 'https://oauth2.googleapis.com/token';
    $token_data = [
        'client_id' => GOOGLE_CLIENT_ID,
        'client_secret' => GOOGLE_CLIENT_SECRET,
        'code' => $code,
        'grant_type' => 'authorization_code',
        'redirect_uri' => GOOGLE_REDIRECT_URI
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $token_url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($token_data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    $token_info = json_decode($response, true);
    
    if (isset($token_info['access_token'])) {
        // Get user info
        $user_info_url = 'https://www.googleapis.com/oauth2/v2/userinfo?access_token=' . $token_info['access_token'];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $user_info_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $user_response = curl_exec($ch);
        curl_close($ch);
        
        $user_info = json_decode($user_response, true);
        
        if (isset($user_info['id'])) {
            // Store user info in session
            $_SESSION['user_id'] = $user_info['id'];
            $_SESSION['user_email'] = $user_info['email'];
            $_SESSION['user_name'] = $user_info['name'];
            $_SESSION['user_picture'] = $user_info['picture'] ?? '';
            
            // Store user data in a simple JSON file (in production, use a database)
            $users_file = 'data/users.json';
            if (!file_exists('data')) {
                mkdir('data', 0755, true);
            }
            
            $users = [];
            if (file_exists($users_file)) {
                $users = json_decode(file_get_contents($users_file), true) ?? [];
            }
            
            $users[$user_info['id']] = [
                'id' => $user_info['id'],
                'email' => $user_info['email'],
                'name' => $user_info['name'],
                'picture' => $user_info['picture'] ?? '',
                'created_at' => date('Y-m-d H:i:s'),
                'last_login' => date('Y-m-d H:i:s')
            ];
            
            file_put_contents($users_file, json_encode($users, JSON_PRETTY_PRINT));
            
            // Redirect to dashboard
            redirect('dashboard.php');
        }
    }
}

// If we get here, something went wrong
redirect('google_login.php?error=auth_failed');
?> 