<?php

error_reporting(E_ALL);

ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);

header('Content-Type: application/json; charset=utf-8');
$output = ['messages' => [], 'status' => []]; 