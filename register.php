<?php

error_reporting(E_ALL);

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

header('Content-Type: application/json; charset=utf-8');
$output = ['messages' => [], 'status' => []]; 