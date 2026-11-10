<?php

$host = 'localhost';
$user = '367163';
$password = 'worth';
$db = '367163';

$conn = new mysqli($host, $user, $password, $db);

if($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}