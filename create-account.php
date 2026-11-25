<?php

// In production, log errors instead of displaying them to visitors.
error_reporting(E_ALL);
ini_set('display_errors', '0');

session_start();
header('Content-Type: application/json; charset=utf-8');

require __DIR__ . '/db.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!is_array($data)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request.'
    ]);
    exit;
}

$username = $data['username'] ?? '';
$password = $data['password'] ?? '';

if (!is_string($username) || !is_string($password)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid username or password.'
    ]);
    exit;
}

$username = trim($username);

// Don't trim the password: spaces may be part of it.
if ($username === '' || $password === '') {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'All fields are required.'
    ]);
    exit;
}

$stmt = $pdo->prepare('SELECT id FROM users WHERE username = ?');
$stmt->execute([$username]);

if ($stmt->fetch()) {
    echo json_encode([
        'success' => false,
        'message' => 'A user with this username already exists.'
    ]);
    exit;
}

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

$stmt = $pdo->prepare(
    'INSERT INTO users (username, password) VALUES (?, ?)'
);
$stmt->execute([$username, $hashedPassword]);

// Only look up the user and create a logged-in session after insertion.
$insertedId = (int) $pdo->lastInsertId();

$stmt = $pdo->prepare(
    'SELECT id, username, role FROM users WHERE id = ?'
);
$stmt->execute([$insertedId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Account was created, but could not be loaded.'
    ]);
    exit;
}

session_regenerate_id(true);

$_SESSION['logged_in'] = true;
$_SESSION['user_id'] = (int) $user['id'];
$_SESSION['username'] = $user['username'];
$_SESSION['role'] = $user['role'];

echo json_encode([
    'success' => true,
    'message' => 'Account created successfully.',
    'username' => $user['username']
]);