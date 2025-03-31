<?php
session_start();
include '../connection.php';

// Inclure la bibliothèque pour générer le QR code
include('lib/phpqrcode/qrlib.php');

if (!isset($_SESSION['user_type']) || $_SESSION['user_role'] !== 'comptable') {
    header("Location: ../../login/login.php");
    exit();
}

// Vérifier si le formulaire a été soumis pour générer des QR codes
if (isset($_POST['submit'])) {
    $date_repas = $_POST['date_repas'];  // Date du repas
    $id_repas = $_POST['id_repas'];  // Type de repas sélectionné

    // Vérifier si la date sélectionnée est dans le passé
    $current_date = date('Y-m-d');
    if ($date_repas < $current_date) {
        echo "Vous ne pouvez pas générer des tickets pour un repas d'une date passée.";
        exit;
    }

    // Vérifier si des tickets ont déjà été générés pour ce repas et cette date
    $query_check_existing = "SELECT * FROM tickets WHERE id_repas = '$id_repas' AND date = '$date_repas'";
    $result_check_existing = mysqli_query($conn, $query_check_existing);

    if (mysqli_num_rows($result_check_existing) > 0) {
        echo "Les tickets pour ce repas ont déjà été générés pour cette date.";
        exit;
    }

    // Récupérer tous les étudiants de la base de données
    $query = "SELECT cin FROM students";
    $result = mysqli_query($conn, $query);

    // Si des étudiants sont trouvés
    if (mysqli_num_rows($result) > 0) {
        // Pour chaque étudiant, générer un ticket QR
        while ($row = mysqli_fetch_assoc($result)) {
            $cin = $row['cin'];

            // Générer un numéro aléatoire pour rendre chaque ticket unique
            $random_number = rand(1000, 9999); 

            // Concaténer les informations pour créer une chaîne unique
            $ticket_info = "CIN: $cin|Repas:$id_repas|Date:$date_repas|Random:$random_number";

            // Générer un identifiant unique pour le ticket
            $ticket_id = uniqid();

            // Chemin pour enregistrer l'image QR
            $file = 'qrcodes/' . $ticket_id . '.png';

            // Créer le répertoire pour les QR codes s'il n'existe pas
            if (!file_exists('qrcodes')) {
                mkdir('qrcodes', 0777, true);
            }

            // Générer le QR code avec les informations du ticket
            QRcode::png($ticket_info, $file, QR_ECLEVEL_L, 10);

            // Insérer le ticket dans la base de données
            $query_insert = "INSERT INTO tickets (cin, id_repas, date, code_qr) VALUES ('$cin', '$id_repas', '$date_repas', '$ticket_id.png')";
            $result_insert = mysqli_query($conn, $query_insert);
        }

        echo "Les tickets ont été générés avec succès pour tous les étudiants !";
    } else {
        echo "Aucun étudiant trouvé dans la base de données.";
    }
}

// Récupérer les types de repas disponibles
$query_repas = "SELECT * FROM repas";
$result_repas = mysqli_query($conn, $query_repas);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Génération des Tickets de Repas</title>
</head>
<body>
    <h1>Génération des Tickets de Repas pour les Étudiants</h1>

    <form method="POST" action="admin_repas.php">
        <label for="date_repas">Sélectionnez la date du repas :</label>
        <input type="date" id="date_repas" name="date_repas" required>
        
        <label for="id_repas">Sélectionnez le type de repas :</label>
        <select name="id_repas" id="id_repas" required>
            <?php while ($row_repas = mysqli_fetch_assoc($result_repas)) : ?>
                <option value="<?php echo $row_repas['id_repas']; ?>"><?php echo $row_repas['type_repas']; ?></option>
            <?php endwhile; ?>
        </select>

        <button type="submit" name="submit">Générer les Tickets</button>
    </form>
</body>
</html>
