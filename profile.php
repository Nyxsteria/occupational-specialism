<?php
declare(strict_types=1);


require_once('./session.php');

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, private');

$userId = filter_var(
    $_SESSION['user_id'] ?? '',
    FILTER_VALIDATE_INT
);

if (
    ($_SESSION['logged_in'] ?? false) !== true ||
    $userId === false ||
    $userId < 1
) {
    http_response_code(401);
    echo json_encode([
        'status' => 'error',
        'message' => 'Not authenticated.'
    ]);
    exit;
}

try {
    require_once './db.php';

    $stmt = $pdo->prepare(
        'SELECT username FROM users WHERE id = :id'
    );
    $stmt->execute(['id' => $userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        http_response_code(404);
        echo json_encode([
            'status' => 'error',
            'message' => 'User record not found.'
        ]);
        exit;
    }

    echo json_encode([
        'status' => 'success',
        'data' => [
            'username' => $user['username']
        ]
    ], JSON_INVALID_UTF8_SUBSTITUTE);
} catch (Throwable $e) {
    error_log('Profile request failed: ' . $e->getMessage());

    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Internal server error.'
    ]);
}