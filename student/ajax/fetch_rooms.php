<?php
include '../../connection.php';

if (isset($_GET['dorm_id'])) {
    $dorm_id = $_GET['dorm_id'];

    // Debugging: Print dorm_id to check if it's received correctly
    error_log("Received dorm_id: " . $dorm_id);

    $query = $conn->prepare("SELECT room_id, room_number, capacity - occupied_slots AS available_slots 
                             FROM rooms WHERE dorm_id = ? AND capacity > occupied_slots");
    
    if (!$query) {
        die("Query Preparation Failed: " . $conn->error);
    }

    $query->bind_param("i", $dorm_id);
    if (!$query->execute()) {
        die("Query Execution Failed: " . $query->error);
    }

    $result = $query->get_result();
    
    // Debugging: Check if any rows are fetched
    if ($result->num_rows === 0) {
        error_log("No rooms found for dorm_id: " . $dorm_id);
    }

    $rooms = [];
    while ($row = $result->fetch_assoc()) {
        $rooms[] = $row;
    }

    echo json_encode($rooms);
}
?>
