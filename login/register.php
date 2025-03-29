<?php
include '../connection.php';

// Initialize variables
$error = '';
$success = '';
$form_data = [
    'cin' => '',
    'name' => '',
    'email' => '',
    'phone' => '',
    'gender' => ''
];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and validate input
    $form_data['cin'] = trim($_POST['cin']);
    $form_data['name'] = trim($_POST['name']);
    $form_data['email'] = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $form_data['phone'] = trim($_POST['phone']);
    $form_data['gender'] = $_POST['gender'];

    // Validate inputs
    if (empty($form_data['cin'])) {
        $error = "CIN is required.";
    } elseif (strlen($form_data['cin']) > 10) {
        $error = "CIN must be 10 characters or less.";
    } elseif (empty($form_data['name'])) {
        $error = "Full name is required.";
    } elseif (strlen($form_data['name']) > 255) {
        $error = "Name must be 255 characters or less.";
    } elseif (empty($form_data['email'])) {
        $error = "Email is required.";
    } elseif (!filter_var($form_data['email'], FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } elseif (strlen($form_data['email']) > 255) {
        $error = "Email must be 255 characters or less.";
    } elseif (empty($form_data['phone'])) {
        $error = "Phone number is required.";
    } elseif (strlen($form_data['phone']) > 20) {
        $error = "Phone number must be 20 characters or less.";
    } elseif (empty($form_data['gender'])) {
        $error = "Gender is required.";
    } elseif (!in_array($form_data['gender'], ['male', 'female'])) {
        $error = "Invalid gender selection.";
    } elseif (empty($password)) {
        $error = "Password is required.";
    } elseif (strlen($password) > 255) {
        $error = "Password must be 255 characters or less.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters long.";
    }

    // If no validation errors, proceed
    if (empty($error)) {
        // Check if email or CIN already exists
        $check_stmt = $conn->prepare("SELECT cin FROM students WHERE email = ? OR cin = ?");
        $check_stmt->bind_param("ss", $form_data['email'], $form_data['cin']);
        $check_stmt->execute();
        $check_stmt->store_result();
        
        if ($check_stmt->num_rows > 0) {
            $error = "Email or CIN already registered.";
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);

            // Handle profile picture upload
            $profile_picture = "default-profile.png";
            if (!empty($_FILES['profile_picture']['name'])) {
                $uploadDir = "../uploads/profile_pictures/";
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0775, true);
                }

                $fileExtension = strtolower(pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION));
                $allowedExtensions = ['jpg', 'jpeg', 'png'];
                
                if (in_array($fileExtension, $allowedExtensions)) {
                    if ($_FILES['profile_picture']['size'] > 2000000) {
                        $error = "Profile picture must be less than 2MB.";
                    } else {
                        $fileName = $form_data['cin'] . "_" . time() . "." . $fileExtension;
                        $targetFile = $uploadDir . $fileName;
                        
                        if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $targetFile)) {
                            $profile_picture = $fileName;
                        } else {
                            $error = "Failed to upload profile picture.";
                        }
                    }
                } else {
                    $error = "Invalid file type. Only JPG, JPEG, and PNG allowed.";
                }
            }

            // If still no errors, insert into database
            if (empty($error)) {
                $current_time = date('Y-m-d H:i:s');
                $stmt = $conn->prepare("INSERT INTO students (cin, name, email, password, phone, gender, profile_picture, status, created_at) 
                                      VALUES (?, ?, ?, ?, ?, ?, ?, 'not_applied', ?)");
                $stmt->bind_param("ssssssss", $form_data['cin'], $form_data['name'], $form_data['email'], 
                                $hashed_password, $form_data['phone'], $form_data['gender'], $profile_picture, $current_time);

                if ($stmt->execute()) {
                    $success = "Registration successful! Redirecting to login...";
                    header("Refresh: 2; url=../login/login.php");
                    exit();
                } else {
                    $error = "Database error: " . $conn->error;
                }
            }
        }
        $check_stmt->close();
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

        <?php if(!empty($error)): ?>
            <p style="color:red;"><?php echo $error; ?></p>
        <?php elseif(!empty($success)): ?>
            <p style="color:green;"><?php echo $success; ?></p>
        <?php endif; ?>

        <form action="register.php" method="POST" enctype="multipart/form-data">
            <div class="form-row">
                <div class="username">
                    <label for="cin">CIN</label>
                    <div class="input-container">
                        <ion-icon name="card-outline"></ion-icon>
                        <input type="text" id="cin" name="cin" placeholder="CIN" required maxlength="10"
                               value="<?php echo htmlspecialchars($form_data['cin']); ?>">
                    </div>
                </div>
                <div class="username">
                    <label for="name">Full Name</label>
                    <div class="input-container">
                        <ion-icon name="person-outline"></ion-icon>
                        <input type="text" id="name" name="name" placeholder="Full Name" required maxlength="255"
                               value="<?php echo htmlspecialchars($form_data['name']); ?>">
                    </div>
                </div>
            </div>

            <div class="username"> 
                <label for="gender">Gender</label>
                <div class="input-container gender-dropdown">
                    <ion-icon name="transgender-outline"></ion-icon>
                    <select id="gender" name="gender" required>
                        <option value="" disabled selected>Select Gender</option>
                        <option value="male" <?php echo ($form_data['gender'] === 'male') ? 'selected' : ''; ?>>Male</option>
                        <option value="female" <?php echo ($form_data['gender'] === 'female') ? 'selected' : ''; ?>>Female</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="username">
                    <label for="email">Email</label>
                    <div class="input-container">
                        <ion-icon name="mail-outline"></ion-icon>
                        <input type="email" id="email" name="email" placeholder="Email" required maxlength="255"
                               value="<?php echo htmlspecialchars($form_data['email']); ?>">
                    </div>
                </div>
                <div class="username">
                    <label for="phone">Phone Number</label>
                    <div class="input-container">
                        <ion-icon name="call-outline"></ion-icon>
                        <input type="tel" id="phone" name="phone" placeholder="Phone Number" required maxlength="20"
                               value="<?php echo htmlspecialchars($form_data['phone']); ?>">
                    </div>
                </div>
            </div>

            <div class="password">
                <label for="password">Password</label>
                <div class="input-container">
                    <ion-icon name="lock-closed-outline"></ion-icon>
                    <input type="password" id="password" name="password" placeholder="Password" required minlength="8" maxlength="255">
                    <ion-icon name="eye-outline" class="toggle-password" onclick="togglePassword('password')"></ion-icon>
                </div>
            </div>

            <div class="password">
                <label for="confirm_password">Confirm Password</label>
                <div class="input-container">
                    <ion-icon name="lock-closed-outline"></ion-icon>
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm Password" required maxlength="255">
                    <ion-icon name="eye-outline" class="toggle-password" onclick="togglePassword('confirm_password')"></ion-icon>
                </div>
                <span id="password-match" style="font-size:12px; color:red;"></span>
            </div>

            <div class="username">
                <label for="profile_picture">Profile Picture</label>
                <div class="input-container">
                    <ion-icon name="image-outline"></ion-icon>
                    <input type="file" id="profile_picture" name="profile_picture" accept=".jpg,.jpeg,.png">
                </div>
                <small style="font-size:12px;">Max 2MB (JPG, JPEG, PNG only)</small>
            </div>

            <button type="submit" class="login">Register</button>
        </form>

        <div class="footer">
            <span><a href="../login/login.php">Already have an account? Login</a></span>
        </div>
    </div>

    <script>
        // Password visibility toggle
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const icon = field.nextElementSibling;
            if (field.type === "password") {
                field.type = "text";
                icon.name = "eye-off-outline";
            } else {
                field.type = "password";
                icon.name = "eye-outline";
            }
        }

        // Password match validation
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;
            const matchText = document.getElementById('password-match');
            
            if (confirmPassword.length === 0) {
                matchText.textContent = '';
                return;
            }
            
            if (password === confirmPassword) {
                matchText.textContent = 'Passwords match!';
                matchText.style.color = 'green';
            } else {
                matchText.textContent = 'Passwords do not match!';
                matchText.style.color = 'red';
            }
        });

        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                document.getElementById('password-match').textContent = 'Passwords do not match!';
                document.getElementById('password-match').style.color = 'red';
            }
            
            if (password.length < 8) {
                e.preventDefault();
                alert('Password must be at least 8 characters long!');
            }
        });
    </script>
</body>
</html>