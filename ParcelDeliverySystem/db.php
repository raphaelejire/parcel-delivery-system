<?php
// db.php - update credentials if needed
$servername = "localhost";
$username = "root";
$password = ""; // XAMPP default is empty
$database = "parcel_delivery";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    http_response_code(500);
    die("Connection failed: " . $conn->connect_error);
}
$conn->set_charset('utf8mb4');
?>