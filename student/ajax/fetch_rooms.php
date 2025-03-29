<?php
include '../../connection.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json'); // ✅ Ensure JSON output

if (isset($_GET['dorm_id'])) {  // ✅ Removed "floor" requirement
    $dorm_id = $_GET['dorm_id'];
    $floor = isset($_GET['floor']) ? $_GET['floor'] : null;

    $query = "SELECT room_id, room_number, floor, (capacity - occupied_slots) AS available_slots FROM rooms WHERE dorm_id = ?";
    $params = [$dorm_id];
    $types = "i";

    if ($floor !== null) {
        $query .= " AND floor = ?";
        $params[] = $floor;
        $types .= "i";
    }

    $roomQuery = $conn->prepare($query);
    $roomQuery->bind_param($types, ...$params);
    $roomQuery->execute();
    $result = $roomQuery->get_result();

    $rooms = [];
    while ($row = $result->fetch_assoc()) {
        $rooms[] = $row;
    }

    echo json_encode($rooms);
} else {
    echo json_encode(["error" => "Missing dorm_id"]);
}
?>
