<?php
session_start();
include '../connection.php';

if (!isset($_SESSION['student_cin'])) {
    header("Location: ../login/login.php");
    exit();
}

$student_cin = $_SESSION['student_cin'];
$student_name = $_SESSION['student_name'];

// ✅ Fetch student's room assignment
$roomQuery = $conn->prepare("SELECT r.room_number, d.name AS dorm_name, r.floor 
                             FROM students s
                             LEFT JOIN rooms r ON s.dorm_id = r.dorm_id AND s.room_number = r.room_number
                             LEFT JOIN dorms d ON r.dorm_id = d.id
                             WHERE s.cin = ?");
$roomQuery->bind_param("s", $student_cin);
$roomQuery->execute();
$roomResult = $roomQuery->get_result();
$room = $roomResult->fetch_assoc();

// ✅ Check if the student has submitted a dorm request
$requestQuery = $conn->prepare("SELECT status FROM room_requests WHERE student_cin = ?");
$requestQuery->bind_param("s", $student_cin);
$requestQuery->execute();
$requestResult = $requestQuery->get_result();
$request = $requestResult->fetch_assoc();

$isRequestPending = $request && $request['status'] === 'pending';
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
        <?php elseif ($isRequestPending): ?>
            <p>Your room request is pending. Please wait for approval.</p>
        <?php else: ?>
            <p>You have not applied for a dorm yet.</p>
            <a href="apply-dorm.php" class="btn">Apply for a Dorm</a>
        <?php endif; ?>

        <!-- ✅ Meal Reservations -->
        <h3>Your Meal Reservations</h3>
        <ul>
            <?php 
            $mealQuery = $conn->prepare("SELECT meal_type, reservation_date 
                                        FROM meal_reservations 
                                        WHERE student_cin = ? 
                                        ORDER BY reservation_date DESC");
            $mealQuery->bind_param("s", $student_cin);
            $mealQuery->execute();
            $mealResult = $mealQuery->get_result();

            if ($mealResult->num_rows > 0) {
                while ($meal = $mealResult->fetch_assoc()) {
                    echo "<li>" . ucfirst($meal['meal_type']) . " - " . $meal['reservation_date'] . "</li>";
                }
            } else {
                echo "<li>You have not reserved any meals yet.</li>";
            }
            ?>
        </ul>

        <div class="actions">
            <a href="reserve-meal.php" class="btn">Reserve a Meal</a>
            <a href="../auth/logout.php" class="btn btn-danger">Logout</a>
        </div>
    </div>
</body>
</html>
