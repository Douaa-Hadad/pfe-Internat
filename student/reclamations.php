<?php
session_start();
include '../connection.php';


if (!isset($_SESSION['student_cin'])) {
    header("Location: ../login/login.php");
    exit();
}

$student_cin = $_SESSION['student_cin'];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $cin = $_SESSION['student_cin'];
    $message = $_POST['message'];

    $query = $conn->prepare("INSERT INTO reclamations (cin, message) VALUES (?, ?)");
    $query->bind_param("ss", $cin, $message);
    
    if ($query->execute()) {
        echo "Reclamation submitted!";
    } else {
        echo "Error: {$conn->error}";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Reclamation</title>
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <form method="POST">
        <textarea name="message" required placeholder="Describe your issue..."></textarea>
        <button type="submit">Submit</button>
    </form>
</body>
</html>
