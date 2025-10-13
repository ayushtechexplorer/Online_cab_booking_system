<?php
$servername = "localhost";
$username = "root";
$password = "";  // No password for the root user by default in XAMPP
$dbname = "cab_system";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

