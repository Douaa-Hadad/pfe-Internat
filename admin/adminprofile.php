<?php
session_start();
include '../connection.php';

/* Ensure user is logged in
if (!isset($_SESSION['cin']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'dorm_manager') {
    header("Location: ../login/login.php");
    exit();
}
*/$cin = $_SESSION['cin']; // Get CIN from session

// Fetch user details from 'users' table
$query = "SELECT full_name, email, phone FROM users WHERE cin = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $cin);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    echo "User not found.";
    exit();
}

// Assign values
$name = $user['full_name'];
$email = $user['email'];
$phone = $user['phone'];

// Default profile picture
$photo = "default.jpg"; // Change this if you have a column for profile pictures
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
    </style>
</head>
<body>
    <div class="profile-container">
        <h2>Mon Profil</h2>
        <img class="profile-img" src="<?php echo htmlspecialchars($photo); ?>" alt="Photo de profil">
        <table>
            <tr><td><strong>Nom Complet:</strong></td><td><?php echo htmlspecialchars($name); ?></td></tr>
            <tr><td><strong>Email:</strong></td><td><?php echo htmlspecialchars($email); ?></td></tr>
            <tr><td><strong>Téléphone:</strong></td><td><?php echo htmlspecialchars($phone); ?></td></tr>
        </table>
        <a class="btn" href="../includes/updateProfile.php">Modifier Profil</a>
    </div>
</body>
</html>
