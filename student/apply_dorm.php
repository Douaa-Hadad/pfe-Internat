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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect form data
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $city = $conn->real_escape_string($_POST['city']);

    // Insert into database
    $sql = "INSERT INTO dorm_applications (name, email, city, status) VALUES ('$name', '$email', '$city', 'Pending')";
    if ($conn->query($sql) === TRUE) {
        echo "Application submitted successfully!";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
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
    max-width: 600px; /* Increase width for better form layout */
    display: grid;
    grid-template-columns: 1fr; /* Change to single column layout */
    gap: 20px; /* Increase spacing between fields */
}

label {
    display: block;
    margin-left: 5px;
    font-weight: bold;
}

input {
    width: 100%;
    padding: 12px; /* Slightly reduce padding to maintain balance */
    border: 1px solid #ccc;
    border-radius: 4px;
    font-size: 16px;
}

button.bttn {
    background-color: #007bff;
    color: #fff;
    border: none;
    padding: 12px; /* Adjust padding to match inputs */
    border-radius: 4px;
    cursor: pointer;
    width: 100%;
    font-size: 16px;
}

button.bttn:hover {
    background-color: #0056b3;
}

/* Adjust for full-width fields (name, city, etc.) */
.full-width {
    grid-column: span 1;
}

@media (min-width: 600px) {
    form {
        grid-template-columns: 1fr 1fr; /* Change to two columns on wider screens */
    }

    .full-width {
        grid-column: span 2;
    }
}

    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>

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
            <input type="text" id="major" name="major" value="<?php echo htmlspecialchars($student['major']); ?>" required>
        </div>
        <div class="full-width">
            <label for="city">City:</label>
            <input type="text" id="city" name="city" required>
        </div>
        <div class="full-width">
            <button type="submit" class="bttn">Submit Application</button>
        </div>
    </form>
</body>
</html>