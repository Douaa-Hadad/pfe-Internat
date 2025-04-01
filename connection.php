<?php
$host = '127.0.0.1'; // Database host
$username = 'root'; // Database username
$password = ''; // Database password
$database = 'estcasa'; // Database name

// Create a new MySQLi connection
$conn = new mysqli($host, $username, $password, $database);

// Check for connection errors
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
