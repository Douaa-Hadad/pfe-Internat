<?php
session_start();
$conn = new mysqli("localhost", "root", "", "estcasa");

// Redirect to login page if no session exists
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: ../login/login.php");
    exit();
}

// Database connection
$conn = new mysqli("localhost", "root", "", "estcasa");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch students with payment status
$sql = "
    SELECT 
        s.cin, s.name, s.email, s.phone, 
        IFNULL(p.status, 'not paid') AS payment_status, 
        IFNULL(p.amount, 0) AS amount, 
        p.payment_date
    FROM students s
    LEFT JOIN payments p ON s.cin = p.student_cin
    ORDER BY s.name ASC
";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion d'internat</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .main-content {
            margin-left: 0; /* Remove sidebar margin if needed */
            padding: 20px;
            width: 100%; /* Make it occupy full width */
        }

        .search-bar {
            margin: 20px auto; /* Adjusted margin for consistency */
            text-align: center;
        }

        .payments-table {
            width: 95%; /* Increase table width */
            max-width: 1100px; /* Adjust max-width */
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
    <?php include 'header.php'; ?>
    <?php include 'sidebar.php'; ?>
    <div class="main-content">
        <div class="payments-table">
            <h2>Payment Status</h2>
            <div class="search-bar">
                <input type="text" id="searchInput" placeholder="Search students by name or CIN...">
                <button type="button" id="searchButton"><i class="fa fa-search"></i></button>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>CIN</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Payment Status</th>
                        <th>Amount</th>
                        <th>Payment Date</th>
                    </tr>
                </thead>
                <tbody>
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
                            <td colspan="7">No data available</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>

<?php
$conn->close();
?>
