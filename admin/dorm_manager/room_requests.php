<?php
session_start();
include '../../connection.php';

// Rediriger vers la page de connexion si aucune session n'existe ou si l'utilisateur n'est pas un gestionnaire de dortoir
if (!isset($_SESSION['user_type']) || $_SESSION['user_role'] !== 'dorm_manager') {
    header("Location: ../../login/login.php");
    exit();
}

// Récupérer toutes les demandes de chambre
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
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demandes de chambres</title>
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

        .btn-assign-room {
            background-color: #4CAF50; /* Green */
            color: white;
            border: none;
            padding: 10px 15px;
            cursor: pointer;
            border-radius: 5px;
            font-size: 14px;
        }

        .btn-assign-room:hover {
            background-color: #45a049; /* Darker green */
        }
    </style>
</head>
<body>
    <?php include '../header.php'; ?>
    <?php include 'sidebar.php'; ?>
    <div class="main-content">
        <div class="table-container">
            <h2>Demandes de chambres</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID de la demande</th>
                        <th>CIN de l'étudiant</th>
                        <th>Nom de l'étudiant</th>
                        <th>Genre</th>
                        <th>Statut</th>
                        <th>Date de la demande</th>
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
                                <button type="button" class="btn-assign-room" onclick="openRoomModal('<?= htmlspecialchars($request['student_cin']); ?>', '<?= htmlspecialchars($request['id']); ?>')">
                                    Assigner une chambre
                                </button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal pour assigner des chambres -->
    <div id="roomModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeRoomModal()">&times;</span>
            <h2>Assigner une chambre</h2>
            <input type="hidden" id="modalStudentCin" name="student_cin">
            <input type="hidden" id="modalRequestId" name="request_id">
            <table>
                <thead>
                    <tr>
                        <th>ID de la chambre</th>
                        <th>Numéro de chambre</th>
                        <th>ID du dortoir</th>
                        <th>Capacité</th>
                        <th>Places occupées</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="roomTableBody">
                    <!-- Les lignes des chambres seront ajoutées dynamiquement -->
                </tbody>
            </table>
        </div>
    </div>

    <script>
        function openRoomModal(studentCin, requestId) {
            document.getElementById('modalStudentCin').value = studentCin;
            document.getElementById('modalRequestId').value = requestId;

            // Récupérer les chambres disponibles dynamiquement
            fetch(`fetch_rooms.php?student_cin=${encodeURIComponent(studentCin)}`)
                .then(response => response.json())
                .then(data => {
                    const roomTableBody = document.getElementById('roomTableBody');
                    roomTableBody.innerHTML = ''; // Effacer les lignes existantes

                    data.forEach(room => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${room.room_id}</td>
                            <td>${room.room_number}</td>
                            <td>${room.dorm_id}</td>
                            <td>${room.capacity}</td>
                            <td>${room.occupied_slots}</td>
                            <td><button onclick="assignRoom('${room.room_number}', ${room.dorm_id})">Assigner</button></td>
                        `;
                        roomTableBody.appendChild(row);
                    });
                })
                .catch(error => {
                    console.error('Erreur lors de la récupération des chambres :', error);
                    alert('Échec de la récupération des chambres disponibles.');
                });

            document.getElementById('roomModal').style.display = 'block';
        }

        function closeRoomModal() {
            document.getElementById('roomModal').style.display = 'none';
        }

        function assignRoom(roomNumber, dormId) {
            const studentCin = document.getElementById('modalStudentCin').value;
            const requestId = document.getElementById('modalRequestId').value;

            const composedKey = `${roomNumber}-${dormId}`; // Créer la clé composée

            fetch('assign_room.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `cin=${encodeURIComponent(studentCin)}&room_id=${encodeURIComponent(composedKey)}&request_id=${encodeURIComponent(requestId)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.message) {
                    alert(data.message); // Afficher le message de succès
                } else if (data.error) {
                    alert("Erreur : " + data.error); // Afficher le message d'erreur détaillé
                } else {
                    alert("Une erreur inconnue s'est produite.");
                }
                closeRoomModal();
                location.reload(); // Recharger la page pour refléter les changements
            })
            .catch(error => {
                console.error("Erreur :", error);
                alert("Une erreur s'est produite lors de l'assignation de la chambre.");
            });
        }
    </script>
</body>
</html>
