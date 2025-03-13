<?php
include '../connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $cin = trim($_POST['cin']);
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $phone = trim($_POST['phone']);
    $gender = isset($_POST['gender']) ? $_POST['gender'] : ''; // Fix missing gender
    $photo = "default-profile.png"; // Default profile picture

    // Check if passwords match
    if ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        // Check for duplicate CIN or Email
        $stmt = $conn->prepare("SELECT email FROM students WHERE email = ? OR cin = ?");
        $stmt->bind_param("ss", $email, $cin);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error = "This email or CIN is already registered.";
        } else {
            // Insert student into database
            $stmt = $conn->prepare("INSERT INTO students (cin, name, email, password, phone, gender, photo) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssss", $cin, $name, $email, $hashed_password, $phone, $gender, $photo);

            if ($stmt->execute()) {
                header("Location: ../login.php");
                exit();
            } else {
                $error = "Error: " . $conn->error;
            }
        }

        $stmt->close();
        $conn->close();
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

        <form action="register.php" method="POST" onsubmit="return checkPasswords()">
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

            <div class="username"> <!-- Now it matches other input fields -->
    <label for="gender">Gender</label>
    <div class="input-container">
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
