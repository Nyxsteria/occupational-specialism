<?php

declare(strict_types=1);

require_once __DIR__ . '/session.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Allow: POST');
    http_response_code(405);
    exit('Method not allowed.');
}

$submittedToken = $_POST['csrf_token'] ?? '';
$sessionToken = $_SESSION['csrf_token'] ?? '';

if (
    !is_string($submittedToken) ||
    !is_string($sessionToken) ||
    $sessionToken === '' ||
    !hash_equals($sessionToken, $submittedToken)
) {
    http_response_code(403);
    exit('Invalid request.');
}

try {
    require __DIR__ . '/db.php';

    if (isset($_SESSION['user_id'])) {
        $sessionIdentifier = hash('sha256', session_id());

        $log = $pdo->prepare(
            'INSERT INTO authentication_events (
                user_id,
                event_type,
                username_attempted,
                ip_address,
                user_agent,
                session_identifier
             ) VALUES (?, ?, ?, ?, ?, ?)'
        );

        $log->execute([
            $_SESSION['user_id'],
            'LOGOUT',
            $_SESSION['username'] ?? '',
            $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            $_SERVER['HTTP_USER_AGENT'] ?? '',
            $sessionIdentifier,
        ]);
    }
} catch (Throwable $e) {
    error_log('Logout event failed: ' . $e->getMessage());
}

$_SESSION = [];

if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();

    setcookie(
        session_name(),
        '',
        [
            'expires' => time() - 42000,
            'path' => $params['path'],
            'domain' => $params['domain'],
            'secure' => $params['secure'],
            'httponly' => $params['httponly'],
            'samesite' => 'Lax',
        ]
    );
}

session_destroy();

header('Location: login.html');
exit();
