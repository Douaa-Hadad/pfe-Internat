<?php
session_start();
include '../connection.php';

if (!isset($_SESSION['student_cin'])) {
    header("Location: ../login/login.php");
    exit();
}

$student_cin = $_SESSION['student_cin'];
$student_name = $_SESSION['student_name'];

// ✅ Fetch student status
$statusQuery = $conn->prepare("SELECT status FROM students WHERE cin = ?");
if (!$statusQuery) {
    die("Error preparing statement for student status: " . $conn->error); // Debugging error
}
$statusQuery->bind_param("s", $student_cin);
$statusQuery->execute();
$statusResult = $statusQuery->get_result();
$statusRow = $statusResult->fetch_assoc();
$status = $statusRow['status'];

// ✅ Check if the student has a dorm application
$dormApplicationQuery = $conn->prepare("SELECT COUNT(*) AS application_count FROM dorm_applications WHERE name = ?");
if (!$dormApplicationQuery) {
    die("Error preparing statement for dorm application: " . $conn->error); // Debugging error
}
$dormApplicationQuery->bind_param("s", $student_name); // Use student_name instead of student_cin
$dormApplicationQuery->execute();
$dormApplicationResult = $dormApplicationQuery->get_result();
$dormApplicationRow = $dormApplicationResult->fetch_assoc();
$hasDormApplication = $dormApplicationRow['application_count'] > 0;

if ($status === 'not_applied') {
    // ✅ New students who haven't applied for a dorm yet
    echo "<div class='container'>";
    echo "<h2>Bienvenue, $student_name!</h2>";
    echo "<p>Vous n'avez pas encore postulé pour un internat.</p>";
    echo "<a href='apply_dorm.php' class='btn'>Soumettre demande d'internat</a>"; // Button to apply for a dorm
    echo "<a href='../login/logout.php' class='btn btn-danger'>Se déconnecter</a>";
    echo "</div>";
    exit(); 
} elseif ($status === 'pending') {
    // ✅ Students waiting for admin approval
    echo "<div class='container'>";
    echo "<h2>Bienvenue, $student_name!</h2>";
    echo "<p>Votre demande d'internat est en attente d'approbation.</p>";
    echo "<a href='../login/logout.php' class='btn btn-danger'>Se déconnecter</a>";
    echo "</div>";
    exit();
} elseif ($status === 'rejected') {
    // ✅ Students whose application was rejected
    echo "<div class='container'>";
    echo "<h2>Bienvenue, $student_name!</h2>";
    echo "<p>Votre demande d'internat a été rejetée. Veuillez contacter l'administration.</p>";
    echo "<a href='../login/logout.php' class='btn btn-danger'>Se déconnecter</a>";
    echo "</div>";
    exit();
}

// ✅ Fetch room details (only if approved)
$roomQuery = $conn->prepare("SELECT r.room_id, r.room_number, d.name AS dorm_name, r.floor 
                             FROM students s
                             LEFT JOIN rooms r ON s.room_id = r.room_id
                             LEFT JOIN dorms d ON r.dorm_id = d.id
                             WHERE s.cin = ?");
$roomQuery->bind_param("s", $student_cin);
$roomQuery->execute();
$roomResult = $roomQuery->get_result();
$room = $roomResult->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Tableau de bord étudiant</title>
    <link rel="stylesheet" href="css/dashboard.css">
    <style>
        /* Inline CSS for additional styling */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 9000px; /* Increased width */
            margin: 20px auto;
            padding: 20px;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        h2, h3 {
            color: #333333;
        }
        p {
            color: #555555;
            line-height: 1.6;
        }
        .btn {
            display: inline-block;
            margin: 10px 5px;
            padding: 10px 20px;
            color: #ffffff;
            background-color: #007bff;
            border: none;
            border-radius: 4px;
            text-decoration: none;
            font-size: 16px;
            cursor: pointer;
        }
        .btn:hover {
            background-color: #0056b3;
        
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>

    <div class="container">
        <h2>Bienvenue, <?php echo htmlspecialchars($student_name); ?>!</h2>

        <!-- ✅ Dorm Application Button -->
        <?php if (!$hasDormApplication): ?>
            <p>Vous n'avez pas encore soumis de demande d'internat.</p>
            <a href="apply_dorm.php" class="btn">Demande d'internat</a>
        <?php endif; ?>

        <!-- ✅ Room Info -->
        <h3>Votre chambre</h3>
        <?php if ($room && $room['dorm_name'] && $room['floor'] && $room['room_number']): ?>
            <p><strong>Internat :</strong> <?php echo htmlspecialchars($room['dorm_name']); ?></p>
            <p><strong>Étage :</strong> <?php echo htmlspecialchars($room['floor']); ?></p>
            <p><strong>Numéro de chambre :</strong> <?php echo htmlspecialchars($room['room_number']); ?></p>
        <?php else: ?>
            <p>Les détails de votre chambre ne sont pas encore disponibles.</p>
            <a href="choose-room.php" class="btn">Choisir une chambre</a>
        <?php endif; ?>

    </div>
</body>
</html>
