<?php
declare(strict_types=1);

// In production, log errors instead of displaying them to visitors.
error_reporting(E_ALL);
ini_set('display_errors', '0');

require_once __DIR__ . '/session.php';
header('Content-Type: application/json; charset=utf-8');

require __DIR__ . '/db.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!is_array($data)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request.'
    ]);
    exit;
}

$username = $data['username'] ?? '';
$password = $data['password'] ?? '';

if (!is_string($username) || !is_string($password)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid username or password.'
    ]);
    exit;
}

$username = trim($username);

if ($username === '' || $password === '') {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'All fields are required.'
    ]);
    exit;
}

if (
    strlen($username) < 3 ||
    strlen($username) > 32 ||
    !preg_match('/\A[a-zA-Z0-9_]+\z/', $username)
) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Username must be 3–32 characters using letters, numbers, or underscores.'
    ]);
    exit;
}

if (strlen($password) < 12 || strlen($password) > 4096) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Password must be 12–4096 characters.'
    ]);
    exit;
}

try {
    $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ?');
    $stmt->execute([$username]);

    if ($stmt->fetch()) {
        http_response_code(409);
        echo json_encode([
            'success' => false,
            'message' => 'A user with this username already exists.'
        ]);
        exit;
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare(
        'INSERT INTO users (username, password) VALUES (?, ?)'
    );
    $stmt->execute([$username, $hashedPassword]);
} catch (PDOException $exception) {
    if ((int) $exception->getCode() === 23000) {
        http_response_code(409);
        echo json_encode([
            'success' => false,
            'message' => 'A user with this username already exists.'
        ]);
        exit;
    }

    error_log('Account creation failed: ' . $exception->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Unable to create account right now.'
    ]);
    exit;
}

$insertedId = (int) $pdo->lastInsertId();

$stmt = $pdo->prepare(
    'SELECT id, username, role FROM users WHERE id = ?'
);
$stmt->execute([$insertedId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Account was created, but could not be loaded.'
    ]);
    exit;
}

session_regenerate_id(true);

$_SESSION['logged_in'] = true;
$_SESSION['user_id'] = (int) $user['id'];
$_SESSION['username'] = $user['username'];
$_SESSION['role'] = $user['role'];

echo json_encode([
    'success' => true,
    'message' => 'Account created successfully.',
    'username' => $user['username']
]);
