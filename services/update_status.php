<?php
require_once 'koneksi.php';
$id = mysqli_real_escape_string($conn, $_GET['id']);
$status = mysqli_real_escape_string($conn, $_GET['status']);
mysqli_query($conn, "UPDATE task SET status = '$status' WHERE id = '$id'");
?>