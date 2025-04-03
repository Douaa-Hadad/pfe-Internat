<?php
session_start();
include '../connection.php';

if (!isset($_SESSION['student_cin'])) {
    header("Location: login.php");
    exit();
}

$student_cin = $_SESSION['student_cin'];

// ✅ Check if the student already has a room request
$checkRequestQuery = $conn->prepare("SELECT id, status FROM room_requests WHERE student_cin = ?");
$checkRequestQuery->bind_param("s", $student_cin);
$checkRequestQuery->execute();
$checkRequestQuery->store_result();

$requestExists = $checkRequestQuery->num_rows > 0;
$status = "";
$requestId = null;

if ($requestExists) {
    $checkRequestQuery->bind_result($requestId, $status);
    $checkRequestQuery->fetch();
}
$checkRequestQuery->close();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['room_id'])) {
    $room_id = $_POST['room_id'];

    if ($requestExists && $status === 'accepted') {
        // ✅ Update existing room request for accepted students
        $updateRequestQuery = $conn->prepare("UPDATE room_requests SET room_id = ?, status = 'pending', request_date = NOW() WHERE id = ?");
        $updateRequestQuery->bind_param("si", $room_id, $requestId);
        $updateRequestQuery->execute();
        $updateRequestQuery->close();
        
        $_SESSION['message'] = "Room change request submitted. Awaiting admin approval.";
    } elseif (!$requestExists) {
        // ✅ Insert a new request if no previous request exists
        $insertRequestQuery = $conn->prepare("INSERT INTO room_requests (student_cin, room_id, status, request_date) VALUES (?, ?, 'pending', NOW())");
        $insertRequestQuery->bind_param("ss", $student_cin, $room_id);
        $insertRequestQuery->execute();
        $insertRequestQuery->close();
        
        $_SESSION['message'] = "Room request submitted. Awaiting admin approval.";
    }

    header("Location: reserve_room.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['new_request'])) {
    // ✅ Delete the old rejected request
    if ($requestExists && $status === 'rejected') {
        $deleteRequestQuery = $conn->prepare("DELETE FROM room_requests WHERE id = ?");
        $deleteRequestQuery->bind_param("i", $requestId);
        $deleteRequestQuery->execute();
        $deleteRequestQuery->close();
    }

    // Redirect to the same page to allow submitting a new request
    header("Location: reserve_room.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['change_room'])) {
    // ✅ Reset the current request and allow the student to start a new one
    if ($requestExists && $status === 'accepted') {
        $resetRequestQuery = $conn->prepare("DELETE FROM room_requests WHERE id = ?");
        $resetRequestQuery->bind_param("i", $requestId);
        $resetRequestQuery->execute();
        $resetRequestQuery->close();

        $_SESSION['message'] = "Votre demande précédente a été réinitialisée. Veuillez sélectionner une nouvelle chambre.";
    }

    // Redirect to the same page to display the room list
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
$roomsQuery = $conn->prepare("SELECT r.room_number, r.dorm_id, d.name AS dorm_name, r.room_id, r.occupied_slots, r.capacity 
                              FROM rooms r 
                              JOIN dorms d ON r.dorm_id = d.id 
                              WHERE d.gender = ? AND r.occupied_slots < r.capacity 
                              ORDER BY d.name, r.room_number");
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
        .room-grid {
            display: grid; /* Use grid layout */
            grid-template-columns: repeat(4, auto); /* 4 rooms per row with auto width */
            gap: 20px; /* Space between cards */
            justify-content: center; /* Center the grid */
            margin: 20px; /* Add margin around the grid */
        }
        .room-card {
            background-color: #f4f4f4;
            padding: 15px; /* Slightly increase padding */
            border-radius: 8px; /* Slightly smaller border radius */
            cursor: pointer;
            transition: 0.3s;
            border: 2px solid #ccc; /* Persistent border */
            width: 140px; /* Slightly increase width */
            height: 110px; /* Slightly increase height */
            text-align: center; /* Center text inside the card */
            box-sizing: border-box; /* Include padding and border in width/height */
            overflow: hidden; /* Prevent overflow of content */
        }
        .room-card:hover {
            border-color: #007bff; /* Change border color on hover */
        }
        .room-card h4, .room-card p {
            margin: 8px 0; /* Add consistent spacing between elements */
            font-size: 14px; /* Increase font size for better readability */
            line-height: 1.4; /* Adjust line height for better spacing */
        }
    </style>
    <script>
        function selectRoom(roomId) {
            document.getElementById('room_id_input').value = roomId;
            document.getElementById('room_form').submit();
        }
    </script>
</head>
<body>
    <?php include 'sidebar.php'; ?>

    <?php if ($requestExists && $status === 'pending'): ?>
        <div class="message-container">
            <div class="message">
                Vous avez déjà une demande de chambre en attente. Veuillez attendre l'approbation de l'administration.
            </div>
        </div>
    <?php elseif ($requestExists && $status === 'rejected'): ?>
        <div class="message-container">
            <div class="message">
                Votre demande de chambre a été rejetée. Veuillez soumettre une nouvelle demande.
                <form action="reserve_room.php" method="POST" style="margin-top: 10px;">
                    <button type="submit" name="new_request" style="padding: 10px 20px; background-color: #28a745; color: white; border: none; border-radius: 5px; cursor: pointer; font-family: inherit; font-size: inherit;">
                        Soumettre une nouvelle demande
                    </button>
                </form>
            </div>
        </div>
    <?php else: ?>
        <?php if ($requestExists && $status === 'accepted'): ?>
            <div class="message-container">
                <div class="message">
                    Votre demande de chambre a été acceptée. Vous pouvez changer de chambre si nécessaire.
                    <form action="reserve_room.php" method="POST" style="margin-top: 10px;">
                        <button type="submit" name="change_room" style="padding: 10px 20px; background-color: #17a2b8; color: white; border: none; border-radius: 5px; cursor: pointer; font-family: inherit; font-size: inherit;">
                            Changer de chambre
                        </button>
                    </form>
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
                            <h4> <?php echo htmlspecialchars($room['room_number']); ?></h4>
                            <p>Dortoir : <?php echo htmlspecialchars($room['dorm_name']); ?></p>
                            <p>Occupé : <?php echo $room['occupied_slots']; ?>/<?php echo $room['capacity']; ?></p>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>Aucune chambre disponible pour le moment.</p>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</body>
</html>
