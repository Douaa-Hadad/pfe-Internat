<?php
session_start();
include '../connection.php';

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

if (!isset($_SESSION['student_cin'])) {
    header("Location: login.php");
    exit();
}

$student_cin = $_SESSION['student_cin'];

$query = $conn->prepare("
    SELECT r.room_number, d.name AS dorm_name, r.capacity, r.occupied_slots
    FROM rooms r
    JOIN dorms d ON r.dorm_id = d.id
    WHERE r.room_id = (SELECT room_id FROM students WHERE cin = ?)
");
$query->bind_param("s", $student_cin);

if (!$query->execute()) {
    die("Query execution failed: " . $query->error);
}

$result = $query->get_result();
$room = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Room</title>
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <h2>Room Details</h2>
    <?php if ($room): ?>
        <p>Dorm: <?= htmlspecialchars($room['dorm_name']) ?></p>
        <p>Room Number: <?= htmlspecialchars($room['room_number']) ?></p>
        <p>Capacity: <?= $room['capacity'] ?></p>
        <p>Occupied Slots: <?= $room['occupied_slots'] ?></p>
    <?php else: ?>
        <p>You are not assigned to any room yet.</p>
    <?php endif; ?>
</body>
</html>
