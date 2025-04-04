<!-- filepath: c:\xampp\htdocs\pfe-Internat\payment-report.php -->
<?php
session_start();
include '../../connection.php';

// Redirect to login page if no session exists or user is not comptable
if (!isset($_SESSION['user_type']) || $_SESSION['user_role'] !== 'comptable') {
    header("Location: ../../login/login.php");
    exit();
}
// Fetch filters from the request
$startDate = $_GET['start_date'] ?? '';
$endDate = $_GET['end_date'] ?? '';
$status = $_GET['status'] ?? '';
$method = $_GET['method'] ?? '';

// Build query with filters
$query = "SELECT * FROM payments WHERE 1=1";
if ($startDate) $query .= " AND date >= '$startDate'";
if ($endDate) $query .= " AND date <= '$endDate'";
if ($status) {
    // Map user-friendly status to database values
    $statusMap = [
        'Paid' => 'paid',
        'Pending' => 'pending',
        'Unpaid' => 'not paid'
    ];
    $dbStatus = $statusMap[$status] ?? $status;
    $query .= " AND (trimester_1_status = '$dbStatus' OR trimester_2_status = '$dbStatus' OR trimester_3_status = '$dbStatus')";
}
if ($method) $query .= " AND method = '$method'";

$result = $conn->query($query);

// Calculate summary with error handling
$totalPaymentsQuery = $conn->query("SELECT SUM(amount) AS total FROM payments");
$totalPayments = $totalPaymentsQuery ? $totalPaymentsQuery->fetch_assoc()['total'] : 0;

$pendingPaymentsQuery = $conn->query("SELECT SUM(amount) AS total FROM payments WHERE trimester_1_status='pending' OR trimester_2_status='pending' OR trimester_3_status='pending'");
$pendingPayments = $pendingPaymentsQuery ? $pendingPaymentsQuery->fetch_assoc()['total'] : 0;

$unpaidPaymentsQuery = $conn->query("SELECT SUM(amount) AS total FROM payments WHERE trimester_1_status='not paid' OR trimester_2_status='not paid' OR trimester_3_status='not paid'");
$unpaidPayments = $unpaidPaymentsQuery ? $unpaidPaymentsQuery->fetch_assoc()['total'] : 0;

$unpaidStudentsQuery = $conn->query("SELECT COUNT(*) AS total FROM payments WHERE trimester_1_status='not paid' OR trimester_2_status='not paid' OR trimester_3_status='not paid'");
$unpaidStudents = $unpaidStudentsQuery ? $unpaidStudentsQuery->fetch_assoc()['total'] : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rapport de Paiement</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        .main-content {
            margin-left: 50px; /* Matches the sidebar width */
        }

    </style>
</head>
<body>
<?php include '../header.php'; ?>
<div class="d-flex">
    <?php include 'sidebar.php'; ?>
    <div class="main-content">
        <div class="container mt-5">
            <h1>Rapport de Paiement</h1>

            <!-- Filters -->
            <form method="GET" class="row g-3 mb-4">
                <div class="col-md-3">
                    <label for="start_date" class="form-label">Date de Début</label>
                    <input type="date" id="start_date" name="start_date" class="form-control" value="<?= $startDate ?>">
                </div>
                <div class="col-md-3">
                    <label for="end_date" class="form-label">Date de Fin</label>
                    <input type="date" id="end_date" name="end_date" class="form-control" value="<?= $endDate ?>">
                </div>
                <div class="col-md-3">
                    <label for="status" class="form-label">Statut</label>
                    <select id="status" name="status" class="form-select">
                        <option value="">Tous</option>
                        <option value="Paid" <?= $status == 'Paid' ? 'selected' : '' ?>>Payé</option>
                        <option value="Pending" <?= $status == 'Pending' ? 'selected' : '' ?>>En Attente</option>
                        <option value="Unpaid" <?= $status == 'Unpaid' ? 'selected' : '' ?>>Non Payé</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="method" class="form-label">Méthode de Paiement</label>
                    <select id="method" name="method" class="form-select">
                        <option value="">Toutes</option>
                        <option value="Credit Card" <?= $method == 'Credit Card' ? 'selected' : '' ?>>Carte de Crédit</option>
                        <option value="PayPal" <?= $method == 'PayPal' ? 'selected' : '' ?>>PayPal</option>
                        <option value="Bank Transfer" <?= $method == 'Bank Transfer' ? 'selected' : '' ?>>Virement Bancaire</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary mt-4">Filtrer</button>
                </div>
            </form>

            <!-- Summary -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card text-white bg-success">
                        <div class="card-body">
                            <h5 class="card-title">Paiements Totaux</h5>
                            <p class="card-text"><?= number_format($totalPayments, 2) ?> MAD</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-white bg-warning">
                        <div class="card-body">
                            <h5 class="card-title">Paiements en Attente</h5>
                            <p class="card-text"><?= number_format($pendingPayments, 2) ?> MAD</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-white bg-danger">
                        <div class="card-body">
                            <h5 class="card-title">Étudiants Non Payés</h5>
                            <p class="card-text"><?= $unpaidStudents ?> students</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Table -->
            <table class="table table-bordered">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Date</th>
                    <th>Client</th>
                    <th>Méthode</th>
                    <th>Montant</th>
                    <th>Statut Trimestre 1</th>
                    <th>Statut Trimestre 2</th>
                    <th>Statut Trimestre 3</th>
                </tr>
                </thead>
                <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= $row['date'] ?></td>
                        <td><?= $row['customer_name'] ?></td>
                        <td><?= $row['method'] ?></td>
                        <td><?= number_format($row['amount'], 2) ?> MAD</td>
                        <td><?= $row['trimester_1_status'] ?></td>
                        <td><?= $row['trimester_2_status'] ?></td>
                        <td><?= $row['trimester_3_status'] ?></td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>