<?php
session_start();
include '../../connection.php';

// Rediriger vers la page de connexion si aucune session n'existe ou si l'utilisateur n'est pas administrateur
if (!isset($_SESSION['user_type']) || $_SESSION['user_role'] !== 'request') {
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
    <title>Demandes d'internat</title>
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
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background-color:rgba(192, 202, 224, 0.71);
            color: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            position: relative;
            width: 50%;
            max-width: 400px;
        }

        .modal-content .close-btn {
            position: absolute;
            top: 10px;
            right: 15px;
            color: white;
            font-size: 20px;
            cursor: pointer;
            border: none;
            background: none;
        }

        .modal-content .close-btn:hover {
            color: #ddd;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const urlParams = new URLSearchParams(window.location.search);
            const message = urlParams.get('message');
            const modal = document.querySelector('.modal');
            const modalMessage = document.querySelector('.modal-message');
            const closeBtn = document.querySelector('.modal-content .close-btn');

            if (message) {
                modalMessage.textContent = message;
                modal.style.display = 'flex';
            }

            if (closeBtn) {
                closeBtn.addEventListener('click', function () {
                    modal.style.display = 'none';
                });
            }
        });
    </script>
</head>
<body>
    <?php include '../header.php'; ?>
    <?php include 'sidebar.php'; ?>
    <div class="modal">
        <div class="modal-content">
            <button class="close-btn">&times;</button>
            <p class="modal-message"></p>
        </div>
    </div>
    <div class="main-content">
        <div class="table-container">
            <h2>Demandes d'internat</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom</th>
                        <th>Email</th>
                        <th>Ville</th>
                        <th>Statut</th>
                        <th>Date de demande</th>
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
                                        <button type="submit" name="action" value="approve" class="btn-accept">Approuver</button>
                                    </form>
                                    <form method="post" action="update_dorm_status.php" style="display:inline;">
                                        <input type="hidden" name="request_id" value="<?= htmlspecialchars($request['id']); ?>">
                                        <button type="submit" name="action" value="reject" class="btn-decline">Rejeter</button>
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