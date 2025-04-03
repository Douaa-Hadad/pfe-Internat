<?php
session_start();
include '../../connection.php';

// Redirect to login page if no session exists or user is not admin
if (!isset($_SESSION['user_type']) || $_SESSION['user_role'] !== 'request') {
    header("Location: ../../login/login.php");
    exit();
}

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
    <title>Demandes de Chambres</title>
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
            <h2>Demandes de Chambres</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID de Demande</th>
                        <th>CIN Étudiant</th>
                        <th>Nom Étudiant</th>
                        <th>ID Chambre</th>
                        <th>Statut</th>
                        <th>Date de Demande</th>
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
                                    <button type="submit" name="action" value="accept" class="btn-accept">Accepter</button>
                                </form>
                                <form method="post" action="update_request_status.php" style="display:inline;">
                                    <input type="hidden" name="request_id" value="<?= htmlspecialchars($request['id']); ?>">
                                    <button type="submit" name="action" value="decline" class="btn-decline">Refuser</button>
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
