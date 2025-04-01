<?php
session_start();
include '../connection.php';

if (!isset($_SESSION['student_cin'])) {
    header("Location: login.php");
    exit();
}

$student_cin = $_SESSION['student_cin'];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];

    $query = $conn->prepare("UPDATE students SET name = ?, email = ?, phone = ? WHERE cin = ?");
    $query->bind_param("ssss", $name, $email, $phone, $student_cin);
    
    if ($query->execute()) {
        echo "Profile updated!";
    } else {
        echo "Error: {$conn->error}";
    }
}

$query = $conn->prepare("SELECT * FROM students WHERE cin = ?");
$query->bind_param("s", $student_cin);
$query->execute();
$result = $query->get_result();
$student = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/dashboard.css">
    <title>Student Profile</title>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <form method="POST">
        <input type="text" name="name" value="<?= htmlspecialchars("{$student['name']}") ?>" required>
        <input type="email" name="email" value="<?= htmlspecialchars("{$student['email']}") ?>" required>
        <input type="text" name="phone" value="<?= htmlspecialchars("{$student['phone']}") ?>" required>
        <button type="submit">Update</button>
    </form>
</body>
</html>
