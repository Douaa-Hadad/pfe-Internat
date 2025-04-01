<?php
session_start();
include '../connection.php';

if (!isset($_SESSION['student_cin'])) {
    header("Location: login.php");
    exit();
}

$student_cin = $_SESSION['student_cin'];
$genderQuery = $conn->prepare("SELECT gender FROM students WHERE cin = ?");
$genderQuery->bind_param("s", $student_cin);
$genderQuery->execute();
$result = $genderQuery->get_result();
$student = $result->fetch_assoc();
$gender = $student['gender'];

$dormQuery = $conn->prepare("SELECT * FROM dorms WHERE gender = ?");
$dormQuery->bind_param("s", $gender);
$dormQuery->execute();
$dormResult = $dormQuery->get_result();

$newDormQuery = $conn->prepare("SELECT * FROM dorms WHERE gender = ?");
$newDormQuery->bind_param("s", $gender);
$newDormQuery->execute();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Choose Room</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container">
        <h2>Select Your Room</h2>

        <form action="reserve_room.php" method="POST">
            <label for="dorm">Choose Dorm:</label>
            <select name="dorm_id" id="dorm" onchange="fetchFloors()">
                <option value="">Select Dorm</option>
                <?php while ($dorm = $dormResult->fetch_assoc()): ?>
                    <option value="<?php echo $dorm['id']; ?>"><?php echo $dorm['name']; ?></option>
                <?php endwhile; ?>
            </select>

            <label for="floor">Choose Floor:</label>
            <select name="floor" id="floor" onchange="fetchRooms()">
                <option value="">Select Floor</option>
            </select>

            <label for="room">Choose Room:</label>
            <div id="room-list"></div>

            <label for="room">Choose Room:</label>
            <select name="room_number" id="room">
                <option value="">Select Room</option>
                <?php
                $roomQuery = $conn->prepare("SELECT room_number FROM rooms WHERE dorm_id = ?");
                $roomQuery->bind_param("i", $dorm_id);
                $roomQuery->execute();
                $roomResult = $roomQuery->get_result();
                while ($room = $roomResult->fetch_assoc()): ?>
                    <option value="<?php echo $room['room_number']; ?>"><?php echo $room['room_number']; ?></option>
                <?php endwhile; ?>
            </select>
            <button type="submit">Reserve Room</button>
        </form>

        <h2>Request Room Change</h2>
        <form action="request_room_change.php" method="POST">
            <label for="new_dorm">Choose New Dorm:</label>
            <select name="new_dorm_id" id="new_dorm" onchange="fetchFloorsForChange()">
                <option value="">Select Dorm</option>
                <?php
                $dormQuery->execute();
                $dormResult = $dormQuery->get_result();
                while ($dorm = $dormResult->fetch_assoc()): ?>
                    <option value="<?php echo $dorm['id']; ?>"><?php echo $dorm['name']; ?></option>
                <?php endwhile; ?>
            </select>

            <label for="new_floor">Choose New Floor:</label>
            <select name="new_floor" id="new_floor" onchange="fetchRoomsForChange()">
                <option value="">Select Floor</option>
            </select>

            <label for="new_room">Choose New Room:</label>
            <div id="new-room-list"></div>

            <input type="hidden" name="new_room_number" id="selected-new-room">
            <button type="submit">Request Room Change</button>
        </form>
    </div>

    <script src="../js/room-selection.js"></script>
</body>
</html>
