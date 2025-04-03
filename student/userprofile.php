<?php
session_start();
include '../connection.php';

if (!isset($_SESSION['student_cin'])) {
    // Debug: Check if the session variable is missing
    error_log("Session 'student_cin' is not set.");
    header("Location: ../login/login.php");
    exit();
}

$student_cin = $_SESSION['student_cin'];

// Debug: Log the CIN being used
error_log("Fetching details for CIN: " . $student_cin);

// Fetch student details from the database
$query = $conn->prepare("SELECT name, email, phone, gender, year_of_study, major, profile_picture, status 
                         FROM students WHERE cin = ?");
if (!$query) {
    // Debug: Log query preparation error
    error_log("Query preparation failed: " . $conn->error);
    echo "An error occurred. Please try again later.";
    exit();
}

$query->bind_param("s", $student_cin);
$query->execute();
$result = $query->get_result();

if (!$result) {
    // Debug: Log query execution error
    error_log("Query execution failed: " . $query->error);
    echo "An error occurred. Please try again later.";
    exit();
}

$student = $result->fetch_assoc();

if (!$student) {
    // Debug: Log if no student is found
    error_log("No student found for CIN: " . $student_cin);
    echo "Student not found.";
    exit();
}

// Ensure the profile picture is stored in the session
$_SESSION['profile_picture'] = $student['profile_picture'];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Profil</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
        }

        .profile-container {
            width: 90%;
            max-width: 800px;
            margin: 20px auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
        }

        h2 {
            text-align: center;
            color: #141460;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 16px;
        }

        th, td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }

        .profile-img {
            display: block;
            margin: 0 auto;
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
        }

        .btn {
            display: block;
            width: 150px;
            margin: 20px auto;
            padding: 10px;
            text-align: center;
            background: #141460;
            color: white;
            border-radius: 5px;
            text-decoration: none;
        }

        .status {
            text-align: center;
            font-weight: bold;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .status.pending {
            background-color: #fff3cd;
            color: #856404;
        }
        .status.approved {
            background-color: #d4edda;
            color: #155724;
        }
        .status.rejected {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
        <?php include 'sidebar.php'; ?>

    <div class="profile-container">
        <h2>Mon Profil</h2>
        <img class="profile-img" src="<?php echo htmlspecialchars($student['profile_picture'] 
            ? '../uploads/' . $student['profile_picture'] 
            : 'https://cdn.pixabay.com/photo/2015/10/05/22/37/blank-profile-picture-973460_1280.png'); ?>" 
            alt="Photo de profil">
        <div class="status <?php echo htmlspecialchars($student['status']); ?>">
            Statut: <?php echo ucfirst(htmlspecialchars($student['status'])); ?>
        </div>
        <table>
            <tr><td><strong>CIN:</strong></td><td><?php echo htmlspecialchars($student_cin); ?></td></tr>
            <tr><td><strong>Nom Complet:</strong></td><td><?php echo htmlspecialchars($student['name']); ?></td></tr>
            <tr><td><strong>Email:</strong></td><td><?php echo htmlspecialchars($student['email']); ?></td></tr>
            <tr><td><strong>Téléphone:</strong></td><td><?php echo htmlspecialchars($student['phone']); ?></td></tr>
            <tr><td><strong>Genre:</strong></td><td><?php echo htmlspecialchars($student['gender'] === 'male' ? 'Homme' : 'Femme'); ?></td></tr>
            <tr><td><strong>Année d'Étude:</strong></td><td><?php echo htmlspecialchars($student['year_of_study']); ?></td></tr>
            <tr><td><strong>Filière:</strong></td><td><?php echo htmlspecialchars($student['major']); ?></td></tr>
        </table>
        <a class="btn" href="editprofile.php">Modifier Profil</a>
    </div>
</body>
</html>
