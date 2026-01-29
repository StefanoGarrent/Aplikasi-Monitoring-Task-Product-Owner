<?php
$hostname = "localhost";
$username = "root";
$password = "";
$database = "mtpo";

$conn = new mysqli($hostname, $username, $password, $database);

if ($conn->connect_error) {
    die("Maaf koneksi error" . $conn->connect_error);
}
?>