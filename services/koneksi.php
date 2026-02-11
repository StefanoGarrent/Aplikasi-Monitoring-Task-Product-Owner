<?php
$hostname = "localhost";
$username = "root";
$password = "";
$database = "mtpo";

$conn = mysqli_connect($hostname, $username, $password, $database);

if (!$conn) {
    die("Maaf koneksi error: " . mysqli_connect_error());
}
?>

