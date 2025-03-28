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
    echo "<h2>Welcome, $student_name!</h2>";
    echo "<p>You have not applied for a dorm yet.</p>";
    echo "<a href='apply_dorm.php' class='btn'>Apply for a Dorm</a>";
    echo "<a href='../login/logout.php' class='btn btn-danger'>Logout</a>";
    echo "</div>";
    exit(); 
} elseif ($status === 'pending') {
    // ✅ Students waiting for admin approval
    echo "<div class='container'>";
    echo "<h2>Welcome, $student_name!</h2>";
    echo "<p>Your dorm application is pending approval.</p>";
    echo "<a href='../login/logout.php' class='btn btn-danger'>Logout</a>";
    echo "</div>";
    exit();
} elseif ($status === 'rejected') {
    // ✅ Students whose application was rejected
    echo "<div class='container'>";
    echo "<h2>Welcome, $student_name!</h2>";
    echo "<p>Your dorm application was rejected. Please contact the administration.</p>";
    echo "<a href='../login/logout.php' class='btn btn-danger'>Logout</a>";
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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body>
    <?php include 'sidebar.php'; ?>

    <div class="container">
        <h2>Welcome, <?php echo htmlspecialchars($student_name); ?>!</h2>

        <!-- ✅ Room Info -->
        <h3>Your Room</h3>
        <?php if ($room): ?>
            <p><strong>Dorm:</strong> <?php echo htmlspecialchars($room['dorm_name']); ?></p>
            <p><strong>Floor:</strong> <?php echo htmlspecialchars($room['floor']); ?></p>
            <p><strong>Room Number:</strong> <?php echo htmlspecialchars($room['room_number']); ?></p>
        <?php else: ?>
            <p>You have not selected a room yet.</p>
            <a href="choose-room.php" class="btn">Choose Room</a>
        <?php endif; ?>

        <!-- ✅ Meal Reservations -->
        <h3>Your Meal Reservations</h3>
        <a href="reserve-meal.php" class="btn">Reserve a Meal</a>

        <a href="../login/logout.php" class="btn btn-danger">Logout</a>
    </div>
</body>
</html>
