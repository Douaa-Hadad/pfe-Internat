<?php
session_start();
include '../connection.php';

if (!isset($_SESSION['student_cin'])) {
    header("Location: login.php");
    exit();
}

$student_cin = $_SESSION['student_cin'];

// ✅ Check if the student already has a room request
$checkRequestQuery = $conn->prepare("
    SELECT status FROM room_requests 
    WHERE student_cin = ?
");
$checkRequestQuery->bind_param("s", $student_cin);
$checkRequestQuery->execute();
$checkRequestQuery->store_result();

if ($checkRequestQuery->num_rows > 0) {
    $checkRequestQuery->bind_result($status);
    $checkRequestQuery->fetch();

    // Redirect if the student has a pending or accepted request
    if ($status === 'Pending' || $status === 'Accepted') {
        $_SESSION['error_message'] = "You already have a room request. Please check your dashboard.";
        header("Location: dashboard.php");
        $checkRequestQuery->close();
        $conn->close();
        exit();
    }
}

$checkRequestQuery->close();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['room_id'])) {
    $room_id = $_POST['room_id'];

    // Insert a new room request
    $insertRequestQuery = $conn->prepare("
        INSERT INTO room_requests (student_cin, room_id, status) 
        VALUES (?, ?, 'Pending')
    ");
    $insertRequestQuery->bind_param("ss", $student_cin, $room_id);

    if ($insertRequestQuery->execute()) {
        $message = "Room request submitted and is pending admin approval.";
    } else {
        $message = "Error: " . $conn->error;
    }

    $insertRequestQuery->close();
}

// Fetch user gender
$genderQuery = $conn->prepare("SELECT gender FROM students WHERE cin = ?");
$genderQuery->bind_param("s", $student_cin);
$genderQuery->execute();
$genderResult = $genderQuery->get_result();
$userGender = $genderResult->fetch_assoc()['gender'];
$genderQuery->close();

// Fetch available rooms filtered by gender
$roomsQuery = $conn->prepare("
    SELECT r.room_number, r.dorm_id, d.name AS dorm_name, CONCAT(r.room_number, '-', r.dorm_id) AS room_id, r.occupied_slots, r.capacity
    FROM rooms r
    JOIN dorms d ON r.dorm_id = d.id
    WHERE d.gender = ? AND r.occupied_slots < r.capacity
    ORDER BY d.name, r.room_number
");
$roomsQuery->bind_param("s", $userGender);

if (!$roomsQuery) {
    die("Error preparing query: " . $conn->error);
}

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
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
            display: flex;
            height: 100vh;
        }
        .content {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px;
            margin-right: 120px;
            margin-left: 200px; /* Added left margin for the sidebar */
        }
        .dorm-section {
            width: 100%;
            margin-bottom: 30px;
        }
        .dorm-title {
            font-size: 20px;
            margin-bottom: 10px;
            text-align: left;
        }
        .room-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
        }
        .room-card {
            background: #ffffff;
            padding: 10px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            text-align: center;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
            font-size: 14px;
        }
        .room-card:hover {
            transform: scale(1.05);
            box-shadow: 0 6px 8px rgba(0, 0, 0, 0.15);
        }
        .room-card.disabled {
            background-color: #f8d7da;
            cursor: not-allowed;
        }
        .room-card h4 {
            margin: 0;
            font-size: 14px;
        }
        .room-card p {
            margin: 5px 0;
            font-size: 12px;
        }
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
        }
        .modal-content {
            background: #ffffff;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            width: 300px;
        }
        .modal-content h3 {
            margin: 0 0 10px;
        }
        .modal-content button {
            margin: 5px;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .modal-content .confirm {
            background-color: #007bff;
            color: #ffffff;
        }
        .modal-content .cancel {
            background-color: #f44336;
            color: #ffffff;
        }
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
    <script>
        function selectRoom(roomId, isDisabled) {
            if (isDisabled) {
                alert("This room is full or unavailable.");
                return;
            }
            const modal = document.getElementById('roomModal');
            document.getElementById('modalRoomId').value = roomId;
            modal.style.display = 'flex';
        }

        function confirmRoomSelection() {
            const roomId = document.getElementById('modalRoomId').value;
            document.getElementById('room_id_input').value = roomId;
            document.getElementById('room_form').submit();
        }

        function closeModal() {
            document.getElementById('roomModal').style.display = 'none';
        }
    </script>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <?php if (isset($message)): ?>
        <div class="message-container">
            <div class="message">
                <?php echo htmlspecialchars($message); ?>
            </div>
        </div>
    <?php else: ?>
        <div class="content">
            <form id="room_form" action="reserve_room.php" method="POST">
                <input type="hidden" id="room_id_input" name="room_id">
            </form>
            <div id="roomModal" class="modal">
                <div class="modal-content">
                    <h3>Do you want to select this room?</h3>
                    <input type="hidden" id="modalRoomId">
                    <button class="confirm" onclick="confirmRoomSelection()">Yes</button>
                    <button class="cancel" onclick="closeModal()">No</button>
                </div>
            </div>
            <?php 
            $currentDorm = null;
            while ($room = $roomsResult->fetch_assoc()): 
                if ($currentDorm !== $room['dorm_name']):
                    if ($currentDorm !== null): ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    <div class="dorm-section">
                        <div class="dorm-title"><?php echo htmlspecialchars($room['dorm_name']); ?></div>
                        <div class="room-grid">
                    <?php 
                    $currentDorm = $room['dorm_name'];
                endif; ?>
                <div 
                    class="room-card <?php echo $room['occupied_slots'] >= $room['capacity'] ? 'disabled' : ''; ?>" 
                    onclick="selectRoom('<?php echo $room['room_id']; ?>', <?php echo $room['occupied_slots'] >= $room['capacity'] ? 'true' : 'false'; ?>)">
                    <h4>Room <?php echo htmlspecialchars($room['room_number']); ?></h4>
                    <p>Occupied: <?php echo $room['occupied_slots']; ?>/<?php echo $room['capacity']; ?></p>
                </div>
            <?php endwhile; ?>
            </div>
            </div>
        </div>
    <?php endif; ?>
</body>
</html>
