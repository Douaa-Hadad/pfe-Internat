<?php
session_start();
include '../../connection.php';

// Redirect to login page if no session exists or user is not admin
if (!isset($_SESSION['user_type']) || $_SESSION['user_role'] !== 'dorm_manager') {
    header("Location: ../../login/login.php");
    exit();
}

// Fetch all room requests
$requestsQuery = $conn->prepare("
    SELECT rr.id, rr.student_cin, s.name AS student_name, s.gender, rr.room_id, rr.status, rr.request_date
    FROM room_requests rr
    JOIN students s ON rr.student_cin = s.cin
    WHERE rr.status != 'Accepted'
    ORDER BY rr.request_date DESC
");
$requestsQuery->execute();
$requestsResult = $requestsQuery->get_result();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Room Requests</title>
    <link rel="stylesheet" href="../styles.css">
    <style>
        .btn-accept {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 5px 10px;
            cursor: pointer;
            border-radius: 3px;
        }

        .btn-accept:hover {
            background-color: #45a049;
        }

        .btn-decline {
            background-color: #f44336;
            color: white;
            border: none;
            padding: 5px 10px;
            cursor: pointer;
            border-radius: 3px;
        }

        .btn-decline:hover {
            background-color: #d32f2f;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 50%;
            border-radius: 10px;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <?php include '../header.php'; ?>
    <?php include 'sidebar.php'; ?>
    <div class="main-content">
        <div class="table-container">
            <h2>Room Requests</h2>
            <table>
                <thead>
                    <tr>
                        <th>Request ID</th>
                        <th>Student CIN</th>
                        <th>Student Name</th>
                        <th>Gender</th>
                        <th>Status</th>
                        <th>Request Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($request = $requestsResult->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($request['id']); ?></td>
                            <td><?= htmlspecialchars($request['student_cin']); ?></td>
                            <td><?= htmlspecialchars($request['student_name']); ?></td>
                            <td><?= htmlspecialchars($request['gender']); ?></td>
                            <td class="status-<?= strtolower($request['status']); ?>">
                                <?= htmlspecialchars($request['status']); ?>
                            </td>
                            <td><?= htmlspecialchars($request['request_date']); ?></td>
                            <td>
                                <button type="button" class="btn-accept" onclick="openRoomModal('<?= htmlspecialchars($request['student_cin']); ?>', '<?= htmlspecialchars($request['id']); ?>')">
                                    Assign Room
                                </button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal for assigning rooms -->
    <div id="roomModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeRoomModal()">&times;</span>
            <h2>Assign Room</h2>
            <input type="hidden" id="modalStudentCin" name="student_cin">
            <input type="hidden" id="modalRequestId" name="request_id">
            <table>
                <thead>
                    <tr>
                        <th>Room ID</th>
                        <th>Room Number</th>
                        <th>Dorm ID</th>
                        <th>Capacity</th>
                        <th>Occupied Slots</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="roomTableBody">
                    <!-- Room rows will be dynamically populated -->
                </tbody>
            </table>
        </div>
    </div>

    <script>
        function openRoomModal(studentCin, requestId) {
            document.getElementById('modalStudentCin').value = studentCin;
            document.getElementById('modalRequestId').value = requestId;

            // Fetch available rooms dynamically
            fetch(`fetch_rooms.php?student_cin=${encodeURIComponent(studentCin)}`)
                .then(response => response.json())
                .then(data => {
                    const roomTableBody = document.getElementById('roomTableBody');
                    roomTableBody.innerHTML = ''; // Clear existing rows

                    data.forEach(room => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${room.room_id}</td>
                            <td>${room.room_number}</td>
                            <td>${room.dorm_id}</td>
                            <td>${room.capacity}</td>
                            <td>${room.occupied_slots}</td>
                            <td><button onclick="assignRoom('${room.room_number}', ${room.dorm_id})">Assign</button></td>
                        `;
                        roomTableBody.appendChild(row);
                    });
                })
                .catch(error => {
                    console.error('Error fetching rooms:', error);
                    alert('Failed to fetch available rooms.');
                });

            document.getElementById('roomModal').style.display = 'block';
        }

        function closeRoomModal() {
            document.getElementById('roomModal').style.display = 'none';
        }

        function assignRoom(roomNumber, dormId) {
            const studentCin = document.getElementById('modalStudentCin').value;
            const requestId = document.getElementById('modalRequestId').value;

            const composedKey = `${roomNumber}-${dormId}`; // Create the composed key

            fetch('assign_room.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `cin=${encodeURIComponent(studentCin)}&room_id=${encodeURIComponent(composedKey)}&request_id=${encodeURIComponent(requestId)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.message) {
                    alert(data.message); // Show success message
                } else if (data.error) {
                    alert("Error: " + data.error); // Show detailed error message
                } else {
                    alert("An unknown error occurred.");
                }
                closeRoomModal();
                location.reload(); // Reload the page to reflect changes
            })
            .catch(error => {
                console.error("Error:", error);
                alert("An error occurred while assigning the room.");
            });
        }
    </script>
</body>
</html>
