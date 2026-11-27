<?php
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '0');

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, private');

// Send a JSON response with the chosen HTTP status, then stop processing the request.
function respond(int $status, array $body): never
{
    http_response_code($status);
    echo json_encode($body, JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Allow: POST');
    respond(405, ['success' => false, 'message' => 'Invalid request.']);
}

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';
if (!is_string($username) || !is_string($password)) {
    respond(400, ['success' => false, 'message' => 'Invalid username or password.']);
}
$username = trim($username);
// Do not trim passwords: whitespace may be part of a user's password.
if ($username === '' || $password === '' || strlen($username) > 255 || strlen($password) > 4096) {
    respond(400, ['success' => false, 'message' => 'Invalid username or password.']);
}

// Only use REMOTE_ADDR. Forwarded headers are client-controlled unless a trusted
// reverse proxy is explicitly configured, and must not be trusted by default.
$ipAddress = substr((string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown'), 0, 45);
$userAgent = substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 1000);
$windowSeconds = 15 * 60;
$pairLimit = 5;
$ipLimit = 30;

try {
    require './db.php';

    // Count recent credential failures matching the supplied account/IP filter.
    // Throttled requests are excluded so repeated retries cannot extend the window.
    $failureCount = static function (PDO $pdo, string $where, array $params): array {
        $query = $pdo->prepare(
            "SELECT COUNT(*) AS failures, MIN(event_timestamp) AS oldest_failure
             FROM authentication_events
             WHERE event_type IN ('LOGIN_INCORRECT', 'LOGIN_FAILED')
               AND event_timestamp >= (NOW() - INTERVAL 15 MINUTE)
               AND {$where}"
        );
        $query->execute($params);
        $result = $query->fetch(PDO::FETCH_ASSOC) ?: [];
        return [
            'count' => (int) ($result['failures'] ?? 0),
            'oldest' => $result['oldest_failure'] ?? null,
        ];
    };

    $pairFailures = $failureCount(
        $pdo,
        'username_attempted = ? AND ip_address = ?',
        [$username, $ipAddress]
    );
    $ipFailures = $failureCount($pdo, 'ip_address = ?', [$ipAddress]);

    // Calculate remaining wait time when a failure count reaches its limit.
    $throttle = static function (array $failures, int $limit, int $windowSeconds): int {
        if ($failures['count'] < $limit || !$failures['oldest']) {
            return 0;
        }
        $oldest = strtotime((string) $failures['oldest']);
        return max(1, $oldest + $windowSeconds - time());
    };

    $retryAfter = max(
        $throttle($pairFailures, $pairLimit, $windowSeconds),
        $throttle($ipFailures, $ipLimit, $windowSeconds)
    );

    $stmt = $pdo->prepare('SELECT id, username, password, role FROM users WHERE username = ? LIMIT 1');
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Save a login event so successful, failed, and throttled attempts are auditable.
    $writeEvent = static function (
        PDO $pdo,
        ?int $userId,
        string $eventType,
        string $username,
        string $ipAddress,
        string $userAgent,
        ?string $failureReason = null,
        ?string $sessionIdentifier = null
    ): void {
        $log = $pdo->prepare(
            'INSERT INTO authentication_events (
                user_id, event_type, username_attempted, ip_address,
                user_agent, session_identifier, failure_reason
             ) VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $log->execute([
            $userId,
            $eventType,
            $username,
            $ipAddress,
            $userAgent,
            $sessionIdentifier,
            $failureReason,
        ]);
    };

    // Apply limits to the account+source-address pair and to the source address
    // across accounts. There is deliberately no account-only lockout: someone
    // cannot block a victim simply by repeatedly submitting the victim's name.
    if ($retryAfter > 0) {
        $writeEvent(
            $pdo,
            $user ? (int) $user['id'] : null,
            'LOGIN_THROTTLED',
            $username,
            $ipAddress,
            $userAgent,
            'RATE_LIMITED'
        );
        header('Retry-After: ' . $retryAfter);
        respond(429, [
            'success' => false,
            'message' => 'Too many unsuccessful attempts. Please try again later.',
            'retry_after' => $retryAfter,
        ]);
    }

    if ($user && password_verify($password, (string) $user['password'])) {
        session_start();
        session_regenerate_id(true);

        $_SESSION['logged_in'] = true;
        $_SESSION['user_id'] = (int) $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];

        $sessionIdentifier = hash('sha256', session_id());
        $writeEvent(
            $pdo,
            (int) $user['id'],
            'LOGIN_SUCCESS',
            $username,
            $ipAddress,
            $userAgent,
            null,
            $sessionIdentifier
        );

        respond(200, [
            'success' => true,
            'message' => 'Login successful.',
            'username' => $user['username'],
        ]);
    }

    $writeEvent(
        $pdo,
        $user ? (int) $user['id'] : null,
        $user ? 'LOGIN_INCORRECT' : 'LOGIN_FAILED',
        $username,
        $ipAddress,
        $userAgent,
        $user ? 'INVALID_PASSWORD' : 'ACCOUNT_NOT_FOUND'
    );

    respond(401, [
        'success' => false,
        'message' => 'Username or password is incorrect.',
    ]);
} catch (Throwable $e) {
    error_log('Login request failed: ' . $e->getMessage());
    respond(500, [
        'success' => false,
        'message' => 'Unable to sign in right now. Please try again later.',
    ]);
}
