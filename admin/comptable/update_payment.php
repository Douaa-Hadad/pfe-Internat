<?php
session_start();
include '../../connection.php';

// Redirect to login page if no session exists or user is not comptable
if (!isset($_SESSION['user_type']) || $_SESSION['user_role'] !== 'comptable') {
    header("Location: ../../login/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payment_id = $_POST['payment_id'] ?? null;
    $amount = $_POST['amount'] ?? null;
    $status = $_POST['status'] ?? null;

    // Validate input
    if (!$payment_id || !$status) {
        header("Location: manage_payments.php?error=Invalid%20input");
        exit();
    }

    // Handle "not paid" status
    if ($status === 'not paid') {
        $amount = null; // Set amount to null for "not paid" status
    } elseif (!is_numeric($amount) || $amount <= 0) {
        header("Location: manage_payments.php?error=Invalid%20amount");
        exit();
    }

    // Update payment in the database
    $stmt = $conn->prepare("UPDATE payments SET amount = ?, status = ? WHERE id = ?");
    $stmt->bind_param("dsi", $amount, $status, $payment_id);

    if ($stmt->execute()) {
        header("Location: manage_payments.php?success=Payment%20updated");
    } else {
        header("Location: manage_payments.php?error=Database%20error");
    }

    $stmt->close();
} else {
    header("Location: manage_payments.php?error=Invalid%20request");
    exit();
}

$conn->close();
?>
