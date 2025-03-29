<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
/*
// Check if the user is logged in as an admin
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login/login.php');
    exit;
}
*/
// Fetch admin details (example assumes details are stored in session)
$adminName = htmlspecialchars($_SESSION['username']);
$adminEmail = htmlspecialchars($_SESSION['email'] ?? 'Not provided');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Profile</title>
</head>
<body>
    <h1>Admin Profile</h1>
    <p><strong>Name:</strong> <?php echo $adminName; ?></p>
    <p><strong>Email:</strong> <?php echo $adminEmail; ?></p>
    <!-- Add more admin details as needed -->
</body>
</html>
