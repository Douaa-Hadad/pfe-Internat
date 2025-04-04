<?php
session_start();
require_once '../connection.php'; // Adjust path to your DB connection file

if (!isset($conn)) {
    die("Database connection failed. Please check your connection settings.");
}

// Redirect to login if not logged in
if (!isset($_SESSION['student_cin'])) {
    header("Location: ../login/login.php");
    exit();
}

$cin = $_SESSION['student_cin'];
$success = '';
$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);

    if (!empty($subject) && !empty($message)) {
        $sql = "INSERT INTO complaints (cin, subject, message) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param('sss', $cin, $subject, $message);

            if ($stmt->execute()) {
                $success = "Complaint submitted successfully.";
            } else {
                $error = "Error submitting complaint. Please try again.";
            }
        } else {
            $error = "Failed to prepare the SQL statement. Please contact the administrator.";
        }
    } else {
        $error = "Please fill in all fields.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Submit Complaint</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 40px;
            background-color: #f5f6fa;
        }

        .container {
            max-width: 600px;
            margin: auto;
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0px 2px 10px rgba(0, 0, 0, 0.15);
        }

        h2 {
            text-align: center;
            margin-bottom: 25px;
        }

        label {
            font-weight: bold;
        }

        input[type="text"], textarea {
            width: 100%;
            padding: 10px;
            margin-top: 6px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 15px;
        }

        button {
            background-color: #1b6ca8;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
            width: 100%;
        }

        button:hover {
            background-color: #155a8a;
        }

        .message {
            margin-bottom: 15px;
            padding: 10px;
            border-radius: 5px;
        }

        .success {
            background-color: #d4edda;
            color: #155724;
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>

<div class="container">
    <h2>Submit a Complaint</h2>

    <?php if ($success): ?>
        <div class="message success"><?php echo $success; ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="message error"><?php echo $error; ?></div>
    <?php endif; ?>

    <form action="complaints.php" method="POST">
        <label for="subject">Subject</label>
        <input type="text" name="subject" id="subject" required>

        <label for="message">Message</label>
        <textarea name="message" id="message" rows="6" required></textarea>

        <button type="submit">Submit Complaint</button>
    </form>
</div>

</body>
</html>
