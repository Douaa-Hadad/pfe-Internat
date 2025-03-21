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

if ($status === 'pending') {
    // Show only "Complete Registration" if still pending
    echo "<div class='container'>";
    echo "<h2>Welcome, $student_name!</h2>";
    echo "<p>Your registration is incomplete. Please complete your registration to access the dorm and meal services.</p>";
    echo "<a href='apply_dorm.php' class='btn'>Complete Registration</a>";
    echo "<a href='../login/logout.php' class='btn btn-danger'>Logout</a>";
    echo "</div>";
    exit(); // ✅ Stop rendering the rest of the page if status is pending
}

// ✅ Fetch room details (only if approved)
$roomQuery = $conn->prepare("SELECT r.room_number, d.name AS dorm_name, r.floor 
                             FROM students s
                             LEFT JOIN rooms r ON s.dorm_id = r.dorm_id AND s.room_number = r.room_number
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
        <h2>Welcome, <?php echo $student_name; ?>!</h2>

        <!-- ✅ Room Info -->
        <h3>Your Room</h3>
        <?php if ($room): ?>
            <p><strong>Dorm:</strong> <?php echo $room['dorm_name']; ?></p>
            <p><strong>Floor:</strong> <?php echo $room['floor']; ?></p>
            <p><strong>Room Number:</strong> <?php echo $room['room_number']; ?></p>
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
