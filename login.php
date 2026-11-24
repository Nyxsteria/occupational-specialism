<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session

session_start();

// Return JSON responses

header('Content-Type: application/json');

// Database connection

require 'db.php';

// Retrieve submitted login credentials

$username = trim($_POST['username'] ?? '');
$password = trim($_POST['password'] ?? '');

// Find matching user account

$stmt = $pdo->prepare(
    "SELECT * FROM users WHERE username = ?"
);

$stmt->execute([$username]);

$user = $stmt->fetch(PDO::FETCH_ASSOC);

// User account found

if ($user) {

    // Validate password

    if (password_verify($password, $user['password'])) {

        // Generate new session ID

        session_regenerate_id(true);

        // Store user session

        $_SESSION['logged_in'] = true;
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];

        // Create hashed session identifier for audit trail

        $sessionIdentifier =
            hash('sha256', session_id());

        // Log successful login

        $log = $pdo->prepare("
            INSERT INTO authentication_events (
                user_id,
                event_type,
                username_attempted,
                ip_address,
                user_agent,
                session_identifier
            )
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        $log->execute([

            $user['id'],

            'LOGIN_SUCCESS',

            $username,

            $_SERVER['REMOTE_ADDR'],

            $_SERVER['HTTP_USER_AGENT'],

            $sessionIdentifier

        ]);

        // Return success response

        echo json_encode([

            'success' => true,

            'message' => 'Login successful.',

            'username' => $user['username']

        ]);

    } else {

        // Log incorrect password

        $log = $pdo->prepare("
            INSERT INTO authentication_events (
                user_id,
                event_type,
                username_attempted,
                ip_address,
                user_agent,
                session_identifier,
                failure_reason
            )
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

        $log->execute([

            $user['id'],

            'LOGIN_INCORRECT',

            $username,

            $_SERVER['REMOTE_ADDR'],

            $_SERVER['HTTP_USER_AGENT'],

            null,

            'INVALID_PASSWORD'

        ]);

        // Return failed response

        echo json_encode([

            'success' => false,

            'message' => 'Username or password is incorrect.'

        ]);

    }

}

// User account not found

else {

    // Log failed login attempt

    $log = $pdo->prepare("
        INSERT INTO authentication_events (
            user_id,
            event_type,
            username_attempted,
            ip_address,
            user_agent,
            session_identifier,
            failure_reason
        )
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");

    $log->execute([

        null,

        'LOGIN_FAILED',

        $username,

        $_SERVER['REMOTE_ADDR'],

        $_SERVER['HTTP_USER_AGENT'],

        null,

        'ACCOUNT_NOT_FOUND'

    ]);

    // Return failed response

    echo json_encode([

        'success' => false,

        'message' => 'Username or password is incorrect.'

    ]);

}