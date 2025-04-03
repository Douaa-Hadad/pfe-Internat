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
$statusQuery->bind_param("s", $student_cin);
$statusQuery->execute();
$statusResult = $statusQuery->get_result();
$statusRow = $statusResult->fetch_assoc();
$status = $statusRow['status'];

if ($status === 'not_applied') {
    // ✅ New students who haven't applied for a dorm yet
    echo "<div class='container'>";
    echo "<h2>Bienvenue, $student_name!</h2>";
    echo "<p>Vous n'avez pas encore postulé pour un internat.</p>";
    echo "<a href='apply_dorm.php' class='btn'>Postuler pour un internat</a>";
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
</head>
<body>
    <?php include 'sidebar.php'; ?>

    <div class="container">
        <h2>Bienvenue, <?php echo htmlspecialchars($student_name); ?>!</h2>

        <!-- ✅ Room Info -->
        <h3>Votre chambre</h3>
        <?php if ($room): ?>
            <p><strong>Internat :</strong> <?php echo htmlspecialchars($room['dorm_name']); ?></p>
            <p><strong>Étage :</strong> <?php echo htmlspecialchars($room['floor']); ?></p>
            <p><strong>Numéro de chambre :</strong> <?php echo htmlspecialchars($room['room_number']); ?></p>
        <?php else: ?>
            <p>Vous n'avez pas encore sélectionné de chambre.</p>
            <a href="choose-room.php" class="btn">Choisir une chambre</a>
        <?php endif; ?>

        <!-- ✅ Meal Reservations -->
        <h3>Vos réservations de repas</h3>
        <a href="etudiant_repas.php" class="btn">Réserver un repas</a>

        <a href="../login/logout.php" class="btn btn-danger">Se déconnecter</a>
    </div>
</body>
</html>
