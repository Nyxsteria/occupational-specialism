<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

header('Content-Type: application/json');

require 'db.php';

$data = json_decode(file_get_contents("php://input"), true);

$username = trim($data['username'] ?? '');
$password = trim($data['password'] ?? '');

if (empty($username) || empty($password)) {

    echo json_encode([
        "success" => false,
        "message" => "All fields are required."
    ]);
    exit();
}

$stmt = $pdo->prepare(
    "SELECT id FROM users WHERE username = ?"
);

$stmt->execute([$username]);

if ($stmt->fetch()) {

    echo json_encode([
        "success" => false,
        "message" => "A user with this username already exists."
    ]);

    exit();
}

$hashedPassword = password_hash(
    $password,
    PASSWORD_DEFAULT
);

$stmt = $pdo->prepare(
    "INSERT INTO users (username, password)
     VALUES (?, ?)"
);

$stmt->execute([
    $username,
    $hashedPassword
]);

$_SESSION['logged_in'] = true;
$_SESSION['username'] = $username;

echo json_encode([
    "success" => true,
    "message" => "Account created successfully.",
    "username" => $username
]);