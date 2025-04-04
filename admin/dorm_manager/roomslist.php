<?php
session_start();
include '../../db.php'; // Chemin mis à jour pour inclure le fichier de connexion à la base de données

// Rediriger vers la page de connexion si aucune session n'existe
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: ../login/login.php");
    exit();
}

include 'sidebar.php';

// Récupérer les informations des chambres et des étudiants avec GROUP_CONCAT pour éviter les doublons
$rooms_query = "SELECT rooms.room_number, rooms.floor, rooms.capacity, rooms.occupied_slots, rooms.dorm_id, 
                       dorms.name AS dorm_name,  -- Récupérer le nom du dortoir
                       COALESCE(GROUP_CONCAT(students.name SEPARATOR ', '), 'Aucun étudiant') AS student_names
                FROM rooms 
                LEFT JOIN students ON rooms.room_id = students.room_id  -- Nom de colonne corrigé
                LEFT JOIN dorms ON rooms.dorm_id = dorms.id  -- Jointure pour obtenir le nom du dortoir
                GROUP BY rooms.room_number, rooms.floor, rooms.capacity, rooms.occupied_slots, rooms.dorm_id, dorms.name
                ORDER BY rooms.dorm_id, rooms.room_number";
$rooms_result = $conn->query($rooms_query);

if (!$rooms_result) {
    die("Échec de la requête : " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>

        .rooms-table {
            width: 100%;
            max-width: 1100px;
            margin-top: 100px; /* Marge par défaut lors de l'ouverture */
            background: white;
            padding: 40px;
            padding-right: 20px;
            border-radius: 10px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
            overflow-x: auto;
            display: flex;
            flex-wrap: nowrap;
            justify-content: center;
            transition: margin-top 0.3s ease; /* Transition fluide pour les changements de marge */
        }

        .rooms-table.opened {
            margin-top: 300px; /* Ajouter plus d'espace lors de l'ouverture */
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
            display: none; /* Masquer toutes les cartes de chambre au départ */
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
            padding: 40px; /* Augmenter le padding */
            border-radius: 10px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
            z-index: 1000;
            width: 40%; /* Augmenter la largeur */
            max-width: 800px; /* Définir une largeur maximale */
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
<?php include '../header.php'; ?> <!-- Chemin mis à jour vers header.php -->

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
                    <h3><?php echo htmlspecialchars($row['dorm_name']); ?></h3> <!-- Afficher le nom du dortoir ici -->
                    <div class="floor-buttons">
                        <button onclick="toggleRooms('<?php echo $row['dorm_id']; ?>', 'all')">Tous les étages</button>
                        <button onclick="toggleRooms('<?php echo $row['dorm_id']; ?>', '1')">Étage 1</button>
                        <button onclick="toggleRooms('<?php echo $row['dorm_id']; ?>', '2')">Étage 2</button>
                        <button onclick="toggleRooms('<?php echo $row['dorm_id']; ?>', '3')">Étage 3</button>
                        <!-- Ajouter plus de boutons pour les étages supplémentaires si nécessaire -->
                    </div>
                <?php endif; ?>
                <div class="room-card" data-dorm="<?php echo htmlspecialchars($row['dorm_id']); ?>" data-floor="<?php echo htmlspecialchars($row['floor']); ?>">
                    <button onclick="showRoomInfo('<?php echo htmlspecialchars($row['room_number']); ?>', '<?php echo htmlspecialchars($row['floor']); ?>', '<?php echo htmlspecialchars($row['capacity']); ?>', '<?php echo htmlspecialchars($row['occupied_slots']); ?>', '<?php echo htmlspecialchars($row['student_names']); ?>')">
                        <h3>Chambre <?php echo htmlspecialchars($row['room_number']); ?></h3>
                    </button>
                </div>
            <?php endwhile; ?>
            </div>
        </div>
        <div id="overlay" class="overlay" onclick="closeRoomInfo()"></div>
        <div id="room-info" class="room-info">
            <button class="close-btn" onclick="closeRoomInfo()">X</button>
            <h3>Informations sur la chambre</h3>
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

            // Ajuster la marge en fonction de la visibilité
            if (isAnyVisible) {
                roomsTable.classList.add('opened');
            } else {
                roomsTable.classList.remove('opened');
            }
        }

        function showRoomInfo(roomNumber, floor, capacity, occupiedSlots, studentNames) {
            document.getElementById('room-number').innerText = 'Numéro de chambre : ' + roomNumber;
            document.getElementById('room-floor').innerText = 'Étage : ' + floor;
            document.getElementById('room-capacity').innerText = 'Capacité : ' + capacity;
            document.getElementById('room-occupied').innerText = 'Places occupées : ' + occupiedSlots;
            document.getElementById('room-students').innerText = 'Étudiants : ' + studentNames;
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
