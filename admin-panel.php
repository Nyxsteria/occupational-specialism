<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php'; // Must provide a PDO connection in $pdo

if (session_status() !== PHP_SESSION_ACTIVE) {
    // Requires HTTPS in production. If your app configures sessions elsewhere,
    // use that configuration instead.
    session_set_cookie_params([
        'httponly' => true,
        'secure' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

$userId = filter_var($_SESSION['user_id'] ?? null, FILTER_VALIDATE_INT);

if (!$userId) {
    http_response_code(403);
    exit('Forbidden');
}

try {
    // Check the current database role rather than relying on a possibly stale
    // role value in the session.
    $roleQuery = $pdo->prepare('SELECT role FROM users WHERE id = :id');
    $roleQuery->execute(['id' => $userId]);
    $role = $roleQuery->fetchColumn();

    if ($role !== 'admin') {
        http_response_code(403);
        exit('Forbidden');
    }

    $page = max(1, filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1);
    $perPage = 50;
    $offset = ($page - 1) * $perPage;

    $totalLogs = (int) $pdo
    ->query('SELECT COUNT(*) FROM authentication_events')
    ->fetchColumn();

    $logsQuery = $pdo->prepare(
        'SELECT
            e.event_id,
            e.user_id,
            e.event_type,
            e.username_attempted,
            e.ip_address,
            e.event_timestamp,
            u.username
        FROM authentication_events AS e
        LEFT JOIN users AS u ON u.id = e.user_id
        ORDER BY e.event_timestamp DESC
        LIMIT :limit OFFSET :offset'
    );
    $logsQuery->bindValue(':limit', $perPage, PDO::PARAM_INT);
    $logsQuery->bindValue(':offset', $offset, PDO::PARAM_INT);
    $logsQuery->execute();

    $logs = $logsQuery->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    error_log('Admin login-log page failed: ' . $e->getMessage());
    http_response_code(500);
    exit('Unable to load login activity.');
}

function escapeHtml(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

$totalPages = max(1, (int) ceil($totalLogs / $perPage));
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login activity</title>
    <style>
        body { font: 1rem/1.5 system-ui, sans-serif; margin: 2rem; }
        .table-wrap { overflow-x: auto; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border-bottom: 1px solid #ddd; padding: .65rem; text-align: left; }
        th { background: #f4f4f4; }
        nav { display: flex; gap: 1rem; margin-top: 1rem; }
    </style>
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
                        <th>Result</th>
                        <th>IP address</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!$logs): ?>
                        <tr><td colspan="4">No login events found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($logs as $log): ?>
                            <tr>
                                <td><?= escapeHtml($log['occurred_at']) ?></td>
                                <td><?= escapeHtml($log['username'] ?? $log['attempted_username']) ?></td>
                                <td><?= escapeHtml($log['status']) ?></td>
                                <td><?= escapeHtml($log['ip_address']) ?></td>
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