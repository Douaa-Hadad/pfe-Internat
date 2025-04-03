<?php
session_start();
include '../../db.php'; // Updated path to include the correct database connection file

// Redirect to login page if no session exists
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: ../login/login.php");
    exit();
}

include 'sidebar.php';

// Fetch room and student information with GROUP_CONCAT to avoid duplicate rows
$rooms_query = "SELECT rooms.room_number, rooms.floor, rooms.capacity, rooms.occupied_slots, rooms.dorm_id, 
                       dorms.name AS dorm_name,  -- Fetch the dorm name
                       COALESCE(GROUP_CONCAT(students.name SEPARATOR ', '), 'No Students') AS student_names
                FROM rooms 
                LEFT JOIN students ON rooms.room_id = students.room_id  -- Corrected column name
                LEFT JOIN dorms ON rooms.dorm_id = dorms.id  -- Join to get the dorm name
                GROUP BY rooms.room_number, rooms.floor, rooms.capacity, rooms.occupied_slots, rooms.dorm_id, dorms.name
                ORDER BY rooms.dorm_id, rooms.room_number";
$rooms_result = $conn->query($rooms_query);

if (!$rooms_result) {
    die("Query failed: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>

        .rooms-table {
            width: 100%;
            max-width: 1100px;
            margin-top: 100px; /* Default margin for when opened */
            background: white;
            padding: 40px;
            padding-right: 20px;
            border-radius: 10px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
            overflow-x: auto;
            display: flex;
            flex-wrap: nowrap;
            justify-content: center;
            transition: margin-top 0.3s ease; /* Smooth transition for margin changes */
        }

        .rooms-table.opened {
            margin-top: 300px; /* Add more space when opened */
        }

        .dorm-section {
            margin-right: 20px;
            flex: 1;
        }

        .dorm-section h3 {
            text-align: center;
            color: #1b6ca8;
        }

        .room-card {
            display: none; /* Initially hide all room cards */
            width: 30%;
            height: 50px;
            margin: 1%;
            background: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 5px;
            text-align: center;
            box-shadow: 0px 2px 4px rgba(0, 0, 0, 0.1);
        }

        .room-card h3 {
            margin: 10px 0;
            color: #1b6ca8;
            font-size: 14px;
        }

        .room-card button {
            width: 100%;
            height: 50px;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .room-card button:hover {
            background-color:rgb(154, 154, 205);
        }

        .room-info {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 40px; /* Increase padding */
            border-radius: 10px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
            z-index: 1000;
            width: 40%; /* Increase width */
            max-width: 800px; /* Set a maximum width */
        }

        .room-info h3 {
            margin-top: 0;
        }

        .close-btn {
            color: #1b6ca8;
            font-size: 20px;
            border: none;
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
            float: right;
        }

        .close-btn:hover {
            color:rgb(76, 76, 137);
        }

        .overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }

        .floor-buttons {
            margin-top: 20px;
            text-align: center;
        }

        .floor-buttons button {
            margin: 5px;
            padding: 10px 20px;
            background-color: #1b6ca8;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .floor-buttons button:hover {
            background-color: rgb(154, 154, 205);
        }
    </style>
</head>
<body>
<?php include '../header.php'; ?> <!-- Updated path to header.php -->

    <div class="main-content">
        <div class="rooms-table">
            <?php 
            $current_dorm = '';
            while ($row = $rooms_result->fetch_assoc()): 
                if ($current_dorm !== $row['dorm_id']):
                    if ($current_dorm !== ''): ?>
                        </div>
                    <?php endif; 
                    $current_dorm = $row['dorm_id']; ?>
                    <div class="dorm-section">
                    <h3><?php echo htmlspecialchars($row['dorm_name']); ?></h3> <!-- Display dorm name here -->
                    <div class="floor-buttons">
                        <button onclick="toggleRooms('<?php echo $row['dorm_id']; ?>', 'all')">All Floors</button>
                        <button onclick="toggleRooms('<?php echo $row['dorm_id']; ?>', '1')">Floor 1</button>
                        <button onclick="toggleRooms('<?php echo $row['dorm_id']; ?>', '2')">Floor 2</button>
                        <button onclick="toggleRooms('<?php echo $row['dorm_id']; ?>', '3')">Floor 3</button>
                        <!-- Add more buttons for additional floors as needed -->
                    </div>
                <?php endif; ?>
                <div class="room-card" data-dorm="<?php echo htmlspecialchars($row['dorm_id']); ?>" data-floor="<?php echo htmlspecialchars($row['floor']); ?>">
                    <button onclick="showRoomInfo('<?php echo htmlspecialchars($row['room_number']); ?>', '<?php echo htmlspecialchars($row['floor']); ?>', '<?php echo htmlspecialchars($row['capacity']); ?>', '<?php echo htmlspecialchars($row['occupied_slots']); ?>', '<?php echo htmlspecialchars($row['student_names']); ?>')">
                        <h3>Room <?php echo htmlspecialchars($row['room_number']); ?></h3>
                    </button>
                </div>
            <?php endwhile; ?>
            </div>
        </div>
        <div id="overlay" class="overlay" onclick="closeRoomInfo()"></div>
        <div id="room-info" class="room-info">
            <button class="close-btn" onclick="closeRoomInfo()">X</button>
            <h3>Room Information</h3>
            <p id="room-number"></p>
            <p id="room-floor"></p>
            <p id="room-capacity"></p>
            <p id="room-occupied"></p>
            <p id="room-students"></p>
        </div>
    </div>

    <script>
        function toggleRooms(dormId, floor) {
            var roomCards = document.querySelectorAll('.room-card[data-dorm="' + dormId + '"]');
            var roomsTable = document.querySelector('.rooms-table');
            var isAnyVisible = false;

            for (var i = 0; i < roomCards.length; i++) {
                var roomCard = roomCards[i];
                var roomFloor = roomCard.getAttribute('data-floor');
                if (floor === 'all' || roomFloor === floor) {
                    roomCard.style.display = roomCard.style.display === 'none' ? 'inline-block' : 'none';
                    if (roomCard.style.display === 'inline-block') isAnyVisible = true;
                } else {
                    roomCard.style.display = 'none';
                }
            }

            // Adjust margin based on visibility
            if (isAnyVisible) {
                roomsTable.classList.add('opened');
            } else {
                roomsTable.classList.remove('opened');
            }
        }

        function showRoomInfo(roomNumber, floor, capacity, occupiedSlots, studentNames) {
            document.getElementById('room-number').innerText = 'Room Number: ' + roomNumber;
            document.getElementById('room-floor').innerText = 'Floor: ' + floor;
            document.getElementById('room-capacity').innerText = 'Capacity: ' + capacity;
            document.getElementById('room-occupied').innerText = 'Occupied Slots: ' + occupiedSlots;
            document.getElementById('room-students').innerText = 'Students: ' + studentNames;
            document.getElementById('room-info').style.display = 'block';
            document.getElementById('overlay').style.display = 'block';
        }

        function closeRoomInfo() {
            document.getElementById('room-info').style.display = 'none';
            document.getElementById('overlay').style.display = 'none';
        }
    </script>
    
    <script src="script.js"></script>
</body>
</html>
