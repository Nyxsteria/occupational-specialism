<?php
<?php

session_start();
header("Content-Type: application/json; charset=utf-8");

$username = trim($_POST['username'] ?? '');
$password = trim($_POST['password'] ?? '');

if ($username === 'admin' && $password === 'password') {

    $_SESSION['logged_in'] = true;
    $_SESSION['username'] = $username;

    echo json_encode([
        "success" => true,
        "redirect" => "main.html"
    ]);

} else {

    echo json_encode([
        "success" => false,
        "message" => "Invalid username or password"
    ]);

}

if (isset($_POST['username']) && isset($_POST['password'])) {

    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if ($username === 'admin' && $password === 'password') {

        echo json_encode([
            "success" => true,
            "message" => "Login successful"
        ]);

    } else {

        echo json_encode([
            "success" => false,
            "message" => "Invalid username or password"
        ]);

    }

} else {

    echo json_encode([
        "success" => false,<?php

session_start();
header("Content-Type: application/json; charset=utf-8");

$username = trim($_POST['username'] ?? '');
$password = trim($_POST['password'] ?? '');

if ($username === 'admin' && $password === 'password') {

    $_SESSION['logged_in'] = true;
    $_SESSION['username'] = $username;

    echo json_encode([
        "success" => true,
        "redirect" => "main.html"
    ]);

} else {

    echo json_encode([
        "success" => false,
        "message" => "Invalid username or password"
    ]);

} else {

    echo json_encode([
        "success" => false,
        "message" => "Username and password are required"
    ]);

}

