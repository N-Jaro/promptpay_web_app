<?php
// public/auth/google-callback.php
// Handles the Google OAuth callback

require_once __DIR__ . '/../../src/config/config.php';

$config = require __DIR__ . '/../../src/config/config.php';

if (!isset($_GET['code'])) {
    echo 'No code parameter.';
    exit;
}

$code = $_GET['code'];

// Exchange code for access token
$token_url = 'https://oauth2.googleapis.com/token';
$params = [
    'code' => $code,
    'client_id' => $config['google']['client_id'],
    'client_secret' => $config['google']['client_secret'],
    'redirect_uri' => $config['google']['redirect_uri'],
    'grant_type' => 'authorization_code',
];

$options = [
    'http' => [
        'header' => "Content-type: application/x-www-form-urlencoded\r\n",
        'method' => 'POST',
        'content' => http_build_query($params),
    ],
];
$context = stream_context_create($options);
$response = file_get_contents($token_url, false, $context);
$token_data = json_decode($response, true);

if (!isset($token_data['access_token'])) {
    echo 'Failed to get access token.';
    exit;
}

// Get user info
$user_info_url = 'https://www.googleapis.com/oauth2/v2/userinfo';
$opts = [
    'http' => [
        'header' => "Authorization: Bearer " . $token_data['access_token'],
        'method' => 'GET',
    ],
];
$context = stream_context_create($opts);
$user_info = file_get_contents($user_info_url, false, $context);
$user = json_decode($user_info, true);

if (!$user || !isset($user['email'])) {
    echo 'Failed to get user info.';
    exit;
}

// Connect to DB
$db = $config['db'];
$mysqli = new mysqli($db['host'], $db['username'], $db['password'], $db['database']);

// Check for errors
if ($mysqli->connect_errno) {
    die('Connect Error: ' . $mysqli->connect_error);
}

// Prepare data
$email = $mysqli->real_escape_string($user['email']);
$name = $mysqli->real_escape_string($user['name'] ?? '');
$google_id = $mysqli->real_escape_string($user['id']);
$avatar = $mysqli->real_escape_string($user['picture'] ?? '');

// Insert or update user
$sql = "INSERT INTO users (email, name, google_id, avatar)
        VALUES ('$email', '$name', '$google_id', '$avatar')
        ON DUPLICATE KEY UPDATE name='$name', google_id='$google_id', avatar='$avatar', updated_at=NOW()";
$mysqli->query($sql);

// Optionally, fetch the user row
$result = $mysqli->query("SELECT * FROM users WHERE email = '$email' LIMIT 1");
$userRow = $result->fetch_assoc();

// Start session
session_start();
$_SESSION['user'] = $userRow;

// Redirect or show user info
header('Location: /promptpay_web_app/'); // or wherever you want
exit;