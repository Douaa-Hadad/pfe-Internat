<?php
session_start();
include '../connection.php';
include 'header.php';
include 'sidebar.php';

// Fetch all room requests
$requestsQuery = $conn->prepare("
    SELECT rr.id, rr.student_cin, s.name AS student_name, rr.room_id, rr.status, rr.request_date
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
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="main-content">
        <div class="table-container">
            <h2>Room Requests</h2>
            <table>
                <thead>
                    <tr>
                        <th>Request ID</th>
                        <th>Student CIN</th>
                        <th>Student Name</th>
                        <th>Room ID</th>
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
                            <td><?= htmlspecialchars($request['room_id']); ?></td>
                            <td class="status-<?= strtolower($request['status']); ?>">
                                <?= htmlspecialchars($request['status']); ?>
                            </td>
                            <td><?= htmlspecialchars($request['request_date']); ?></td>
                            <td>
                                <form method="post" action="update_request_status.php" style="display:inline;">
                                    <input type="hidden" name="request_id" value="<?= htmlspecialchars($request['id']); ?>">
                                    <button type="submit" name="action" value="accept">Accept</button>
                                </form>
                                <form method="post" action="update_request_status.php" style="display:inline;">
                                    <input type="hidden" name="request_id" value="<?= htmlspecialchars($request['id']); ?>">
                                    <button type="submit" name="action" value="decline">Decline</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
    <script src="script.js"></script>
</body>
</html>
