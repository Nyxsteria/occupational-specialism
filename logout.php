<?php

session_start();

require 'db.php';

// Log logout event if user is authenticated

if (isset($_SESSION['user_id'])) {

    // Create hashed session identifier

    $sessionIdentifier =
        hash('sha256', session_id());

    // Log logout event

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

        $_SESSION['user_id'],

        'LOGOUT',

        $_SESSION['username'],

        $_SERVER['REMOTE_ADDR'],

        $_SERVER['HTTP_USER_AGENT'],

        $sessionIdentifier

    ]);

}

// Remove all session variables

$_SESSION = [];

// Remove the PHP session cookie

if (ini_get('session.use_cookies')) {

    $params = session_get_cookie_params();

    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );

}

// Destroy server-side session

session_destroy();

// Redirect user back to login page

header("Location: login.html");
exit();