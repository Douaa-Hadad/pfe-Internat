<?php
session_start();
include '../connection.php';

if (!isset($_SESSION['student_cin'])) {
    header("Location: ../login/login.php");
    exit();
}

// Database connection
$conn = new mysqli('localhost', 'root', '', 'estcasa');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch student information
$student_cin = $_SESSION['student_cin']; // Corrected session key
$sql = "SELECT * FROM students WHERE cin = '$student_cin'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $student = $result->fetch_assoc();
} else {
    echo "Student not found.";
    exit();
}

// Check if the student already has a dorm application
$checkApplicationQuery = $conn->prepare("SELECT status FROM dorm_applications WHERE email = ?");
$checkApplicationQuery->bind_param("s", $student['email']);
$checkApplicationQuery->execute();
$checkApplicationQuery->store_result();

$applicationExists = $checkApplicationQuery->num_rows > 0;
$applicationStatus = "";

if ($applicationExists) {
    $checkApplicationQuery->bind_result($applicationStatus);
    $checkApplicationQuery->fetch();
}
$checkApplicationQuery->close();

// Display message if an application already exists
if ($applicationExists) {
    if ($applicationStatus === 'Pending') {
        $message = "Vous avez déjà une demande de dortoir en attente. Veuillez attendre l'approbation de l'administration.";
    } elseif ($applicationStatus === 'Rejected') {
        $message = "Votre demande de dortoir a été rejetée. Veuillez soumettre une nouvelle demande.";
    } elseif ($applicationStatus === 'Approved') {
        $message = "Votre demande de dortoir a été acceptée. Vous ne pouvez pas soumettre une nouvelle demande.";
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$applicationExists) {
    // Collect form data
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $city = $conn->real_escape_string($_POST['city']);

    // Insert into database
    $sql = "INSERT INTO dorm_applications (name, email, city, status) VALUES ('$name', '$email', '$city', 'Pending')";
    if ($conn->query($sql) === TRUE) {
        $message = "Application submitted successfully!";
        header("Location: apply_dorm.php?message=" . urlencode($message));
        exit();
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

// Prevent form submission if the application is accepted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $applicationExists && $applicationStatus === 'Accepted') {
    $message = "Votre demande de dortoir a été acceptée. Vous ne pouvez pas soumettre une nouvelle demande.";
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply for Dorm</title>
    <style>
body {
    font-family: Arial, sans-serif;
    background-color: #f4f4f9;
    margin: 0;
    padding: 0;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
}

h1 {
    color: #333;
    text-align: center;
}

form {
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    width: 100%;
    max-width: 600px;
    display: grid;
    grid-template-columns: 1fr; /* Single column layout */
    gap: 15px; /* Add spacing between fields */
}

label {
    display: block;
    margin-bottom: 5px; /* Add space below labels */
    font-weight: bold;
}

input, select {
    width: 100%;
    padding: 10px; /* Adjust padding for better balance */
    border: 1px solid #ccc;
    border-radius: 4px;
    font-size: 16px;
    box-sizing: border-box; /* Ensure padding doesn't affect width */
    background-color: #fff; /* Ensure consistent background */
}

button.bttn {
    background-color: #007bff;
    color: #fff;
    border: none;
    padding: 12px;
    border-radius: 4px;
    cursor: pointer;
    width: 100%;
    font-size: 16px;
}

button.bttn:hover {
    background-color: #0056b3;
}

.full-width {
    grid-column: span 1; /* Ensure full-width fields span the container */
}

@media (min-width: 600px) {
    form {
        grid-template-columns: 1fr 1fr; /* Two-column layout for wider screens */
    }

    .full-width {
        grid-column: span 2; /* Full-width fields span both columns */
    }
}

.message-container {
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    text-align: center;
}

.message {
    background-color: #ffcccc;
    color: #cc0000;
    padding: 20px;
    border-radius: 8px;
    font-size: 18px;
    font-weight: bold;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}
</style>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const urlParams = new URLSearchParams(window.location.search);
            const message = urlParams.get('message');
            const modal = document.querySelector('.modal');
            const modalMessage = document.querySelector('.modal-message');
            const closeBtn = document.querySelector('.modal-content .close-btn');

            if (message) {
                modalMessage.textContent = message;
                modal.style.display = 'flex';
            }

            if (closeBtn) {
                closeBtn.addEventListener('click', function () {
                    modal.style.display = 'none';
                });
            }
        });
    </script>
</head>
<body>
    <?php include 'sidebar.php'; ?>

    <?php if (isset($message)): ?>
        <div class="message-container">
            <div class="message">
                <?php echo htmlspecialchars($message); ?>
                <?php if ($applicationStatus === 'Rejected'): ?>
                    <form action="apply_dorm.php" method="POST" style="margin-top: 10px;">
                        <button type="submit" name="new_request" style="padding: 10px 20px; background-color: #28a745; color: white; border: none; border-radius: 5px; cursor: pointer; font-family: inherit; font-size: inherit;">
                            Soumettre une nouvelle demande
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    <?php else: ?>
        <h1>Apply for Dorm</h1>
        <form method="POST" action="">
            <div>
                <label for="name">Name:</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($student['name']); ?>" required>
            </div>
            <div>
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($student['email']); ?>" required>
            </div>
            <div>
                <label for="phone">Phone:</label>
                <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($student['phone']); ?>" required>
            </div>
            <div>
                <label for="gender">Gender:</label>
                <input type="text" id="gender" name="gender" value="<?php echo htmlspecialchars($student['gender']); ?>" readonly>
            </div>
            <div>
                <label for="year_of_study">Year of Study:</label>
                <input type="text" id="year_of_study" name="year_of_study" value="<?php echo htmlspecialchars($student['year_of_study']); ?>" required>
            </div>
            <div>
                <label for="major">Major:</label>
                <select id="major" name="major" required>
                    <option value="" disabled selected>Select Major</option>
                    <option value="Genie Mecanique" <?php echo ($student['major'] === 'Genie Mecanique') ? 'selected' : ''; ?>>Genie Mecanique</option>
                    <option value="Genie Informatique" <?php echo ($student['major'] === 'Genie Informatique') ? 'selected' : ''; ?>>Genie Informatique</option>
                    <option value="Genie Electrique" <?php echo ($student['major'] === 'Genie Electrique') ? 'selected' : ''; ?>>Genie Electrique</option>
                    <option value="Genie des Procedes" <?php echo ($student['major'] === 'Genie des Procedes') ? 'selected' : ''; ?>>Genie des Procedes</option>
                    <option value="Finance et Commerce" <?php echo ($student['major'] === 'Finance et Commerce') ? 'selected' : ''; ?>>Finance et Commerce</option>
                    <option value="Business et Marketing" <?php echo ($student['major'] === 'Business et Marketing') ? 'selected' : ''; ?>>Business et Marketing</option>
                    <option value="INED" <?php echo ($student['major'] === 'INED') ? 'selected' : ''; ?>>INED</option>
                </select>
            </div>
            <div class="full-width">
                <label for="city">City:</label>
                <input type="text" id="city" name="city" required>
            </div>
            <div class="full-width">
                <button type="submit" class="bttn">Submit Application</button>
            </div>
        </form>
    <?php endif; ?>
</body>
</html>