<?php
include '../connection.php';

$cin = $_GET['cin'];
$repas = $_GET['repas'];
$date = $_GET['date'];

// Requête pour récupérer les informations de l'étudiant
$query = "SELECT name, profile_picture FROM students WHERE cin = '$cin'";
$query_repas = "SELECT type_repas FROM repas WHERE id_repas = '$repas'";

// Vérifier si la requête pour l'étudiant a été exécutée avec succès
$result = mysqli_query($conn, $query);
if (!$result) {
    die("Erreur de requête pour l'étudiant : " . mysqli_error($conn));
}

// Vérifier si la requête pour le repas a été exécutée avec succès
$result_repas = mysqli_query($conn, $query_repas);
if (!$result_repas) {
    die("Erreur de requête pour le repas : " . mysqli_error($conn));
}

$row = mysqli_fetch_assoc($result);
$row_repas = mysqli_fetch_assoc($result_repas);

// Vérifier si les résultats existent avant d'accéder aux données
if ($row && $row_repas) {
    $profile_picture = $row['profile_picture'] ? '../admin/dorm_manager/uploads/' . $row['profile_picture'] : '../admin/dorm_manager/uploads/default.png';
    $response = [
        'success' => true,
        'cin' => $cin,
        'nom' => $row['name'],
        'repas' => $row_repas['type_repas'],
        'date' => $date,
        'profile_picture' => $profile_picture // Include the correct path for the image
        // 'profile_picture' => $row['profile_picture']
    ];
} else {
    $response = [
        'success' => false,
        'message' => 'Étudiant ou repas introuvable.'
    ];
}

echo json_encode($response);
?>
