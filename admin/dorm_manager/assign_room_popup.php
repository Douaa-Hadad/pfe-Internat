<?php
session_start();
include '../../connection.php';

// Ensure the user is authorized
if (!isset($_SESSION['user_type']) || $_SESSION['user_role'] !== 'dorm_manager') {
    http_response_code(403); // Forbidden
    echo json_encode(["error" => "Unauthorized access"]);
    exit();
}

// Check if request is POST and has required parameters
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['cin'], $_POST['room_id'], $_POST['request_id'])) {
    $cin = $_POST['cin'];
    $room_id = $_POST['room_id'];
    $request_id = $_POST['request_id'];

    // Validate room_id format
    if (!strpos($room_id, '-')) {
        echo json_encode(["error" => "Invalid room ID format"]);
        exit();
    }

    // Fetch the student's gender
    $genderQuery = $conn->prepare("SELECT gender FROM students WHERE cin = ?");
    $genderQuery->bind_param("s", $cin);
    $genderQuery->execute();
    $genderResult = $genderQuery->get_result();
    if ($genderResult->num_rows === 0) {
        echo json_encode(["error" => "Student not found"]);
        exit();
    }
    $studentGender = $genderResult->fetch_assoc()['gender'];
    $genderQuery->close();

    // Verify if the room exists, has available slots, and matches the student's gender and dorm ID restrictions
    list($room_number, $dorm_id) = explode('-', $room_id);
    if ($studentGender === 'male') {
        $roomQuery = $conn->prepare("
            SELECT room_number, dorm_id 
            FROM rooms 
            WHERE room_number = ? AND dorm_id = ? AND occupied_slots < capacity AND dorm_id = 3
        ");
    } else if ($studentGender === 'female') {
        $roomQuery = $conn->prepare("
            SELECT room_number, dorm_id 
            FROM rooms 
            WHERE room_number = ? AND dorm_id = ? AND occupied_slots < capacity AND dorm_id IN (1, 2)
        ");
    }

    $roomQuery->bind_param("si", $room_number, $dorm_id);
    if (!$roomQuery->execute()) {
        echo json_encode(["error" => "Failed to verify room: " . $roomQuery->error]);
        exit();
    }
    $roomResult = $roomQuery->get_result();

    if ($roomResult->num_rows === 0) {
        echo json_encode(["error" => "Invalid room, no available slots, or gender mismatch"]);
        exit();
    }

    // Assign room in the database
    $updateRequestQuery = $conn->prepare("UPDATE room_requests SET status = 'Accepted', room_id = ? WHERE id = ?");
    $updateRequestQuery->bind_param("si", $room_id, $request_id);
    if (!$updateRequestQuery->execute()) {
        echo json_encode(["error" => "Failed to update room request: " . $updateRequestQuery->error]);
        exit();
    }

    // Update the student's room_id
    $updateStudentQuery = $conn->prepare("UPDATE students SET room_id = ? WHERE cin = ?");
    $updateStudentQuery->bind_param("ss", $room_id, $cin);
    if (!$updateStudentQuery->execute()) {
        echo json_encode(["error" => "Failed to assign student to room: " . $updateStudentQuery->error]);
        exit();
    }

    echo json_encode(["message" => "Room assigned successfully"]);
    exit();
} else {
    http_response_code(400); // Bad Request
    echo json_encode(["error" => "Invalid request"]);
}

$conn->close();
exit();
