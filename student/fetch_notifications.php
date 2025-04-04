<?php
require_once '../connection.php';

$sql = "SELECT id, cin, message, type, is_read, created_at FROM notifications ORDER BY created_at DESC LIMIT 5";
$result = $conn->query($sql);

$notifications = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $notifications[] = $row;
    }
}

echo json_encode($notifications);
