<?php
session_start();
include '../connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $requestId = $_POST['request_id'];
    $action = $_POST['action'];

    if ($action === 'accept') {
        $status = 'Accepted';

        // Fetch the room_id and student_cin for the request
        $fetchRequestQuery = $conn->prepare("
            SELECT room_id, student_cin 
            FROM room_requests 
            WHERE id = ?
        ");
        $fetchRequestQuery->bind_param('i', $requestId);
        $fetchRequestQuery->execute();
        $fetchRequestQuery->bind_result($roomId, $studentCin);
        $fetchRequestQuery->fetch();
        $fetchRequestQuery->close();

        // Update the room's occupied slots
        $updateRoomQuery = $conn->prepare("
            UPDATE rooms 
            SET occupied_slots = occupied_slots + 1 
            WHERE room_id = ?
        ");
        $updateRoomQuery->bind_param('s', $roomId);
        $updateRoomQuery->execute();
        $updateRoomQuery->close();

        // Assign the room to the student
        $assignRoomQuery = $conn->prepare("
            UPDATE students 
            SET room_id = ? 
            WHERE cin = ?
        ");
        $assignRoomQuery->bind_param('ss', $roomId, $studentCin);
        $assignRoomQuery->execute();
        $assignRoomQuery->close();
    } elseif ($action === 'decline') {
        $status = 'Declined';
    } else {
        header('Location: room_requests.php');
        exit;
    }

    // Update the request status
    $updateRequestQuery = $conn->prepare("
        UPDATE room_requests 
        SET status = ? 
        WHERE id = ?
    ");
    $updateRequestQuery->bind_param('si', $status, $requestId);
    $updateRequestQuery->execute();
    $updateRequestQuery->close();

    $conn->close();
    header('Location: room_requests.php');
    exit;
}
?>
