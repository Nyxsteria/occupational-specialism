<?php


error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();


header('Content-Type: application/json');

require 'db.php';

$username = trim($_POST['username'] ?? '');
$password = trim($_POST['password'] ?? '');

$sql = "SELECT * FROM users WHERE username = ?";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "s",
    $username
);

$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 1) {

    $user = $result->fetch_assoc();

    if ($password === $user['password']) {

        $_SESSION['logged_in'] = true;

        $_SESSION['username'] =
            $user['username'];

        echo json_encode([
            'success' => true
        ]);

    } else {

        echo json_encode([
            'success' => false,
            'message' => 'Invalid password'
        ]);

    }

} else {

    echo json_encode([
        'success' => false,
        'message' => 'User not found'
    ]);

}
