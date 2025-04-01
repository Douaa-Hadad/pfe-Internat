<?php
session_start();
include '../connection.php';

if (!isset($_SESSION['student_cin'])) {
    header("Location: login.php");
    exit();
}

$student_cin = $_SESSION['student_cin'];

$query = $conn->prepare("SELECT message, created_at FROM notifications WHERE cin = ? ORDER BY created_at DESC");
$query->bind_param("s", $student_cin);
$query->execute();
$result = $query->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications</title>
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <h2>Notifications</h2>
    <?php while ($row = $result->fetch_assoc()): ?>
        <p><strong><?= htmlspecialchars($row['created_at']) ?></strong>: <?= htmlspecialchars($row['message']) ?></p>
        <hr>
    <?php endwhile; ?>
</body>
</html>
