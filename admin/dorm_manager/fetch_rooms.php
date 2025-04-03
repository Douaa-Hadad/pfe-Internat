<?php
session_start();
include '../../connection.php';

if (!isset($_SESSION['user_type']) || $_SESSION['user_role'] !== 'dorm_manager') {
    http_response_code(403);
    echo json_encode(["error" => "Unauthorized access"]);
    exit();
}

$studentCin = $_GET['student_cin'] ?? null; // Get student CIN from the request
if (!$studentCin) {
    http_response_code(400);
    echo json_encode(["error" => "Student CIN is required"]);
    exit();
}

// Fetch the student's gender
$genderQuery = $conn->prepare("SELECT gender FROM students WHERE cin = ?");
$genderQuery->bind_param("s", $studentCin);
$genderQuery->execute();
$genderResult = $genderQuery->get_result();
if ($genderResult->num_rows === 0) {
    http_response_code(404);
    echo json_encode(["error" => "Student not found"]);
    exit();
}
$studentGender = $genderResult->fetch_assoc()['gender'];
$genderQuery->close();

// Fetch rooms based on gender and specific dorm IDs
if ($studentGender === 'male') {
    $roomsQuery = $conn->prepare("
        SELECT room_id, room_number, dorm_id, capacity, occupied_slots
        FROM rooms
        WHERE occupied_slots < capacity AND dorm_id = 3
        ORDER BY dorm_id, room_number
    ");
} else if ($studentGender === 'female') {
    $roomsQuery = $conn->prepare("
        SELECT room_id, room_number, dorm_id, capacity, occupied_slots
        FROM rooms
        WHERE occupied_slots < capacity AND dorm_id IN (1, 2)
        ORDER BY dorm_id, room_number
    ");
}

$roomsQuery->execute();
$result = $roomsQuery->get_result();

$rooms = [];
while ($row = $result->fetch_assoc()) {
    $rooms[] = $row;
}

header('Content-Type: application/json');
echo json_encode($rooms);

$roomsQuery->close();
$conn->close();
