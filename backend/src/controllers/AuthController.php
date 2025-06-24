<?php
// src/controllers/AuthController.php
// Handles authentication logic (Google OAuth, etc.)

class AuthController
{
    public static function googleLogin()
    {
        $config = require __DIR__ . '/../config/config.php';
        $client_id = $config['google']['client_id'];
        $redirect_uri = $config['google']['redirect_uri'];
        $scope = 'openid email profile';
        $state = bin2hex(random_bytes(16));
        $auth_url = "https://accounts.google.com/o/oauth2/v2/auth?" . http_build_query([
            'client_id' => $client_id,
            'redirect_uri' => $redirect_uri,
            'response_type' => 'code',
            'scope' => $scope,
            'state' => $state,
            'access_type' => 'online',
            'prompt' => 'select_account',
        ]);
        // Optionally store $state in session for CSRF protection
        header('Location: ' . $auth_url);
        exit;
    }
}