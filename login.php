<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

header('Content-Type: application/json');

require 'db.php';

$username = trim($_POST['username'] ?? '');
$password = trim($_POST['password'] ?? '');

$stmt = $pdo->prepare(
    "SELECT * FROM users WHERE username = ?"
);

$stmt->execute([$username]);

$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user) {

    if (password_verify($password, $user['password'])) {

        $_SESSION['logged_in'] = true;
        $_SESSION['username'] = $user['username'];

        echo json_encode([
            'success' => true,
            'message' => 'Login successful',
            'username' => $user['username']
        ]);

        exit;

    } else {

        echo json_encode([
            'success' => false,
            'message' => 'Invalid password'
        ]);

        exit;

    }

} else {

    echo json_encode([
        'success' => false,
        'message' => 'User not found'
    ]);

    exit;

}