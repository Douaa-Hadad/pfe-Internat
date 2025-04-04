<?php
session_start();
include '../../connection.php';

// Redirect to login page if no session exists or user is not comptable
if (!isset($_SESSION['user_type']) || $_SESSION['user_role'] !== 'comptable') {
    header("Location: ../../login/login.php");
    exit();
}

// Fetch all payments with student details
$payments_query = "
    SELECT p.id, p.student_cin, s.name AS student_name, p.amount, p.frais_d_inscription, p.date, 
           p.trimester_1_status, p.trimester_2_status, p.trimester_3_status 
    FROM payments p
    INNER JOIN students s ON p.student_cin = s.cin -- Join with students table
    ORDER BY p.date DESC";
$payments_result = $conn->query($payments_query);

if (!$payments_result) {
    die("Error fetching payments: " . $conn->error);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <title>Gérer les Paiements</title>
    <style>
        .main-content {
            margin-left: 0;
            padding: 20px;
            width: 100%;
        }
        .search-bar {
            margin: 20px auto;
            text-align: center;
        }
        .payments-table {
            width: 95%;
            max-width: 1100px;
            margin: 20px auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
            overflow-x: auto;
        }
        .payments-table h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #141460;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 16px;
        }
        thead {
            background: #141460;
            color: white;
        }
        th, td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
            text-align: center;
            vertical-align: middle;
        }
        table tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        table tr:hover {
            background-color: #ddd;
        }
        .not-paid {
            color: red;
            font-weight: bold;
        }
        .paid {
            color: green;
            font-weight: bold;
        }
        .edit-btn {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 5px;
            cursor: pointer;
        }
        .edit-btn:hover {
            background-color: #45a049;
        }
        .contact-btn {
            background-color: #007BFF;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 5px;
            cursor: pointer;
        }
        .contact-btn:hover {
            background-color: #0056b3;
        }
        .modal {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
            z-index: 1000;
            width: 400px;
        }
        .modal-header {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .modal-footer {
            margin-top: 20px;
            text-align: right;
        }
        .modal-footer button {
            padding: 5px 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .modal-footer .save-btn {
            background-color: #4CAF50;
            color: white;
        }
        .modal-footer .cancel-btn {
            background-color: #f44336;
            color: white;
        }
        .modal-footer .send-btn {
            background-color: #4CAF50;
            color: white;
        }
        .overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }
    </style>
</head>
<body>
    <?php include '../../admin/header.php'; ?>
    <?php include 'sidebar.php'; ?>
    <div class="main-content">
        <div class="payments-table">
            <h2>Gérer les Paiements</h2>
            <div class="search-bar">
                <input type="text" id="searchInput" placeholder="Rechercher des paiements par CIN ou nom de l'étudiant...">
                <button type="button" id="searchButton"><i class="fa fa-search"></i></button>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>CIN Étudiant</th>
                        <th>Nom Étudiant</th>
                        <th>Montant</th>
                        <th>Frais d'Inscription</th>
                        <th>Date de Paiement</th>
                        <th>Trimestre 1</th>
                        <th>Trimestre 2</th>
                        <th>Trimestre 3</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    <?php if ($payments_result->num_rows > 0): ?>
                        <?php while ($payment = $payments_result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($payment['student_cin']); ?></td>
                                <td><?php echo htmlspecialchars($payment['student_name']); ?></td> <!-- Use student name from students table -->
                                <td><?php echo htmlspecialchars($payment['amount']); ?> MAD</td>
                                <td class="<?php echo $payment['frais_d_inscription'] === 'paid' ? 'paid' : 'not-paid'; ?>">
                                    <?php echo htmlspecialchars($payment['frais_d_inscription']); ?>
                                </td>
                                <td><?php echo htmlspecialchars($payment['date']); ?></td>
                                <td class="<?php echo $payment['trimester_1_status'] === 'paid' ? 'paid' : 'not-paid'; ?>">
                                    <?php echo htmlspecialchars($payment['trimester_1_status']); ?>
                                </td>
                                <td class="<?php echo $payment['trimester_2_status'] === 'paid' ? 'paid' : 'not-paid'; ?>">
                                    <?php echo htmlspecialchars($payment['trimester_2_status']); ?>
                                </td>
                                <td class="<?php echo $payment['trimester_3_status'] === 'paid' ? 'paid' : 'not-paid'; ?>">
                                    <?php echo htmlspecialchars($payment['trimester_3_status']); ?>
                                </td>
                                <td>
                                    <button class="edit-btn" onclick="openEditModal(<?php echo htmlspecialchars(json_encode($payment)); ?>)">Modifier</button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9">No data available</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Edit Payment Modal -->
    <div class="modal" id="editModal">
        <div class="modal-header">Modifier le Paiement</div>
        <form id="editForm" method="POST" action="update_payment.php">
            <input type="hidden" name="payment_id" id="paymentId">
            <div class="form-group">
                <label for="editAmount">Montant :</label>
                <input type="number" name="amount" id="editAmount" class="form-control" step="0.01" min="0">
            </div>
            <div class="form-group">
                <label for="editFraisInscription">Frais d'Inscription :</label>
                <select name="frais_d_inscription" id="editFraisInscription" class="form-control" required>
                    <option value="paid">Payé</option>
                    <option value="unpaid">Non Payé</option>
                </select>
            </div>
            <div class="form-group">
                <label for="editTrimester1">Statut Trimestre 1 :</label>
                <select name="trimester_1_status" id="editTrimester1" class="form-control" required>
                    <option value="paid">Payé</option>
                    <option value="not paid">Non Payé</option>
                    <option value="pending">En Attente</option>
                </select>
            </div>
            <div class="form-group">
                <label for="editTrimester2">Statut Trimestre 2 :</label>
                <select name="trimester_2_status" id="editTrimester2" class="form-control" required>
                    <option value="paid">Payé</option>
                    <option value="not paid">Non Payé</option>
                    <option value="pending">En Attente</option>
                </select>
            </div>
            <div class="form-group">
                <label for="editTrimester3">Statut Trimestre 3 :</label>
                <select name="trimester_3_status" id="editTrimester3" class="form-control" required>
                    <option value="paid">Payé</option>
                    <option value="not paid">Non Payé</option>
                    <option value="pending">En Attente</option>
                </select>
            </div>
            <div class="modal-footer">
                <button type="button" class="cancel-btn" onclick="closeEditModal()">Annuler</button>
                <button type="submit" class="save-btn">Enregistrer</button>
            </div>
        </form>
    </div>

    <div class="overlay" id="overlay" onclick="closeEditModal()"></div>

    <script>
        function openEditModal(payment) {
            document.getElementById('paymentId').value = payment.id;
            document.getElementById('editAmount').value = payment.amount;
            document.getElementById('editFraisInscription').value = payment.frais_d_inscription.toLowerCase();
            document.getElementById('editTrimester1').value = payment.trimester_1_status.toLowerCase();
            document.getElementById('editTrimester2').value = payment.trimester_2_status.toLowerCase();
            document.getElementById('editTrimester3').value = payment.trimester_3_status.toLowerCase();

            document.getElementById('editModal').style.display = 'block';
            document.getElementById('overlay').style.display = 'block';
        }

        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
            document.getElementById('overlay').style.display = 'none';
        }

        // Check for the 'update' query parameter in the URL
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('update') === 'success') {
            alert('Mise à jour réussie !');
            // Remove the query parameter from the URL
            window.history.replaceState({}, document.title, window.location.pathname);
        }
    </script>
</body>
</html>