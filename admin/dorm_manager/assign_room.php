<?php
session_start();
include '../db.php'; 

if (!isset($_SESSION['username'])) {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cin = $_POST['cin']; 
    $room_id = $_POST['room_id'];

    // Check if room has available space
    $room_check_sql = "SELECT capacity, occupied_slots FROM rooms WHERE id = ?";
    $stmt = $conn->prepare($room_check_sql);
    $stmt->bind_param("i", $room_id);
    $stmt->execute();
    $room = $stmt->get_result()->fetch_assoc();
    
    if ($room['occupied_slots'] < $room['capacity']) {
        // Assign room to student
        $assign_sql = "UPDATE students SET room_id = ? WHERE cin = ?";
        $stmt = $conn->prepare($assign_sql);
        $stmt->bind_param("is", $room_id, $cin);
        $stmt->execute();

        // Update occupied slots in the room
        $update_room_sql = "UPDATE rooms SET occupied_slots = occupied_slots + 1 WHERE id = ?";
        $stmt = $conn->prepare($update_room_sql);
        $stmt->bind_param("i", $room_id);
        $stmt->execute();

        header("Location: student_management.php?success=Room assigned successfully");
        exit();
    } else {
        header("Location: assign_room.php?cin=$cin&error=Room is full");
        exit();
    }
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
                <button type="submit" class="search-btn">Assign Room</button>
            </div>
        </form>
    </div>
</body>
</html>
