<?php
session_start();
include '../connection.php';

// Vérifier si l'étudiant est connecté
if (!isset($_SESSION['student_cin'])) {
    header("Location: ../login/login.php");
    exit();
}

// Connexion à la base de données


// Récupérer le CIN de l'étudiant connecté depuis la session
$cin = $_SESSION['student_cin'];

// Récupérer la date d'aujourd'hui
$date_aujourdhui = date('Y-m-d');

// Récupérer les tickets de repas de l'étudiant pour le jour courant
$query = "
    SELECT t.code_qr, r.type_repas, t.date 
    FROM tickets t 
    INNER JOIN repas r ON t.id_repas = r.id_repas 
    WHERE t.cin = '$cin' AND t.date = '$date_aujourdhui'
";
$result = mysqli_query($conn, $query);

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mes Tickets de Repas</title>
</head>
<body>
    <h1>Mes Tickets de Repas pour Aujourd'hui</h1>

    <?php
    // Vérifier si l'étudiant a des tickets pour le jour courant
    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $code_qr = $row['code_qr'];
            $type_repas = $row['type_repas'];
            $date_repas = $row['date'];

            echo "<h3>$type_repas du $date_repas</h3>";
            echo "<img src='qrcodes/$code_qr' alt='QR Code $type_repas' />";
            echo "<p>Scannez ce code pour récupérer votre repas.</p>";
        }
    } else {
        echo "<p>Vous n'avez pas de repas pour aujourd'hui.</p>";
    }
    ?>
</body>
</html>
