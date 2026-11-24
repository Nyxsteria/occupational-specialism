<?php
declare(strict_types=1);

session_start();

$userId = filter_var($_SESSION['user_id'] ?? null, FILTER_VALIDATE_INT);
if (($_SESSION['logged_in'] ?? false) !== true || !$userId) {
    header('Location: login.html');
    exit;
}

try {
    // db.php is the project's existing PDO bootstrap.
    require_once __DIR__ . '/db.php';

    // Read the current role from the database, so revoked admin access takes effect.
    $roleQuery = $pdo->prepare('SELECT role FROM users WHERE id = :id');
    $roleQuery->execute(['id' => $userId]);
    if ($roleQuery->fetchColumn() !== 'admin') {
        http_response_code(403);
        exit('Forbidden');
    }

    $perPage = 50;
    $totalLogs = (int) $pdo
        ->query('SELECT COUNT(*) FROM authentication_events')
        ->fetchColumn();
    $totalPages = max(1, (int) ceil($totalLogs / $perPage));

    $requestedPage = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT);
    $page = min(max(1, $requestedPage ?: 1), $totalPages);
    $offset = ($page - 1) * $perPage;

    $logsQuery = $pdo->prepare(
        'SELECT
            e.event_id,
            e.user_id,
            e.event_type,
            e.username_attempted,
            e.ip_address,
            e.failure_reason,
            e.event_timestamp,
            u.username
         FROM authentication_events AS e
         LEFT JOIN users AS u ON u.id = e.user_id
         ORDER BY e.event_timestamp DESC, e.event_id DESC
         LIMIT :limit OFFSET :offset'
    );
    $logsQuery->bindValue(':limit', $perPage, PDO::PARAM_INT);
    $logsQuery->bindValue(':offset', $offset, PDO::PARAM_INT);
    $logsQuery->execute();
    $logs = $logsQuery->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    error_log('Admin login-log page failed: ' . $e->getMessage());
    http_response_code(500);
    exit('Unable to load login activity. Check the server error log.');
}

function escapeHtml(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login activity</title>
    <link rel = "stylesheet" href="admin-panel.css">
</head>
<body>
    <main>
        <h1>Login activity</h1>
        <p><?= number_format($totalLogs) ?> events</p>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Time</th>
                        <th>Username</th>
                        <th>Event</th>
                        <th>IP address</th>
                        <th>Failure reason</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!$logs): ?>
                        <tr><td colspan="5">No login events found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($logs as $log): ?>
                            <tr>
                                <td><?= escapeHtml($log['event_timestamp']) ?></td>
                                <td><?= escapeHtml($log['username'] ?? $log['username_attempted']) ?></td>
                                <td><?= escapeHtml($log['event_type']) ?></td>
                                <td><?= escapeHtml($log['ip_address']) ?></td>
                                <td><?= escapeHtml($log['failure_reason']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <nav aria-label="Pagination">
            <?php if ($page > 1): ?>
                <a href="?page=<?= $page - 1 ?>">Previous</a>
            <?php endif; ?>
            <span>Page <?= $page ?> of <?= $totalPages ?></span>
            <?php if ($page < $totalPages): ?>
                <a href="?page=<?= $page + 1 ?>">Next</a>
            <?php endif; ?>
        </nav>
    </main>
</body>
</html>
