<?php
$host = "localhost";       // Database host (e.g., localhost)
$dbname = "inventory_pilar"; // Database name
$db_user = "root"; // Database username
$db_pass = ""; // Database password

// Create a connection
$conn = new mysqli($host, $db_user, $db_pass, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
