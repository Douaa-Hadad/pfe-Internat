<?php
session_start();
include '../connection.php';

if (!isset($_SESSION['student_cin'])) {
    header("Location: ../login/login.php");
    exit();
}

$student_cin = $_SESSION['student_cin'];

// Fetch student details from the database
$query = $conn->prepare("SELECT cin, name, email, phone, gender, year_of_study, major, profile_picture 
                         FROM students WHERE cin = ?");
$query->bind_param("s", $student_cin);
$query->execute();
$result = $query->get_result();
$student = $result->fetch_assoc();

if (!$student || !isset($student['cin'])) {
    echo "Student not found or CIN not available.";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $cin = $_POST['cin'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $year_of_study = $_POST['year_of_study'] ?? '';
    $major = $_POST['major'] ?? '';

    // Handle profile picture upload
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/';
        $file_name = uniqid() . '_' . basename($_FILES['profile_picture']['name']);
        $target_file = $upload_dir . $file_name;

        if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $target_file)) {
            $profile_picture = $file_name;
        } else {
            echo "Failed to upload profile picture.";
            exit();
        }
    } else {
        $profile_picture = $student['profile_picture'];
    }

    // Update student details in the database
    $update_query = $conn->prepare("UPDATE students SET name = ?, cin = ?, email = ?, phone = ?, gender = ?, year_of_study = ?, major = ?, profile_picture = ? WHERE cin = ?");
    $update_query->bind_param("sssssssss", $name, $cin, $email, $phone, $gender, $year_of_study, $major, $profile_picture, $student_cin);

    if ($update_query->execute()) {
        $_SESSION['student_cin'] = $cin; // Update session CIN if changed
        header("Location: userprofile.php");
        exit();
    } else {
        echo "Failed to update profile.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier Profil</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
        }

        .form-container {
            width: 90%;
            max-width: 600px;
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

        form {
            display: flex;
            flex-direction: column;
        }

        label {
            margin-top: 10px;
            font-weight: bold;
        }

        input, select {
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .btn {
            margin-top: 20px;
            padding: 10px;
            background: #141460;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .btn:hover {
            background: #0f0f5a;
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="form-container">
        <h2>Modifier Profil</h2>
        <form method="POST" enctype="multipart/form-data">
            <label for="name">Nom Complet</label>
            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($student['name']); ?>" required>

            <label for="cin">CIN</label>
            <input type="text" id="cin" name="cin" value="<?php echo htmlspecialchars($student['cin'] ?? ''); ?>" required>

            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($student['email']); ?>" required>

            <label for="phone">Téléphone</label>
            <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($student['phone']); ?>" required>

            <label for="gender">Genre</label>
            <select id="gender" name="gender" required>
                <option value="male" <?php echo $student['gender'] === 'male' ? 'selected' : ''; ?>>Homme</option>
                <option value="female" <?php echo $student['gender'] === 'female' ? 'selected' : ''; ?>>Femme</option>
            </select>

            <label for="year_of_study">Année d'Étude</label>
            <input type="text" id="year_of_study" name="year_of_study" value="<?php echo htmlspecialchars($student['year_of_study']); ?>" required>

            <label for="major">Filière</label>
            <input type="text" id="major" name="major" value="<?php echo htmlspecialchars($student['major']); ?>" required>

            <label for="profile_picture">Photo de Profil</label>
            <input type="file" id="profile_picture" name="profile_picture" accept="image/*">

            <button type="submit" class="btn">Enregistrer</button>
        </form>
    </div>
</body>
</html>
