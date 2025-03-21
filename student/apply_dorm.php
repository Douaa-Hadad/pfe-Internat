<?php
session_start();
include '../connection.php';

if (!isset($_SESSION['student_cin'])) {
    header("Location: ../login/login.php");
    exit();
}

$student_cin = $_SESSION['student_cin'];

// ✅ Check if the student has already applied
$statusQuery = $conn->prepare("SELECT status FROM students WHERE cin = ?");
$statusQuery->bind_param("s", $student_cin);
if (!$statusQuery->execute()) {
    die("Query failed: " . $statusQuery->error);
}
$statusResult = $statusQuery->get_result();
$statusRow = $statusResult->fetch_assoc();
$status = $statusRow['status'];

if ($status === 'pending') {
    // ✅ If request is pending, redirect to dashboard
    header("Location: dashboard.php");
    exit();
}

$error = '';
$success = '';

// ✅ Function to handle file uploads securely
function uploadFile($file, $prefix) {
    $uploadDir = "../uploads/";
    $fileName = $prefix . "_" . basename($file["name"]);
    $targetFile = $uploadDir . $fileName;

    // ✅ Check file type and size
    $allowedTypes = ['jpg', 'jpeg', 'png', 'pdf'];
    $fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
    if (!in_array($fileType, $allowedTypes)) {
        return "Invalid file type. Only JPG, JPEG, PNG, and PDF are allowed.";
    }
    if ($file["size"] > 2 * 1024 * 1024) { // Max size: 2MB
        return "File is too large. Maximum allowed size is 2MB.";
    }

    if (move_uploaded_file($file["tmp_name"], $targetFile)) {
        return $fileName;
    } else {
        return "Failed to upload file.";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_card_front = uploadFile($_FILES['id_card_front'], $student_cin . "_front");
    $id_card_back = uploadFile($_FILES['id_card_back'], $student_cin . "_back");
    $payment_receipt = uploadFile($_FILES['payment_receipt'], $student_cin . "_receipt");

    // ✅ If all files uploaded successfully
    if (!is_string($id_card_front) && !is_string($id_card_back) && !is_string($payment_receipt)) {
        // ✅ Update student record and set status to 'pending'
        $stmt = $conn->prepare("UPDATE students 
                                SET id_card_front = ?, id_card_back = ?, payment_receipt = ?, status = 'pending'
                                WHERE cin = ?");
        $stmt->bind_param("ssss", $id_card_front, $id_card_back, $payment_receipt, $student_cin);

        if ($stmt->execute()) {
            $success = "Your dorm application has been submitted successfully!";
            header("Refresh: 2; url=dashboard.php"); // ✅ Redirect to dashboard after success
        } else {
            $error = "Error: " . $conn->error;
        }
        $stmt->close();
    } else {
        // ✅ If upload failed, store error
        $error = $id_card_front ?: $id_card_back ?: $payment_receipt;
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Apply for Dorm</title>
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body>
    <?php include 'sidebar.php'; ?>

    <div class="container">
        <h2>Apply for a Dorm</h2>
        
        <?php if ($error): ?>
            <p class="error-message"><?php echo $error; ?></p>
        <?php elseif ($success): ?>
            <p class="success-message"><?php echo $success; ?></p>
        <?php endif; ?>

        <?php if ($status !== 'pending' && $status !== 'approved'): ?>
            <form action="apply-dorm.php" method="POST" enctype="multipart/form-data">
                <!-- ✅ ID Card Front -->
                <div class="form-group">
                    <label for="id_card_front">ID Card (Front):</label>
                    <input type="file" name="id_card_front" required>
                </div>

                <!-- ✅ ID Card Back -->
                <div class="form-group">
                    <label for="id_card_back">ID Card (Back):</label>
                    <input type="file" name="id_card_back" required>
                </div>

                <!-- ✅ Payment Receipt -->
                <div class="form-group">
                    <label for="payment_receipt">Payment Receipt:</label>
                    <input type="file" name="payment_receipt" required>
                </div>

                <!-- ✅ Submit Button -->
                <button type="submit" class="btn">Submit Application</button>
            </form>
        <?php else: ?>
            <p class="pending-message">You have already applied for a dorm. Please wait for admin approval.</p>
        <?php endif; ?>
    </div>
</body>
</html>
