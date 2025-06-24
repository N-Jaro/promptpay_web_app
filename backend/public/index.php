<?php
// public/index.php
// Main entry point for all backend requests

require_once __DIR__ . '/../src/config/config.php';
require_once __DIR__ . '/../src/routes/web.php';

// Simple router (for demonstration)
$scriptName = dirname($_SERVER['SCRIPT_NAME']);
$uri = $_SERVER['REQUEST_URI'];
if (strpos($uri, $scriptName) === 0) {
    $uri = substr($uri, strlen($scriptName));
}
$uri = strtok($uri, '?'); // Remove query string
if ($uri === '' || $uri === false)
    $uri = '/';

if ($uri === '/' || $uri === '/index.php') {
    echo json_encode(['status' => 'ok', 'message' => 'Backend API is running.']);
    exit;
}

// Route handling
if ($uri === '/api/ping' || $uri === '/api/ping/') {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'success', 'message' => 'pong']);
    exit;
}

// Google OAuth login endpoint
if ($uri === '/auth/google') {
    require_once __DIR__ . '/../src/controllers/AuthController.php';
    AuthController::googleLogin();
    exit;
}

// User info endpoint
if ($uri === '/api/user' || $uri === '/api/user/') {
    session_start();
    header('Content-Type: application/json');
    if (isset($_SESSION['user'])) {
        echo json_encode(['logged_in' => true, 'user' => $_SESSION['user']]);
    } else {
        echo json_encode(['logged_in' => false]);
    }
    exit;
}

// Save transaction endpoint
if ($uri === '/api/transactions' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    session_start();
    header('Content-Type: application/json');
    if (!isset($_SESSION['user'])) {
        http_response_code(401);
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
        exit;
    }
    $input = json_decode(file_get_contents('php://input'), true);
    $amount = $input['amount'] ?? null;
    $description = $input['description'] ?? '';
    if (!$amount || !is_numeric($amount)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Invalid amount']);
        exit;
    }
    require_once __DIR__ . '/../src/models/Transaction.php';
    $config = require __DIR__ . '/../src/config/config.php';
    $db = new PDO('mysql:host=' . $config['db']['host'] . ';dbname=' . $config['db']['database'] . ';charset=' . $config['db']['charset'], $config['db']['username'], $config['db']['password']);
    $transaction = new Transaction($db);
    // Debug: log input and session
    error_log('Transaction API called. User: ' . print_r($_SESSION['user'], true) . ' Input: ' . print_r($input, true));
    $userId = $_SESSION['user']['id'] ?? null;
    error_log('User ID used for transaction: ' . print_r($userId, true));
    // Debug: echo the insert statement and parameters
    $debugSql = 'INSERT INTO transactions (user_id, amount, description, created_at) VALUES (?, ?, ?, NOW())';
    error_log('SQL: ' . $debugSql);
    error_log('Params: ' . print_r([$userId, $amount, $description], true));
    $result = $transaction->create($userId, $amount, $description);
    error_log('Transaction insert result: ' . print_r($result, true));
    if ($result) {
        echo json_encode(['status' => 'success']);
    } else {
        $errorInfo = $db->errorInfo();
        error_log('DB Error: ' . print_r($errorInfo, true));
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Failed to save transaction', 'db_error' => $errorInfo, 'user_id' => $userId]);
    }
    exit;
}

// Get transaction history for logged-in user
if ($uri === '/api/transactions' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    session_start();
    header('Content-Type: application/json');
    if (!isset($_SESSION['user'])) {
        http_response_code(401);
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
        exit;
    }
    require_once __DIR__ . '/../src/models/Transaction.php';
    $config = require __DIR__ . '/../src/config/config.php';
    $db = new PDO('mysql:host=' . $config['db']['host'] . ';dbname=' . $config['db']['database'] . ';charset=' . $config['db']['charset'], $config['db']['username'], $config['db']['password']);
    $transaction = new Transaction($db);
    $transactions = $transaction->getByUser($_SESSION['user']['id']);
    echo json_encode(['status' => 'success', 'transactions' => $transactions]);
    exit;
}

// If no route matched
http_response_code(404);
echo 'URL ' . $uri;
echo json_encode(['status' => 'error', 'message' => 'Not found']);