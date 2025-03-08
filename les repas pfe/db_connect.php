<?php
$servername = "localhost";
$username = "root"; // Par défaut avec XAMPP
$password = ""; // Mot de passe vide avec XAMPP
$dbname = "internat_app";

// Connexion à la base de données
$conn = new mysqli($servername, $username, $password, $dbname);

// Vérification de la connexion
if ($conn->connect_error) {
    die("Échec de la connexion : " . $conn->connect_error);
}
?>
