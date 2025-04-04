<?php
session_start();
include '../connection.php'; // Updated path to the correct location

if (!isset($_SESSION['student_cin'])) {
    header("Location: login/login.php");
    exit();
}

$student_cin = $_SESSION['student_cin'];
$student_name = $_SESSION['student_name'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = htmlspecialchars($_POST['name']);
    $email = htmlspecialchars($_POST['email']);
    $complaint = htmlspecialchars($_POST['complaint']);

    // Save the complaint (e.g., to a database or file)
    $file = 'complaints.txt';
    $entry = "Nom: $name\nEmail: $email\nPlainte: $complaint\n---\n";
    file_put_contents($file, $entry, FILE_APPEND);

    echo "<p class='success-message'>Merci pour votre réclamation. Nous allons la traiter bientôt.</p>";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Déposer une réclamation</title>
    <link rel="stylesheet" href="student/style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 800px;
            margin: 40px auto;
            padding: 30px;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        h1 {
            color: #333333;
            margin-bottom: 30px;
            text-align: center;
        }
        label {
            color: #555555;
            font-weight: bold;
            display: block;
            margin-bottom: 8px;
        }
        input, textarea {
            width: 100%;
            padding: 12px;
            margin: 10px 0 20px 0;
            border: 1px solid #cccccc;
            border-radius: 4px;
            font-size: 16px;
            box-sizing: border-box;
        }
        textarea {
            min-height: 200px;
            resize: vertical;
        }
        #submit_complaint {
            display: block;
            width: 600px;
            padding: 12px 20px;
            color: #ffffff;
            background-color: #007bff;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            margin: 30px auto 0;
            transition: background-color 0.3s;
        }
        #submit_complaint:hover {
            background-color: #0056b3;
        }
        .success-message {
            text-align: center;
            color: #28a745;
            font-size: 18px;
            margin: 20px 0;
            padding: 15px;
            background-color: #e8f5e9;
            border-radius: 4px;
        }
    </style>
</head>
<body>
<?php include 'sidebar.php'; ?>
    <div class="container">
        <h1>Déposer une réclamation</h1>
        <?php if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
            <p class="success-message">Merci pour votre réclamation. Nous allons la traiter bientôt.</p>
        <?php endif; ?>
        <form method="POST" action="complains.php">
            <label for="name">Nom:</label>
            <input type="text" id="name" name="name" required>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>

            <label for="complaint">réclamation:</label>
            <textarea id="complaint" name="complaint" required></textarea>

            <button type="submit" id="submit_complaint" name="submit_complaint">Soumettre</button>
        </form>
    </div>
</body>
</html>