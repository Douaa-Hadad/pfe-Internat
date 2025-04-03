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
            // Update the room request status to 'rejected'
            $updateRequestQuery = $conn->prepare("
                UPDATE room_requests 
                SET status = 'rejected' 
                WHERE id = ?
            ");
            $updateRequestQuery->bind_param("i", $requestId);
            if ($updateRequestQuery->execute()) {
                // Increment the occupied slots for the room
                $incrementRoomSlotsQuery = $conn->prepare("
                    UPDATE rooms 
                    SET occupied_slots = occupied_slots + 1 
                    WHERE id = ?
                ");
                $incrementRoomSlotsQuery->bind_param("s", $request['room_id']);
                if ($incrementRoomSlotsQuery->execute()) {
                    // Log success for debugging
                    error_log("Occupied slots incremented for Room ID " . $request['room_id']);
                } else {
                    // Log failure for debugging
                    error_log("Failed to increment occupied slots for Room ID " . $request['room_id'] . ": " . $incrementRoomSlotsQuery->error);
                }

                // Log success for debugging
                error_log("Request ID $requestId successfully declined.");
            } else {
                // Log failure for debugging
                error_log("Failed to decline Request ID $requestId: " . $updateRequestQuery->error);
            }

            // Redirect back to the room requests page
            header("Location: room_requests.php");
            exit();
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
