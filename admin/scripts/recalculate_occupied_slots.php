<?php
session_start();
include '../../connection.php';

// Ensure the user is an admin or dorm manager
if (!isset($_SESSION['user_type']) || $_SESSION['user_role'] !== 'dorm_manager') {
    http_response_code(403);
    echo 'Unauthorized access';
    exit();
}

// Call the stored procedure to recalculate occupied slots
$callProcedureQuery = $conn->prepare("CALL RecalculateOccupiedSlots()");
if ($callProcedureQuery->execute()) {
    echo 'Occupied slots recalculated successfully for all rooms.';
} else {
    echo 'Failed to recalculate occupied slots.';
}
$callProcedureQuery->close();
$conn->close();
?>
