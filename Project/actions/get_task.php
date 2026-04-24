<?php
require_once '../config/db.php';
header('Content-Type: application/json');
requireLogin();

$user_id = (int)$_SESSION['user_id'];
$task_id = (int)($_GET['task_id'] ?? 0);

if (!$task_id) { echo json_encode(null); exit(); }

$stmt = mysqli_prepare($conn, "SELECT * FROM tasks WHERE id=? AND user_id=?");
mysqli_stmt_bind_param($stmt, 'ii', $task_id, $user_id);
mysqli_stmt_execute($stmt);
$task = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
echo json_encode($task ?: null);