<?php
session_start();
include '../connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $requestId = $_POST['request_id'];
    $action = $_POST['action'];

    if ($action === 'accept') {
        $status = 'Accepted';
        // Optionally, add logic here to handle room assignment or other actions for accepted requests.
    } elseif ($action === 'decline') {
        $status = 'Declined';
    } else {
        header('Location: room_requests.php');
        exit;
    }

    $updateQuery = $conn->prepare("UPDATE room_requests SET status = ? WHERE id = ?");
    $updateQuery->bind_param('si', $status, $requestId);
    $updateQuery->execute();

    $updateQuery->close();
    $conn->close();
    header('Location: room_requests.php');
    exit;
}
?>
