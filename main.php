<?php
session_start();

if (
    !isset($_SESSION['logged_in']) ||
    $_SESSION['logged_in'] !== true
) {
    header('Location: login.html');
    exit();
}
?>

<header>
    <div class="header-container">

        <div class="site-title">
            Welcome back,
            <span class="username">
                <?= htmlspecialchars($_SESSION['username']) ?>
            </span>
        </div>

        <nav class="navigator-menu">
            <ul>
                <li><button class="nav-button">About</button></li>
                <li><button class="nav-button">Contact</button></li>
                <li><button class="nav-button">Resources</button></li>
                <li><button class="nav-button">Home</button></li>
            </ul>
        </nav>

    </div>
</header>