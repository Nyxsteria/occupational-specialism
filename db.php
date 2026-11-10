<?php

$host = 'localhost';
$dbname = '367163';
$user = '367163';
$password = 'worth';

try {

    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $user,
        $password
    );

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch(PDOException $e) {

    die(json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]));
}