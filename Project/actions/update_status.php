<?php
require_once '../config/db.php';
header('Content-Type: application/json');
requireLogin();

$user_id = (int)$_SESSION['user_id'];
$task_id = (int)($_POST['task_id'] ?? 0);
$status  = trim($_POST['status'] ?? '');

$valid = ['todo', 'inprogress', 'done'];
if (!$task_id || !in_array($status, $valid)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid data']);
    exit();
}

$stmt = mysqli_prepare($conn, "UPDATE tasks SET status=? WHERE id=? AND user_id=?");
mysqli_stmt_bind_param($stmt, 'sii', $status, $task_id, $user_id);
echo json_encode(mysqli_stmt_execute($stmt)
    ? ['status' => 'success']
    : ['status' => 'error', 'message' => mysqli_error($conn)]);