<?php
require_once '../connection.php';

$sql = "SELECT id, subject, message FROM complaints WHERE status = 'Pending' ORDER BY created_at DESC LIMIT 5";
$result = $conn->query($sql);

$notifications = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $notifications[] = $row;
    }
}

echo json_encode($notifications);
?>
