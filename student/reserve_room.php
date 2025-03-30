<?php
session_start();
include '../connection.php';

if (!isset($_SESSION['student_cin'])) {
    header("Location: login.php");
    exit();
}

$student_cin = $_SESSION['student_cin'];

// ✅ Check if the student already has a room request
$checkRequestQuery = $conn->prepare("SELECT status FROM room_requests WHERE student_cin = ?");
$checkRequestQuery->bind_param("s", $student_cin);
$checkRequestQuery->execute();
$checkRequestQuery->store_result();

$requestExists = $checkRequestQuery->num_rows > 0;
$status = "";
if ($requestExists) {
    $checkRequestQuery->bind_result($status);
    $checkRequestQuery->fetch();
}
$checkRequestQuery->close();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['room_id']) && !$requestExists) {
    $room_id = $_POST['room_id'];

    // Insert a new room request
    $insertRequestQuery = $conn->prepare("INSERT INTO room_requests (student_cin, room_id, status) VALUES (?, ?, 'Pending')");
    $insertRequestQuery->bind_param("ss", $student_cin, $room_id);
    $insertRequestQuery->execute();
    $insertRequestQuery->close();
    
    $_SESSION['message'] = "Room request submitted and is pending admin approval.";
    header("Location: reserve_room.php");
    exit();
}

// Fetch user gender
$genderQuery = $conn->prepare("SELECT gender FROM students WHERE cin = ?");
$genderQuery->bind_param("s", $student_cin);
$genderQuery->execute();
$genderResult = $genderQuery->get_result();
$userGender = $genderResult->fetch_assoc()['gender'];
$genderQuery->close();

// Fetch available rooms filtered by gender
$roomsQuery = $conn->prepare("SELECT r.room_number, r.dorm_id, d.name AS dorm_name, r.room_id, r.occupied_slots, r.capacity FROM rooms r JOIN dorms d ON r.dorm_id = d.id WHERE d.gender = ? AND r.occupied_slots < r.capacity ORDER BY d.name, r.room_number");
$roomsQuery->bind_param("s", $userGender);
$roomsQuery->execute();
$roomsResult = $roomsQuery->get_result();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reserve Room</title>
    <link rel="stylesheet" href="css/sidebar.css">
    <style>
        .message-container {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            text-align: center;
        }
        .message {
            background-color: #ffcccc;
            color: #cc0000;
            padding: 20px;
            border-radius: 8px;
            font-size: 18px;
            font-weight: bold;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <?php if ($requestExists && $status === 'Pending'): ?>
        <div class="message-container">
            <div class="message">
                You already have a <?php echo htmlspecialchars($status); ?> room request. Please wait for admin approval.
            </div>
        </div>
    <?php else: ?>
        <?php if ($requestExists && $status === 'Accepted'): ?>
            <div class="message-container">
                <div class="message">
                    Your room request has been <?php echo htmlspecialchars($status); ?>. You can now change your room if needed.
                </div>
            </div>
        <?php endif; ?>
        <div class="content">
            <form id="room_form" action="reserve_room.php" method="POST">
                <input type="hidden" id="room_id_input" name="room_id">
            </form>
            <div class="room-grid">
                <?php if ($roomsResult->num_rows > 0): ?>
                    <?php while ($room = $roomsResult->fetch_assoc()): ?>
                        <div class="room-card" onclick="selectRoom('<?php echo $room['room_id']; ?>')">
                            <h4>Room <?php echo htmlspecialchars($room['room_number']); ?></h4>
                            <p>Occupied: <?php echo $room['occupied_slots']; ?>/<?php echo $room['capacity']; ?></p>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>No rooms available at the moment.</p>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</body>
</html>
