<?php
$mysqli = new mysqli("localhost", "root", "", "estcasa");

if ($mysqli->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Database connection failed.']));
}

$data = json_decode(file_get_contents('php://input'), true);
$cin = $data['cin'];

if ($stmt = $mysqli->prepare("DELETE FROM students WHERE cin = ?")) {
    $stmt->bind_param("s", $cin);
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete student.']);
    }
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to prepare statement.']);
}

$mysqli->close();
?>
