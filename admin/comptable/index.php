<?php
session_start();
include '../../connection.php';

// Redirect to login page if no session exists or user is not comptable
if (!isset($_SESSION['user_type']) || $_SESSION['user_role'] !== 'comptable') {
    header("Location: ../../login/login.php");
    exit();
}

// Fetch payment statistics
$total_payments_query = "SELECT COUNT(*) AS total_payments FROM payments";
$total_payments_result = $conn->query($total_payments_query);
$total_payments = $total_payments_result->fetch_assoc()['total_payments'];

$total_amount_query = "SELECT SUM(amount) AS total_amount FROM payments";
$total_amount_result = $conn->query($total_amount_query);
$total_amount = $total_amount_result->fetch_assoc()['total_amount'];

// Fetch recent payment activities
$recent_payments_query = "SELECT student_cin, amount, payment_date FROM payments ORDER BY payment_date DESC LIMIT 5";
$recent_payments_result = $conn->query($recent_payments_query);
$recent_payments = [];
while ($row = $recent_payments_result->fetch_assoc()) {
    $recent_payments[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../admin/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <title>Comptable Dashboard</title>
</head>
<body>
    <?php include '../header.php'; ?>
    <?php include 'sidebar.php'; ?>
    <div class="main-content">
        <h2>Dashboard Overview</h2>
        <div class="overview">
            <div>Total Payments: <?php echo $total_payments; ?></div>
            <div>Total Amount: <?php echo number_format($total_amount, 2); ?> MAD</div>
        </div>
        <h2>Recent Payments</h2>
        <div class="recent-activities">
            <table>
                <thead>
                    <tr>
                        <th>Student CIN</th>
                        <th>Amount</th>
                        <th>Payment Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_payments as $payment): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($payment['student_cin']); ?></td>
                            <td><?php echo htmlspecialchars($payment['amount']); ?> MAD</td>
                            <td><?php echo htmlspecialchars($payment['payment_date']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
