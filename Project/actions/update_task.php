<?php
require_once '../config/db.php';
header('Content-Type: application/json');
requireLogin();

$user_id     = (int)$_SESSION['user_id'];
$task_id     = (int)($_POST['task_id'] ?? 0);
$title       = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');
$priority    = trim($_POST['priority'] ?? 'medium');
$status      = trim($_POST['status'] ?? 'todo');
$due_date    = !empty($_POST['due_date']) ? $_POST['due_date'] : null;

if (!$task_id || empty($title)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid data']);
    exit();
}

$title       = mysqli_real_escape_string($conn, $title);
$description = mysqli_real_escape_string($conn, $description);

$stmt = mysqli_prepare($conn, "UPDATE tasks SET title=?, description=?, priority=?, status=?, due_date=? WHERE id=? AND user_id=?");
mysqli_stmt_bind_param($stmt, 'sssssii', $title, $description, $priority, $status, $due_date, $task_id, $user_id);

if (mysqli_stmt_execute($stmt)) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'DB error: ' . mysqli_error($conn)]);
}