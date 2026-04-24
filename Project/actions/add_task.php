<?php
require_once '../config/db.php';
header('Content-Type: application/json');
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit();
}

$user_id     = (int)$_SESSION['user_id'];
$title       = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');
$priority    = trim($_POST['priority'] ?? 'medium');
$status      = trim($_POST['status'] ?? 'todo');
$due_date    = !empty($_POST['due_date']) ? $_POST['due_date'] : null;

if (empty($title)) {
    echo json_encode(['status' => 'error', 'message' => 'Task title is required']);
    exit();
}

$valid_priorities = ['low', 'medium', 'high'];
$valid_statuses   = ['todo', 'inprogress', 'done'];
if (!in_array($priority, $valid_priorities)) $priority = 'medium';
if (!in_array($status, $valid_statuses))     $status   = 'todo';

$title       = mysqli_real_escape_string($conn, $title);
$description = mysqli_real_escape_string($conn, $description);

if ($due_date) {
    $stmt = mysqli_prepare($conn, "INSERT INTO tasks (user_id, title, description, priority, status, due_date) VALUES (?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, 'isssss', $user_id, $title, $description, $priority, $status, $due_date);
} else {
    $stmt = mysqli_prepare($conn, "INSERT INTO tasks (user_id, title, description, priority, status) VALUES (?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, 'issss', $user_id, $title, $description, $priority, $status);
}

if (mysqli_stmt_execute($stmt)) {
    echo json_encode(['status' => 'success', 'task_id' => mysqli_insert_id($conn)]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'DB error: ' . mysqli_error($conn)]);
}