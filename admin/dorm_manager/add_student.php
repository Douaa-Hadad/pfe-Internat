<?php
header('Content-Type: application/json'); // Ensure JSON response
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$mysqli = new mysqli("localhost", "root", "", "estcasa");

if ($mysqli->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

// Debugging: Log received POST and FILES data
file_put_contents('debug.log', "POST Data: " . print_r($_POST, true) . "\n", FILE_APPEND);
file_put_contents('debug.log', "FILES Data: " . print_r($_FILES, true) . "\n", FILE_APPEND);

try {
    $cin = filter_var($_POST['cin'] ?? null, FILTER_SANITIZE_STRING);
    $name = filter_var($_POST['name'] ?? null, FILTER_SANITIZE_STRING);
    $gender = filter_var($_POST['gender'] ?? null, FILTER_SANITIZE_STRING);
    $phone = filter_var($_POST['phone'] ?? null, FILTER_SANITIZE_STRING);
    $email = filter_var($_POST['email'] ?? null, FILTER_VALIDATE_EMAIL);
    $major = filter_var($_POST['major'] ?? null, FILTER_SANITIZE_STRING);
    $year_of_study = filter_var($_POST['year_of_study'] ?? null, FILTER_SANITIZE_STRING);
    $room_id = filter_var($_POST['room_id'] ?? null, FILTER_SANITIZE_NUMBER_INT);

    // Check for missing fields
    $missing_fields = [];
    if (!$cin) $missing_fields[] = "CIN";
    if (!$name) $missing_fields[] = "Name";
    if (!$gender) $missing_fields[] = "Gender";
    if (!$phone) $missing_fields[] = "Phone";
    if (!$email) $missing_fields[] = "Email";
    if (!$major) $missing_fields[] = "Major";
    if (!$year_of_study) $missing_fields[] = "Year of Study";
    if (!$room_id) $missing_fields[] = "Room ID";

    if (!empty($missing_fields)) {
        echo json_encode(['success' => false, 'message' => 'Missing fields: ' . implode(", ", $missing_fields)]);
        exit;
    }

    // Ensure files are uploaded properly
    if (empty($_FILES)) {
        echo json_encode(['success' => false, 'message' => 'No files uploaded. Make sure form has enctype="multipart/form-data"']);
        exit;
    }

    function handleFileUpload($file, $folder, $allowedTypes, $maxSize) {
        if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
            return null; // No file uploaded or error
        }

        $fileName = basename($file['name']);
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $fileSize = $file['size'];
        $uniqueName = uniqid() . "." . $fileExt;
        $targetPath = "$folder/$uniqueName";

        // Validate file type and size
        if (!in_array($fileExt, $allowedTypes) || $fileSize > $maxSize) {
            return null; // Invalid file
        }

        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            return $uniqueName;
        }

        return null; // Upload failed
    }

    $profile_picture = handleFileUpload($_FILES['profile_picture'], "uploads", ["jpg", "png", "jpeg"], 2 * 1024 * 1024); // 2MB limit
    $payment_receipt = handleFileUpload($_FILES['payment_receipt'], "uploads", ["pdf", "jpg", "png"], 2 * 1024 * 1024); 

    // Insert into database
    $query = "INSERT INTO students (cin, name, gender, phone, email, major, year_of_study, room_id, profile_picture, payment_receipt) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $mysqli->prepare($query);
    
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Database error']);
        exit;
    }

    $stmt->bind_param("ssssssssss", $cin, $name, $gender, $phone, $email, $major, $year_of_study, $room_id, $profile_picture, $payment_receipt);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => $stmt->error]);
    }

    $stmt->close();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Unexpected error occurred']);
}

$mysqli->close();
?>
