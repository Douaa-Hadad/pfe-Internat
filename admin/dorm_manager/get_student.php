<?php
$mysqli = new mysqli("localhost", "root", "", "estcasa");

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

if (isset($_GET['cin'])) {
    $cin = $mysqli->real_escape_string($_GET['cin']);
    $result = $mysqli->query("SELECT * FROM students WHERE cin = '$cin'");

    if ($result->num_rows > 0) {
        $student = $result->fetch_assoc();
        echo json_encode($student);
    } else {
        echo json_encode(['error' => 'Student not found']);
    }
} else {
    echo json_encode(['error' => 'CIN not provided']);
}

$mysqli->close();
?>
