<?php
require_once 'koneksi.php';
$id = mysqli_real_escape_string($conn, $_GET['id']);
$status = mysqli_real_escape_string($conn, $_GET['status']);
$validStatus = ['Revisi', 'Selesai', 'Belum di cek'];
if (!in_array($status, $validStatus)) {
    http_response_code(400);
    exit;
}
mysqli_query($conn, "UPDATE task SET status_cek = '$status' WHERE id = '$id'");
