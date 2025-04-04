<?php
session_start();
include '../../connection.php';

// Rediriger vers la page de connexion si aucune session n'existe ou si l'utilisateur n'est pas un gestionnaire de dortoir
if (!isset($_SESSION['user_type']) || $_SESSION['user_role'] !== 'dorm_manager') {
    header("Location: ../../login/login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "estcasa");

// Vérifier la connexion
if ($conn->connect_error) {
    die("Échec de la connexion : " . $conn->connect_error);
}

// Récupérer les étudiants avec leur statut de paiement
$sql = "
    SELECT 
        s.cin, s.name, s.email, s.phone, 
        IFNULL(p.frais_d_inscription, 'non payé') AS payment_status, 
        IFNULL(p.amount, 0) AS amount, 
        p.date AS payment_date
    FROM students s
    LEFT JOIN payments p ON s.cin = p.student_cin
    ORDER BY s.name ASC
";
$result = $conn->query($sql);

if (!$result) {
    die("Erreur lors de l'exécution de la requête : " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion d'internat</title>
    <link rel="stylesheet" href="../styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .main-content {
            margin-left: 0; /* Supprimer la marge de la barre latérale si nécessaire */
            padding: 20px;
            width: 100%; /* Occuper toute la largeur */
        }

        .search-bar {
            margin: 20px auto; /* Ajuster la marge pour plus de cohérence */
            text-align: center;
        }

        .payments-table {
            width: 95%; /* Augmenter la largeur de la table */
            max-width: 1100px; /* Ajuster la largeur maximale */
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
    </style>
</head>
<body>
    <?php include '../header.php'; ?>
    <?php include 'sidebar.php'; ?>
    <div class="main-content">
        <div class="payments-table">
            <h2>Statut des paiements</h2>
            <div class="search-bar">
                <input type="text" id="searchInput" placeholder="Rechercher des étudiants par nom ou CIN...">
                <button type="button" id="searchButton"><i class="fa fa-search"></i></button>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>CIN</th>
                        <th>Nom</th>
                        <th>Email</th>
                        <th>Téléphone</th>
                        <th>Statut de paiement</th>
                        <th>Montant</th>
                        <th>Date de paiement</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['cin']); ?></td>
                                <td><?php echo htmlspecialchars($row['name']); ?></td>
                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                <td><?php echo htmlspecialchars($row['phone']); ?></td>
                                <td class="<?php echo $row['payment_status'] === 'paid' ? 'paid' : 'not-paid'; ?>">
                                    <?php echo htmlspecialchars($row['payment_status']); ?>
                                </td>
                                <td><?php echo htmlspecialchars($row['amount']); ?></td>
                                <td><?php echo htmlspecialchars($row['payment_date'] ?? 'N/A'); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7">Aucune donnée disponible</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <script>
        document.getElementById('searchInput').addEventListener('input', function () {
            const filter = this.value.toLowerCase();
            const rows = document.querySelectorAll('#tableBody tr');
            rows.forEach(row => {
                const cin = row.cells[0].textContent.toLowerCase();
                const name = row.cells[1].textContent.toLowerCase();
                if (cin.includes(filter) || name.includes(filter)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>

<?php
$conn->close();
?>
