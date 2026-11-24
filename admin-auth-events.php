<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, private');

session_start();

$userId = filter_var($_SESSION['user_id'] ?? null, FILTER_VALIDATE_INT);
if (($_SESSION['logged_in'] ?? false) !== true || !$userId) {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden']);
    exit;
}

try {
    // db.php is the project's existing PDO bootstrap.
    require_once __DIR__ . './db.php';

    // Confirm the current database role instead of trusting a stale session role.
    $roleQuery = $pdo->prepare('SELECT role FROM users WHERE id = :id');
    $roleQuery->execute(['id' => $userId]);
    if ($roleQuery->fetchColumn() !== 'admin') {
        http_response_code(403);
        echo json_encode(['error' => 'Forbidden']);
        exit;
    }

    $query = $pdo->prepare(
        'SELECT
            e.event_id,
            e.user_id,
            u.username,
            e.username_attempted,
            e.event_type,
            e.ip_address,
            e.failure_reason,
            e.event_timestamp
         FROM authentication_events AS e
         LEFT JOIN users AS u ON u.id = e.user_id
         ORDER BY e.event_timestamp DESC, e.event_id DESC
         LIMIT 50'
    );
    $query->execute();

    echo json_encode(
        ['events' => $query->fetchAll(PDO::FETCH_ASSOC)],
        JSON_INVALID_UTF8_SUBSTITUTE | JSON_PRETTY_PRINT
    );
} catch (Throwable $e) {
    error_log('Admin authentication-events request failed: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Unable to load login activity']);
}
