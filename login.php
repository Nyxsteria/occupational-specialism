<?php

header("Content-Type: application/json; charset=utf-8");

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
        "success" => false,
        "message" => "Username and password are required"
    ]);

}