<?php
session_start();
include '../../connection.php';

if (!isset($_SESSION['user_type']) || $_SESSION['user_role'] !== 'dorm_manager') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized access']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cin = $_POST['cin'];
    $composed_key = $_POST['room_id']; // Composed key: room_number-dorm_id
    $request_id = $_POST['request_id'];

    // Validate the composed key format
    if (!strpos($composed_key, '-')) {
        echo json_encode(['error' => 'Invalid room identifier format']);
        exit();
    }

    // Split the composed key into room_number and dorm_id
    list($room_number, $dorm_id) = explode('-', $composed_key);

    // Fetch the student's gender
    $genderQuery = $conn->prepare("SELECT gender FROM students WHERE cin = ?");
    $genderQuery->bind_param("s", $cin);
    $genderQuery->execute();
    $genderResult = $genderQuery->get_result();
    if ($genderResult->num_rows === 0) {
        echo json_encode(['error' => 'Student not found']);
        exit();
    }
    $studentGender = $genderResult->fetch_assoc()['gender'];
    $genderQuery->close();

    // Verify if the room exists, has available slots, and matches the student's gender and dorm ID restrictions
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
        echo json_encode(['error' => 'Failed to verify room: ' . $roomQuery->error]);
        exit();
    }
    $roomResult = $roomQuery->get_result();

    if ($roomResult->num_rows === 0) {
        echo json_encode(['error' => 'Invalid room, no available slots, or gender mismatch']);
        exit();
    }

    $room = $roomResult->fetch_assoc();
    $room_id = $room['room_number'] . '-' . $room['dorm_id']; // Correctly format the room_id

    // Assign the student to the room
    $assignStudentQuery = $conn->prepare("UPDATE students SET room_id = ? WHERE cin = ?");
    $assignStudentQuery->bind_param("ss", $room_id, $cin);
    if (!$assignStudentQuery->execute()) {
        echo json_encode(['error' => 'Failed to assign student to room: ' . $assignStudentQuery->error]);
        exit();
    }
    $assignStudentQuery->close();

    // Mark the request as accepted
    $updateRequestQuery = $conn->prepare("UPDATE room_requests SET status = 'Accepted', room_id = ? WHERE id = ?");
    $updateRequestQuery->bind_param("ss", $room_id, $request_id);
    if (!$updateRequestQuery->execute()) {
        echo json_encode(['error' => 'Failed to update room request: ' . $updateRequestQuery->error]);
        exit();
    }
    $updateRequestQuery->close();

    // Recalculate the occupied slots for the room
    $recalculateSlotsQuery = $conn->prepare("
        UPDATE rooms r
        SET r.occupied_slots = (
            SELECT COUNT(*) FROM students s WHERE s.room_id = r.room_id
        )
        WHERE r.room_id = ?
    ");
    $recalculateSlotsQuery->bind_param("i", $room_id);
    if (!$recalculateSlotsQuery->execute()) {
        echo json_encode(['error' => 'Failed to recalculate room slots: ' . $recalculateSlotsQuery->error]);
        exit();
    }
    $recalculateSlotsQuery->close();

    echo json_encode(['message' => 'Room assigned successfully']);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../admin/styles.css">
    <title>Assign Room</title>
</head>
<body>
    <?php include 'header.php'; ?>
    <?php include 'sidebar.php'; ?>
    
    <div class="main-content">
        <h1>Assign Room to Student</h1>
        <form method="POST" action="">
            <div class="form-group">
                <label for="cin">CIN:</label>
                <input type="text" id="cin" name="cin" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="room_id">Room ID:</label>
                <input type="text" id="room_id" name="room_id" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="request_id">Request ID:</label>
                <input type="text" id="request_id" name="request_id" class="form-control" required>
            </div>
            <div class="form-group">
                <button type="submit" class="search-btn">Assign Room</button>
            </div>
        </form>
    </div>
</body>
</html>
