<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Demandes</title>
    <link rel="stylesheet" href="styles.css"> <!-- Add your CSS file here -->
</head>
<body>
    <?php include '../header.php'; ?>
    <?php include 'sidebar.php'; ?>
   
    <div class="main-content">
        <!-- Room Requests Table -->
        <section class="table-container">
            <h2>Demandes de Chambres</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID de Demande</th>
                        <th>CIN Étudiant</th>
                        <th>ID Chambre</th>
                        <th>Statut</th>
                        <th>Date de Demande</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Database connection
                    $conn = new mysqli('localhost', 'root', '', 'estcasa');

                    // Check connection
                    if ($conn->connect_error) {
                        die("Connection failed: " . $conn->connect_error);
                    }

                    // Fetch room requests
                    $room_requests_query = "SELECT id, student_cin, room_id, status, request_date FROM room_requests";
                    $room_requests_result = $conn->query($room_requests_query);

                    if (!$room_requests_result) {
                        die("Room requests query failed: " . $conn->error);
                    }

                    if ($room_requests_result->num_rows > 0) {
                        while ($row = $room_requests_result->fetch_assoc()) {
                            echo "<tr>
                                <td>{$row['id']}</td>
                                <td>{$row['student_cin']}</td>
                                <td>{$row['room_id']}</td>
                                <td>" . ucfirst($row['status']) . "</td>
                                <td>{$row['request_date']}</td>
                            </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5'>No room requests found.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </section>

        <!-- Dorm Requests Table -->
        <section class="table-container">
            <h2>Demandes de Dortoir</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID de Demande</th>
                        <th>Nom Étudiant</th>
                        <th>Email</th>
                        <th>Ville</th>
                        <th>Statut</th>
                        <th>Date de Création</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Fetch dorm requests
                    $dorm_requests_query = "SELECT id, name, email, city, status, created_at FROM dorm_applications";
                    $dorm_requests_result = $conn->query($dorm_requests_query);

                    if (!$dorm_requests_result) {
                        die("Dorm requests query failed: " . $conn->error);
                    }

                    if ($dorm_requests_result->num_rows > 0) {
                        while ($row = $dorm_requests_result->fetch_assoc()) {
                            echo "<tr>
                                <td>{$row['id']}</td>
                                <td>{$row['name']}</td>
                                <td>{$row['email']}</td>
                                <td>{$row['city']}</td>
                                <td>{$row['status']}</td>
                                <td>{$row['created_at']}</td>
                            </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='6'>No dorm requests found.</td></tr>";
                    }

                    $conn->close();
                    ?>
                </tbody>
            </table>
        </section>
    </div>
</body>
</html>
