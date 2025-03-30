<?php
session_start();
include '../db.php';

// Redirect to login page if no session exists //
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'comptable') {
    header("Location: ../login/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payment_id = intval($_POST['payment_id']);
    $amount = floatval($_POST['amount']);
    $status = strtolower(trim($conn->real_escape_string($_POST['status']))); // Normalize status

    // Debugging: Log received values
    file_put_contents('debug.log', "Payment ID: $payment_id, Amount: $amount, Status: $status\n", FILE_APPEND);

    // Validate input
    if ($payment_id <= 0 || $amount <= 0 || !in_array($status, ['paid', 'not paid', 'pending'])) {
        file_put_contents('debug.log', "Validation failed\n", FILE_APPEND);
        header("Location: manage_payments.php?error=Invalid input");
        exit();
    }

    // Update payment in the database
    $update_query = "UPDATE payments SET amount = ?, status = ? WHERE id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("dsi", $amount, $status, $payment_id);

    if ($stmt->execute()) {
        file_put_contents('debug.log', "Update successful\n", FILE_APPEND);
        header("Location: manage_payments.php?success=Payment updated successfully");
    } else {
        file_put_contents('debug.log', "Update failed: " . $stmt->error . "\n", FILE_APPEND);
        header("Location: manage_payments.php?error=Failed to update payment");
    }

    $stmt->close();
} else {
    header("Location: manage_payments.php");
    exit();
}

$conn->close();
?>
