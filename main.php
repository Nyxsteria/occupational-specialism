<?php

declare(strict_types=1);

require_once __DIR__ . '/session.php';

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if (
    !isset($_SESSION['logged_in']) ||
    $_SESSION['logged_in'] !== true
) {
    header('Location: login.html');
    exit();
}

$username = $_SESSION['username'] ?? '';
$userId = (int) ($_SESSION['user_id'] ?? 0);

$statistics = [
    'tasks_completed' => 0,
    'skills_developed' => 0,
    'certificates' => 0,
    'hours_studied' => 0,
];

$statisticsError = false;
$statisticsRowFound = false;

if ($userId > 0) {
    require_once __DIR__ . '/db.php';

    try {
        $stmt = $pdo->prepare(
            'SELECT s.user_id, s.tasks_completed, s.skills_developed,
                    s.certificates, s.hours_studied
             FROM users AS u
             LEFT JOIN user_dashboard_statistics AS s ON s.user_id = u.id
             WHERE u.id = ?'
        );
        $stmt->execute([$userId]);
        $row = $stmt->fetch();

        if ($row && $row['user_id'] !== null) {
            $statisticsRowFound = true;
            foreach ($statistics as $key => $defaultValue) {
                $statistics[$key] = max(0, (int) $row[$key]);
            }
        }
    } catch (PDOException $exception) {
        $statisticsError = true;
        error_log('Dashboard statistics query failed: ' . $exception->getMessage());
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Occupational Specialism</title>
    <link rel="stylesheet" href="css/styles.css">
    <script src="scripts/script.js" defer></script>
</head>
<body>
<header>
    <div class="header-container">
        <div class="site-title">
            <h1>Welcome back,
                <span id="username"><?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?></span>
            </h1>
        </div>
        <nav class="navigator-menu">
            <ul>
                <li><a class="nav-button">About</a></li>
                <li><a class="nav-button">Contact</a></li>
                <li><a class="nav-button">Resources</a></li>
                <li><a class="nav-button">Home</a></li>
                <li>
                    <form method="post" action="logout.php">
                        <input
                            type="hidden"
                            name="csrf_token"
                            value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>"
                        >
                        <button type="submit" class="nav-button">Log Out</button>
                    </form>
                </li>
            </ul>
        </nav>
    </div>
</header>
<main class="main-content">
    <div class="left-column">
        <div class="image-container">
            <img src="images/placeholder.png" alt="Placeholder image">
        </div>
        <section class="card-container">
            <div class="card">
                <h2>About</h2>
                <p>Learn more about the Occupational Specialism and its objectives.</p>
                <button id="openPopup">Read More</button>
            </div>
            <div class="card">
                <h2>Contact</h2>
                <p>Find contact details and ways to get in touch.</p>
                <button>View Contact Information</button>
            </div>
            <div class="card">
                <h2>Home</h2>
                <p>Return to the homepage and explore other sections.</p>
                <button>Return To Home</button>
            </div>
        </section>
    </div>
    <div class="right-column">
        <section class="dashboard">
            <p class="subtitle">WELCOME TO</p>
            <h1>Occupational Specialism</h1>
            <p class="subtitle">
                Explore project work, industry standards, technical skills and
                learning resources related to Digital Production, Design and Development.
            </p>
            <button class="dashboard-btn">View Projects</button>
            <button class="dashboard-btn">Learn More</button>
            <div class="body-container">
                <p class="quote">"Developing today's skills for tomorrow's opportunities"</p>
            </div>
        </section>
        <section class="data-card" aria-label="Your learning statistics">
            <?php if ($statisticsError): ?>
                <p class="statistics-message" role="status">
                    Statistics are currently unavailable. Please check the server error log.
                </p>
            <?php elseif (!$statisticsRowFound): ?>
                <p class="statistics-message" role="status">
                    No statistics row was found for this account; showing zeroes.
                </p>
            <?php endif; ?>
            <div class="item-card">
                <img src="images/document.svg" alt="">
                <h2><?php echo number_format($statistics['tasks_completed']); ?></h2>
                <p>Tasks Completed</p>
            </div>
            <div class="item-card">
                <img src="images/grad-cap.svg" alt="">
                <h2><?php echo number_format($statistics['skills_developed']); ?></h2>
                <p>Skills Developed</p>
            </div>
            <div class="item-card">
                <img src="images/medal.svg" alt="">
                <h2><?php echo number_format($statistics['certificates']); ?></h2>
                <p>Certificates</p>
            </div>
            <div class="item-card">
                <img src="images/clock.svg" alt="">
                <h2><?php echo number_format($statistics['hours_studied']); ?></h2>
                <p>Hours Studied</p>
            </div>
        </section>
    </div>
</main>
</body>
</html>
