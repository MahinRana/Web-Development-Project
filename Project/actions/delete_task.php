<?php
require_once '../config/db.php';
header('Content-Type: application/json');
requireLogin();

$user_id = (int)$_SESSION['user_id'];
$task_id = (int)($_POST['task_id'] ?? 0);

if (!$task_id) { echo json_encode(['status' => 'error', 'message' => 'Invalid task']); exit(); }

$stmt = mysqli_prepare($conn, "DELETE FROM tasks WHERE id=? AND user_id=?");
mysqli_stmt_bind_param($stmt, 'ii', $task_id, $user_id);
echo json_encode(mysqli_stmt_execute($stmt) && mysqli_stmt_affected_rows($stmt) > 0
    ? ['status' => 'success']
    : ['status' => 'error', 'message' => 'Delete failed']);