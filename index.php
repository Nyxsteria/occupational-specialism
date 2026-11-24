<?php

session_start();

// Send logged-in users to the main page.
if (
    isset($_SESSION['logged_in']) &&
    $_SESSION['logged_in'] === true
) {
    header('Location: main.php');
    exit();
}

// Send logged-out users to the current login page.
header('Location: login.html');
exit();