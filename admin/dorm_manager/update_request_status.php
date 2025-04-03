<?php
session_start();
include '../../connection.php'; // Corrected relative path

// Redirect to login page if no session exists or user is not admin
if (!isset($_SESSION['user_type']) || $_SESSION['user_role'] !== 'dorm_manager') {
    header("Location: ../../login/login.php");
    exit();
}

// Check if request ID and action are set
if (isset($_POST['request_id'], $_POST['action'])) {
    $requestId = $_POST['request_id'];
    $action = $_POST['action'];

    // Debugging: Log the action and request ID
    error_log("Action: $action, Request ID: $requestId");

    // Fetch the room request details
    $requestQuery = $conn->prepare("
        SELECT student_cin, room_id 
        FROM room_requests 
        WHERE id = ?
    ");
    $requestQuery->bind_param("i", $requestId);
    $requestQuery->execute();
    $requestResult = $requestQuery->get_result();
    $request = $requestResult->fetch_assoc();

    if ($request) {
        if ($action === 'accept') {
            // Update the room request status to 'Accepted'
            $updateRequestQuery = $conn->prepare("
                UPDATE room_requests 
                SET status = 'Accepted' 
                WHERE id = ?
            ");
            $updateRequestQuery->bind_param("i", $requestId);
            $updateRequestQuery->execute();

            // Assign the room ID to the student in the students table
            $updateStudentQuery = $conn->prepare("
                UPDATE students 
                SET room_id = ? 
                WHERE cin = ?
            ");
            $updateStudentQuery->bind_param("ss", $request['room_id'], $request['student_cin']); // Fixed room_id type
            $updateStudentQuery->execute();
        } elseif ($action === 'decline') {
            // Update the room request status to 'Declined'
            $updateRequestQuery = $conn->prepare("
                UPDATE room_requests 
                SET status = 'Declined' 
                WHERE id = ?
            ");
            $updateRequestQuery->bind_param("i", $requestId);
            $updateRequestQuery->execute();
        }
    }
} else {
    // Debugging: Log invalid data
    error_log("Invalid data: Request ID or action not set.");
    // Redirect back to the room requests page if no valid data is provided
    header("Location: room_requests.php");
}

$conn->close();
header("Location: room_requests.php");
exit();
?>
