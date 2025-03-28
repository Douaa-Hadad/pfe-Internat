<?php
session_start();
include '../connection.php';

if (!isset($_SESSION['student_cin'])) {
    header("Location: ../login/login.php");
    exit();
}

$student_cin = $_SESSION['student_cin'];

// ✅ Fetch student info
$studentQuery = $conn->prepare("SELECT gender, status FROM students WHERE cin = ?");
$studentQuery->bind_param("s", $student_cin);
$studentQuery->execute();
$studentResult = $studentQuery->get_result();
$student = $studentResult->fetch_assoc();

$gender = $student['gender'];
$status = $student['status'];

$error = '';
$success = '';

// ✅ Prevent multiple applications
if ($status !== 'not_applied') {
    echo "<script>
            alert('You have already applied. Redirecting to dashboard...');
            window.location.href = 'dashboard.php';
          </script>";
    exit();
}

// ✅ Fetch Dorms (Filtered by Student Gender)
$dormQuery = $conn->prepare("SELECT id, name FROM dorms WHERE gender = ?");
$dormQuery->bind_param("s", $gender);
$dormQuery->execute();
$dorms = $dormQuery->get_result();

// ✅ Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $room_id = $_POST['room_id'];

    // ✅ Check if room has available slots
    $roomCheck = $conn->prepare("SELECT capacity, occupied_slots FROM rooms WHERE room_id = ?");
    $roomCheck->bind_param("s", $room_id);
    $roomCheck->execute();
    $roomResult = $roomCheck->get_result();
    $room = $roomResult->fetch_assoc();

    if ($room['occupied_slots'] < $room['capacity']) {
        // ✅ Assign room & update status to pending
        $updateQuery = $conn->prepare("UPDATE students SET room_id = ?, status = 'pending' WHERE cin = ?");
        $updateQuery->bind_param("ss", $room_id, $student_cin);
        
        if ($updateQuery->execute()) {
            // ✅ Increase occupied slots in rooms table
            $updateRoom = $conn->prepare("UPDATE rooms SET occupied_slots = occupied_slots + 1 WHERE room_id = ?");
            $updateRoom->bind_param("s", $room_id);
            $updateRoom->execute();

            $success = "Your dorm application has been submitted successfully!";
            echo "<script>
                    setTimeout(function(){
                        window.location.href='dashboard.php';
                    }, 2000);
                  </script>";
        } else {
            $error = "Error: " . $conn->error;
        }
    } else {
        $error = "Selected room is full. Please choose another.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Apply for Dorm</title>
    <link rel="stylesheet" href="css/dashboard.css">
    <script src="js/apply_dorm.js"></script> <!-- AJAX for live room updates -->
</head>
<body>
    <?php /* include 'sidebar.php';*/ ?>

    <div class="container">
        <h2>Apply for a Dorm</h2>
        
        <?php if (!empty($error)): ?>
            <p class="error-message"><?php echo $error; ?></p>
        <?php elseif (!empty($success)): ?>
            <p class="success-message"><?php echo $success; ?></p>
        <?php endif; ?>

        <form action="apply_dorm.php" method="POST">
            <!-- ✅ Dorm Selection -->
            <div class="form-group">
                <label for="dorm">Choose a Dorm</label>
                <select name="dorm" id="dorm" required>
                    <option value="" disabled selected>Select a Dorm</option>
                    <?php while ($dorm = $dorms->fetch_assoc()): ?>
                        <option value="<?php echo $dorm['id']; ?>">
                            <?php echo $dorm['name']; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <!-- ✅ Room Selection (Updated by AJAX) -->
            <div class="form-group">
                <label for="room_id">Choose a Room</label>
                <select name="room_id" id="room_id" required>
                    <option value="" disabled selected>Select a Room</option>
                </select>
            </div>

            <button type="submit" class="btn">Submit Application</button>
        </form>
    </div>
</body>
</html>
