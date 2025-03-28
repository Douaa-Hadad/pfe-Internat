<?php
include '../connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $cin = trim($_POST['cin']);
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $phone = trim($_POST['phone']);
    $gender = $_POST['gender'];

    // ✅ Check if passwords match
    if ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        // ✅ Handle Profile Picture Upload
        $uploadDir = "../uploads/profile_pictures/";
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0775, true);
        }

        $profile_picture = "default-profile.png"; // Default profile pic
        if (!empty($_FILES['profile_picture']['name'])) {
            $fileExtension = pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION);
            $allowedExtensions = ['jpg', 'jpeg', 'png'];
            
            if (in_array(strtolower($fileExtension), $allowedExtensions)) {
                $fileName = $cin . "_" . time() . "." . $fileExtension; // Unique file name
                $targetFile = $uploadDir . $fileName;
                
                if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $targetFile)) {
                    $profile_picture = $fileName;
                } else {
                    $error = "Failed to upload profile picture.";
                }
            } else {
                $error = "Invalid file type. Only JPG, JPEG, and PNG allowed.";
            }
        }

        // ✅ Insert into database only if no errors
        if (!isset($error)) {
            $stmt = $conn->prepare("INSERT INTO students (cin, name, email, password, phone, gender, profile_picture, status)
                                    VALUES (?, ?, ?, ?, ?, ?, ?, 'not_applied')");
            $stmt->bind_param("sssssss", $cin, $name, $email, $hashed_password, $phone, $gender, $profile_picture);

            if ($stmt->execute()) {
                header("Location: ../login/login.php");
                exit();
            } else {
                $error = "Error: " . $conn->error;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Registration</title>
    <link rel="stylesheet" href="register.css">
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <img src="../logo.png" alt="logo">
        </div>

        <h2>Register</h2>

        <?php if(isset($error)) echo "<p style='color:red;'>$error</p>"; ?>

        <form action="register.php" method="POST" enctype="multipart/form-data" onsubmit="return checkPasswords()">
            <div class="form-row">
                <div class="username">
                    <label for="cin">CIN</label>
                    <div class="input-container">
                        <ion-icon name="card-outline"></ion-icon>
                        <input type="text" id="cin" name="cin" placeholder="CIN" required>
                    </div>
                </div>
                <div class="username">
                    <label for="name">Full Name</label>
                    <div class="input-container">
                        <ion-icon name="person-outline"></ion-icon>
                        <input type="text" id="name" name="name" placeholder="Full Name" required>
                    </div>
                </div>
            </div>

            <!-- ✅ Gender Dropdown Styled Properly -->
            <div class="username"> 
                <label for="gender">Gender</label>
                <div class="input-container gender-dropdown"> <!-- Added class for targeting -->
                    <select id="gender" name="gender" required>
                        <option value="" disabled selected>Select Gender</option>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                    </select>
                </div>
            </div>


            <div class="form-row">
                <div class="username">
                    <label for="email">Email</label>
                    <div class="input-container">
                        <ion-icon name="mail-outline"></ion-icon>
                        <input type="email" id="email" name="email" placeholder="Email" required>
                    </div>
                </div>
                <div class="username">
                    <label for="phone">Phone Number</label>
                    <div class="input-container">
                        <ion-icon name="call-outline"></ion-icon>
                        <input type="text" id="phone" name="phone" placeholder="Phone Number" required>
                    </div>
                </div>
            </div>

            <div class="password">
                <label for="password">Password</label>
                <div class="input-container">
                    <ion-icon name="lock-closed-outline"></ion-icon>
                    <input type="password" id="password" name="password" placeholder="Password" required>
                </div>
            </div>

            <div class="password">
                <label for="confirm_password">Confirm Password</label>
                <div class="input-container">
                    <ion-icon name="lock-closed-outline"></ion-icon>
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm Password" required>
                </div>
            </div>

            <!-- ✅ Profile Picture Upload -->
            <div class="username">
                <label for="profile_picture">Profile Picture</label>
                <div class="input-container">
                    <input type="file" id="profile_picture" name="profile_picture" accept=".jpg, .jpeg, .png">
                </div>
            </div>

            <button type="submit" class="login">Register</button>
        </form>

        <div class="footer">
            <span><a href="../login/login.php">Already have an account? Login</a></span>
        </div>
    </div>

    <script>
        function checkPasswords() {
            let password = document.getElementById("password").value;
            let confirmPassword = document.getElementById("confirm_password").value;

            if (password !== confirmPassword) {
                alert("Passwords do not match!");
                return false;
            }
            return true;
        }
    </script>
</body>
</html>
