<?php
session_start();
include '../../connection.php';

// Redirect to login page if no session exists or user is not comptable
if (!isset($_SESSION['user_type']) || $_SESSION['user_role'] !== 'comptable') {
    header("Location: ../../login/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Debugging: Log POST data
    error_log(print_r($_POST, true)); // Logs POST data to the server's error log

    $payment_id = $_POST['payment_id'] ?? null;
    $amount = $_POST['amount'] ?? null;
    $frais_d_inscription = $_POST['frais_d_inscription'] ?? null;
    $trimester_1_status = $_POST['trimester_1_status'] ?? null;
    $trimester_2_status = $_POST['trimester_2_status'] ?? null;
    $trimester_3_status = $_POST['trimester_3_status'] ?? null;

    // Validate input
    if (!$payment_id || (!$amount && !$frais_d_inscription && !$trimester_1_status && !$trimester_2_status && !$trimester_3_status)) {
        die("Error: Missing required fields.");
    }

    if ($amount !== null && (!is_numeric($amount) || $amount < 0)) {
        die("Error: Invalid amount.");
    }

    // Prepare the SQL query dynamically based on the fields being updated
    $fields = [];
    $params = [];
    $types = "";

    if ($amount !== null) {
        $fields[] = "amount=?";
        $params[] = $amount;
        $types .= "d";
    }
    if ($frais_d_inscription !== null) {
        $fields[] = "frais_d_inscription=?";
        $params[] = $frais_d_inscription;
        $types .= "s";
    }
    if ($trimester_1_status !== null) {
        $fields[] = "trimester_1_status=?";
        $params[] = $trimester_1_status;
        $types .= "s";
    }
    if ($trimester_2_status !== null) {
        $fields[] = "trimester_2_status=?";
        $params[] = $trimester_2_status;
        $types .= "s";
    }
    if ($trimester_3_status !== null) {
        $fields[] = "trimester_3_status=?";
        $params[] = $trimester_3_status;
        $types .= "s";
    }

    $params[] = $payment_id;
    $types .= "i";

    $query = "UPDATE payments SET " . implode(", ", $fields) . " WHERE id=?";
    $stmt = $conn->prepare($query);

    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param($types, ...$params);

    if ($stmt->execute()) {
        header("Location: manage_payments.php?update=success");
        exit();
    } else {
        die("Error updating: " . $stmt->error);
    }

    $stmt->close();
} else {
    die("Invalid request.");
}

$conn->close();
?>