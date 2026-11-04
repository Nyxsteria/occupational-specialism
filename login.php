<?php

session_start();

header('Content-Type: application/json');

$username = trim($_POST['username'] ?? '');
$password = trim($_POST['password'] ?? '');

if ($username === 'admin' && $password === 'password') {

    $_SESSION['logged_in'] = true;
    $_SESSION['username'] = $username;

    echo json_encode([
        'success' => true
    ]);

} else {

    echo json_encode([
        'success' => false,
        'message' => 'Invalid username or password'
    ]);

}