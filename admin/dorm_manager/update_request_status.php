<?php
session_start();
include '../../connection.php'; // Chemin relatif corrigé

// Rediriger vers la page de connexion si aucune session n'existe ou si l'utilisateur n'est pas un gestionnaire de dortoir
if (!isset($_SESSION['user_type']) || $_SESSION['user_role'] !== 'dorm_manager') {
    header("Location: ../../login/login.php");
    exit();
}

// Vérifier si l'ID de la demande et l'action sont définis
if (isset($_POST['request_id'], $_POST['action'])) {
    $requestId = $_POST['request_id'];
    $action = $_POST['action'];

    // Débogage : Enregistrer l'action et l'ID de la demande
    error_log("Action : $action, ID de la demande : $requestId");

    // Récupérer les détails de la demande de chambre
    $requestQuery = $conn->prepare("
        SELECT student_cin, room_id 
        FROM room_requests 
        WHERE id = ?
    ");
    $requestQuery->bind_param("i", $requestId);
    $requestQuery->execute();
    $requestResult = $requestQuery->get_result();
    $request = $requestResult->fetch_assoc();

    if ($request) {
        if ($action === 'accept') {
            // Mettre à jour le statut de la demande de chambre à 'Acceptée'
            $updateRequestQuery = $conn->prepare("
                UPDATE room_requests 
                SET status = 'Acceptée' 
                WHERE id = ?
            ");
            $updateRequestQuery->bind_param("i", $requestId);
            $updateRequestQuery->execute();

            // Assigner l'ID de la chambre à l'étudiant dans la table des étudiants
            $updateStudentQuery = $conn->prepare("
                UPDATE students 
                SET room_id = ? 
                WHERE cin = ?
            ");
            $updateStudentQuery->bind_param("ss", $request['room_id'], $request['student_cin']);
            $updateStudentQuery->execute();
        } elseif ($action === 'decline') {
            // Mettre à jour le statut de la demande de chambre à 'Rejetée'
            $updateRequestQuery = $conn->prepare("
                UPDATE room_requests 
                SET status = 'Rejetée' 
                WHERE id = ?
            ");
            $updateRequestQuery->bind_param("i", $requestId);
            if ($updateRequestQuery->execute()) {
                // Incrémenter les places occupées pour la chambre
                $incrementRoomSlotsQuery = $conn->prepare("
                    UPDATE rooms 
                    SET occupied_slots = occupied_slots + 1 
                    WHERE id = ?
                ");
                $incrementRoomSlotsQuery->bind_param("s", $request['room_id']);
                if ($incrementRoomSlotsQuery->execute()) {
                    // Enregistrer le succès pour le débogage
                    error_log("Places occupées incrémentées pour l'ID de chambre " . $request['room_id']);
                } else {
                    // Enregistrer l'échec pour le débogage
                    error_log("Échec de l'incrémentation des places occupées pour l'ID de chambre " . $request['room_id'] . ": " . $incrementRoomSlotsQuery->error);
                }

                // Enregistrer le succès pour le débogage
                error_log("ID de demande $requestId rejetée avec succès.");
            } else {
                // Enregistrer l'échec pour le débogage
                error_log("Échec du rejet de l'ID de demande $requestId : " . $updateRequestQuery->error);
            }

            // Rediriger vers la page des demandes de chambre
            header("Location: room_requests.php");
            exit();
        }
    }
} else {
    // Débogage : Enregistrer les données invalides
    error_log("Données invalides : ID de demande ou action non définis.");
    // Rediriger vers la page des demandes de chambre si aucune donnée valide n'est fournie
    header("Location: room_requests.php");
}

$conn->close();
header("Location: room_requests.php");
exit();
?>
