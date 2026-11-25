<?php
// Start the session to access stored user credentials
session_start();

// Set header to return JSON responses
header('Content-Type: application/json');

// 1. Guard Clause: Check if the user is authenticated
if (!isset($_SESSION['username'])) {
    http_response_code(401); // Unauthorized
    echo json_encode(['status' => 'error', 'message' => 'User not logged in.']);
    exit();
}

$username = $_SESSION['username'];

// 2. Database Connection Credentials
require_once('db');

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    // 3. Initialize PDO Connection
    $pdo = new PDO($dsn, $dbUser, $dbPass, $options);

    // 4. Securely fetch user details (Using Prepared Statements to prevent SQL Injection)
    $stmt = $pdo->prepare('SELECT id, username, email, bio, created_at FROM users WHERE username = :username');
    $stmt->execute(['username' => $username]);
    $user = $stmt->fetch();

    if ($user) {
        // Remove sensitive data like password hashes if they were included in the query
        echo json_encode([
            'status' => 'success',
            'data'   => $user
        ]);
    } else {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'User record not found.']);
    }

} catch (\PDOException $e) {
    // Log the actual error internally; return a clean error to the client
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Internal server error.']);
}