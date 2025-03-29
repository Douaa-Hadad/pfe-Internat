<?php
session_start();
include '../connection.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

// Fetch all room requests
$requestsQuery = $conn->prepare("
    SELECT rr.id, rr.student_cin, s.name AS student_name, rr.room_id, rr.status, rr.request_date
    FROM room_requests rr
    JOIN students s ON rr.student_cin = s.cin
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
    <link rel="stylesheet" href="../css/admin.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
        }
        .container {
            padding: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #007bff;
            color: white;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
        .status-pending {
            color: orange;
        }
        .status-accepted {
            color: green;
        }
        .status-rejected {
            color: red;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Room Requests</h1>
        <table>
            <thead>
                <tr>
                    <th>Request ID</th>
                    <th>Student CIN</th>
                    <th>Student Name</th>
                    <th>Room ID</th>
                    <th>Status</th>
                    <th>Request Date</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($request = $requestsResult->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($request['id']); ?></td>
                        <td><?php echo htmlspecialchars($request['student_cin']); ?></td>
                        <td><?php echo htmlspecialchars($request['student_name']); ?></td>
                        <td><?php echo htmlspecialchars($request['room_id']); ?></td>
                        <td class="status-<?php echo strtolower($request['status']); ?>">
                            <?php echo htmlspecialchars($request['status']); ?>
                        </td>
                        <td><?php echo htmlspecialchars($request['request_date']); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
