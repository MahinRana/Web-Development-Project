<?php
header('Content-Type: application/json');
echo json_encode(['status' => 'ok', 'message' => 'PHP is reachable', 'session' => session_status()]);