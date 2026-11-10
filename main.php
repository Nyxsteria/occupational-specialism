<?php

session_start();

if (
    !isset($_SESSION['logged_in']) ||
    $_SESSION['logged_in'] !== true
){
    header('Location: index.html');
    exit();
}

$username = $_SESSION['username'];


?>