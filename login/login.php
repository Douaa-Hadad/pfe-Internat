<?php
session_start();
include '../connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']); // Can be CIN or Email
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error = "Username and password are required.";
    } else {
        // ✅ Check if user is a student
        $stmt = $conn->prepare("SELECT cin, name, email, password FROM students WHERE cin = ? OR email = ?");
        $stmt->bind_param("ss", $username, $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $student = $result->fetch_assoc();
            
            if (password_verify($password, $student['password'])) {
                // ✅ Store student session
                $_SESSION['user_type'] = 'student';
                $_SESSION['student_cin'] = $student['cin'];
                $_SESSION['student_name'] = $student['name'];
                $_SESSION['student_email'] = $student['email'];

                header("Location: ../student/dashboard.php");
                exit();
            }
        }

        // ✅ If not found in students, check users table (admins)
        $stmt = $conn->prepare("SELECT cin, full_name, email, password, role FROM users WHERE cin = ? OR email = ?");
        $stmt->bind_param("ss", $username, $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $admin = $result->fetch_assoc();
            
            if (password_verify($password, $admin['password'])) {
                // ✅ Store admin session
                $_SESSION['user_type'] = 'admin';
                $_SESSION['user_cin'] = $admin['cin'];
                $_SESSION['user_name'] = $admin['full_name'];
                $_SESSION['user_email'] = $admin['email'];
                $_SESSION['user_role'] = $admin['role'];

                // ✅ Redirect based on role
                if ($admin['role'] == 'comptable') {
                    header("Location: ../admin/comptable/index.php");
                } elseif ($admin['role'] == 'dorm_manager') {
                    header("Location: ../admin/dorm_manager/index.php");
                } 
                elseif ($admin['role'] == 'Gestionnaire_Tickets') {
                    header("Location: ../gestion_repas/admin_repas.php");
                }
                elseif ($admin['role'] == 'Responsable_Scan') {
                    header("Location: ../gestion_repas/scanner_qr.php");
                }
                 elseif ($admin['role'] == 'request') {
                    header("Location: ../admin/dorm_accepter/index.php");
                }

                exit();
            }
        }

        // ✅ If no match, show error
        $error = "Invalid username or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <link rel="stylesheet" href="login.css">
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
    <script>
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
    </script>

</head>
<body>
    <div class="login-container">
        <div class="logo">
            <img src="logo.png" alt="logo">
        </div>
        <h2>Login</h2>
        
        <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>

        <form method="post" action="login.php">
            <div class="username">
                <label for="username">Email / CIN</label>
                <div class="input-container">
                    <ion-icon name="person-outline"></ion-icon>
                    <input type="text" id="username" name="username" placeholder="Email or CIN" required>
                </div>
            </div>

            <div class="password">
                <label for="password">Password</label>
                <div class="input-container">
                    <ion-icon name="lock-closed-outline"></ion-icon>
                    <input type="password" id="password" name="password" placeholder="Password" required>
                </div>
            </div>

            <button type="submit" class="login">Login</button>
        </form>

        <div class="footer">
            <span><a href="register.php">Sign up</a></span>
            <span><a href="forgot_password.php">Forgot Password?</a></span>
        </div>
    </div>

    <script type="text/javascript" src="js/login.js"></script>
</body>
</html>
