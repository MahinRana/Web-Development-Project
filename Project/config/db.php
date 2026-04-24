<?php
// config/db.php

define('DB_HOST', 'localhost');
define('DB_USER', 'root');       // ← Change to your MySQL username
define('DB_PASS', '');           // ← Change to your MySQL password
define('DB_NAME', 'taskflow_db');

$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if (!$conn) {
    // Only return JSON error if this is an AJAX request
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'DB connection failed: ' . mysqli_connect_error()]);
        exit();
    }
    die('<h3 style="font-family:sans-serif;color:red;padding:2rem;">Database connection failed: ' . mysqli_connect_error() . '<br><small>Check config/db.php credentials.</small></h3>');
}

mysqli_set_charset($conn, 'utf8mb4');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: ' . (strpos($_SERVER['PHP_SELF'], '/actions/') !== false ? '../' : '') . 'login.php');
        exit();
    }
}

function redirectIfLoggedIn() {
    if (isset($_SESSION['user_id'])) {
        header('Location: dashboard.php');
        exit();
    }
}

function clean($conn, $data) {
    return mysqli_real_escape_string($conn, htmlspecialchars(trim($data)));
}