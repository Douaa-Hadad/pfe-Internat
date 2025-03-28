<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Perform login logic here
    $loginSuccessful = false; // Replace with actual login logic
    $userId = null; // Replace with actual user ID
    $userRole = null; // Replace with actual user role

    if ($loginSuccessful) {
        $_SESSION['user_id'] = $userId; // Replace $userId with the actual user ID
        $_SESSION['user_role'] = $userRole; // Replace $userRole with 'dorm_admin' or 'comptable'

        if ($userRole === 'dorm_admin') {
            header("Location: dormAdminDashboard.php");
        } elseif ($userRole === 'comptable') {
            header("Location: comptableDashboard.php");
        }
        exit();
    } else {
        $error = "Invalid username or password.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
</head>
<body>
    <h2>Login</h2>
    <?php if (isset($error)): ?>
        <p style="color: red;"><?php echo $error; ?></p>
    <?php endif; ?>
    <form method="post" action="login.php">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required>
        <br>
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>
        <br>
        <input type="submit" value="Login">
    </form>
</body>
</html>
