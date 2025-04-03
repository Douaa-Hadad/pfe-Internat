<?php
session_start();
include '../../connection.php';

// Redirect to login page if no session exists or user is not admin
if (!isset($_SESSION['user_type']) || $_SESSION['user_role'] !== 'dorm_manager') {
    header("Location: ../../login/login.php");
    exit();
}

// Fetch all dorm requests
$requestsQuery = $conn->prepare("
    SELECT id, name, email, city, status, created_at
    FROM dorm_applications
    ORDER BY created_at DESC
");

if (!$requestsQuery) {
    die("Error preparing query: " . $conn->error);
}

$requestsQuery->execute();
$requestsResult = $requestsQuery->get_result();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dorm Requests</title>
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
    </style>
</head>
<body>
    <?php include '../header.php'; ?>
    <?php include 'sidebar.php'; ?>
    <div class="main-content">
        <div class="table-container">
            <h2>Dorm Requests</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>City</th>
                        <th>Status</th>
                        <th>Request Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($request = $requestsResult->fetch_assoc()): ?>
                        <tr>
                        <td><?= htmlspecialchars($request['id']); ?></td>
                            <td><?= htmlspecialchars($request['name']); ?></td>
                            <td><?= htmlspecialchars($request['email']); ?></td>
                            <td><?= htmlspecialchars($request['city']); ?></td>
                            <td class="status-<?= strtolower($request['status']); ?>">
                                <?= htmlspecialchars($request['status']); ?>
                            </td>
                            <td><?= htmlspecialchars($request['created_at']); ?></td>
                            <td>
                                <?php if ($request['status'] === 'Pending'): ?>
                                    <form method="post" action="update_dorm_status.php" style="display:inline;">
                                        <input type="hidden" name="request_id" value="<?= htmlspecialchars($request['id']); ?>">
                                        <button type="submit" name="action" value="approve" class="btn-accept">Approve</button>
                                    </form>
                                    <form method="post" action="update_dorm_status.php" style="display:inline;">
                                        <input type="hidden" name="request_id" value="<?= htmlspecialchars($request['id']); ?>">
                                        <button type="submit" name="action" value="reject" class="btn-decline">Reject</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>