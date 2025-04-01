<?php
include '../connection.php';

$cin = $_GET['cin'];
$repas = $_GET['repas'];
$date = $_GET['date'];

// Check if the ticket exists and is valid
$query_check = "SELECT * FROM tickets WHERE cin = '$cin' AND id_repas = '$repas' AND date = '$date' AND statut = 'valid'";
$result_check = mysqli_query($conn, $query_check);

if (mysqli_num_rows($result_check) > 0) {
    // Update the ticket status to "utilise"
    $query_update = "UPDATE tickets SET statut = 'utilise' WHERE cin = '$cin' AND id_repas = '$repas' AND date = '$date'";
    if (mysqli_query($conn, $query_update)) {
        $response = [
            'success' => true,
            'message' => 'Le ticket a été utilisé avec succès.'
        ];
    } else {
        $response = [
            'success' => false,
            'message' => 'Erreur lors de la mise à jour du statut du ticket.'
        ];
    }
} else {
    $response = [
        'success' => false,
        'message' => 'Le ticket est invalide ou déjà utilisé.'
    ];
}

echo json_encode($response);
?>
