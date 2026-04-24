<?php
require_once '../config/db.php';
requireLogin();

$user_id = $_SESSION['user_id'];
mysqli_query($conn, "DELETE FROM tasks WHERE user_id = $user_id");
mysqli_query($conn, "DELETE FROM users WHERE id = $user_id");
session_destroy();
header('Location: ../index.php?msg=account_deleted');
exit();
?>
