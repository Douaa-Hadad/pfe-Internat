<?php
include '../connection.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];

    if (!$conn) {
        die("Database connection failed: " . mysqli_connect_error());
    }

    // Use prepared statements to prevent SQL injection
    $stmt = $conn->prepare("SELECT email FROM students WHERE email = ?");
    if (!$stmt) {
        die("Query preparation failed: " . $conn->error);
    }
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $key = md5(time() . rand(1000, 9999));

        // Insert into forgot_password table with prepared statements
        $insert_stmt = $conn->prepare("INSERT INTO forgot_password (email, temp_key) VALUES (?, ?)");
        if (!$insert_stmt) {
            die("Query preparation failed: " . $conn->error);
        }
        $insert_stmt->bind_param("ss", $email, $key);
        if ($insert_stmt->execute()) {
            $mail = new PHPMailer(true);
            try {
                $mail->SMTPDebug = 0;  // Disable debug output for production
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = getenv('SMTP_USERNAME'); // Use environment variable
                $mail->Password = getenv('SMTP_PASSWORD'); // Use environment variable
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                $mail->setFrom('estdormmanagement@gmail.com', 'Dorm Management System');
                $mail->addAddress($email);

                $mail->isHTML(true);
                $mail->Subject = 'Password Reset Request';
                $mail->Body = "Click the link below to reset your password:<br>";
                $mail->Body .= "<a href='http://localhost/pfe-Internat/login/reset_password.php?key=$key&email=$email'>Reset Password</a>";

                $mail->send();
                $message = "Please check your email for the reset link.";
            } catch (Exception $e) {
                $message = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            }
        } else {
            $message = "Failed to process your request. Please try again.";
        }
    } else {
        $message = "Email not found in our records.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Forgot Password</title>
    <link rel="stylesheet" type="text/css" href="login.css"> <!-- Link to the CSS file -->
</head>
<body>
    <div class="login-container">
        <h2>Forgot Password</h2>
        <form method="POST">
            <div class="input-container">
                <input type="email" name="email" placeholder="Enter your email" required>
            </div>
            <button type="submit" class="login">Send Reset Link</button>
        </form>
        <?php if ($message) echo "<p>$message</p>"; ?>
    </div>
</body>
</html>
