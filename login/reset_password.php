<?php
include '../connection.php';
$message = "";

if (isset($_GET['key']) && isset($_GET['email'])) {
    $key = $_GET['key'];
    $email = $_GET['email'];
    $check_key = mysqli_query($conn, "SELECT * FROM forgot_password WHERE email='$email' AND temp_key='$key'");

    if (mysqli_num_rows($check_key) == 0) {
        die("Invalid or expired reset link.");
    }
} else {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $confirm_password = mysqli_real_escape_string($conn, $_POST['confirm_password']);

    if ($password === $confirm_password) {
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        mysqli_query($conn, "UPDATE students SET password='$hashed_password' WHERE email='$email'");
        mysqli_query($conn, "DELETE FROM forgot_password WHERE email='$email'");
        $message = "Password reset successfully. <a href='login.php'>Login</a>";
    } else {
        $message = "Passwords do not match.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Reset Password</title>
</head>
<body>
    <form method="POST">
        <label>New Password:</label>
        <input type="password" name="password" required>
        <label>Confirm Password:</label>
        <input type="password" name="confirm_password" required>
        <button type="submit">Reset Password</button>
    </form>
    <?php if ($message) echo "<p>$message</p>"; ?>
</body>
</html>
