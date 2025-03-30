<?php
$mysqli = new mysqli("localhost", "root", "", "estcasa");

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $cin = $mysqli->real_escape_string($_POST['cin']);
    $name = $mysqli->real_escape_string($_POST['name']);
    $gender = $mysqli->real_escape_string($_POST['gender']);
    $phone = $mysqli->real_escape_string($_POST['phone']);
    $email = $mysqli->real_escape_string($_POST['email']);
    $major = $mysqli->real_escape_string($_POST['major']);
    $year_of_study = $mysqli->real_escape_string($_POST['year_of_study']);
    $room_id = $mysqli->real_escape_string($_POST['room_id']);

    $profile_picture = $_FILES['profile_picture']['name'];
    $payment_receipt = $_FILES['payment_receipt']['name'];

    // Ensure the uploads directory exists
    if (!is_dir('uploads')) {
        mkdir('uploads', 0777, true);
    }

    // Upload files
    if ($profile_picture) {
        move_uploaded_file($_FILES['profile_picture']['tmp_name'], 'uploads/' . $profile_picture);
    }
    if ($payment_receipt) {
        move_uploaded_file($_FILES['payment_receipt']['tmp_name'], 'uploads/' . $payment_receipt);
    }

    // Update student information
    $query = "UPDATE students SET 
              name='$name', 
              gender='$gender', 
              phone='$phone', 
              email='$email', 
              major='$major', 
              year_of_study='$year_of_study', 
              room_id='$room_id'";
    if ($profile_picture) {
        $query .= ", profile_picture='$profile_picture'";
    }
    if ($payment_receipt) {
        $query .= ", payment_receipt='$payment_receipt'";
    }

    $query .= " WHERE cin='$cin'";

    if ($mysqli->query($query) === TRUE) {
        echo "Record updated successfully";
    } else {
        echo "Error updating record: " . $mysqli->error;
    }
}

$mysqli->close();
?>
