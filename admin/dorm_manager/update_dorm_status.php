<?php
session_start();
include '../../connection.php';

if (!isset($_SESSION['user_type']) || $_SESSION['user_role'] !== 'dorm_manager') {
    header("Location: ../../login/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $requestId = $_POST['request_id'];
    $action = $_POST['action'];

    if ($action === 'approve') {
        $status = 'Approved';
    } elseif ($action === 'reject') {
        $status = 'Rejected';
    } else {
        die("Invalid action.");
    }

    $updateQuery = $conn->prepare("UPDATE dorm_applications SET status = ? WHERE id = ?");
    if (!$updateQuery) {
        die("Error preparing query: " . $conn->error);
    }

    $updateQuery->bind_param("si", $status, $requestId);
    if ($updateQuery->execute()) {
        $message = "Request successfully " . strtolower($status) . ".";
        header("Location: dorm_requests.php?message=" . urlencode($message));
    } else {
        die("Error updating record: " . $conn->error);
    }

    $updateQuery->close();
}

$conn->close();
?>
