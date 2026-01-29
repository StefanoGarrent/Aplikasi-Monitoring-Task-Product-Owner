<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header("Location: index.php");
    exit;
}

require_once 'services/koneksi.php';

// validasi ID dokumen dan client
if (!isset($_GET['id']) || !isset($_GET['client'])) {
    header("Location: client.php");
    exit;
}

$id = mysqli_real_escape_string($conn, $_GET['id']);
$id_client = mysqli_real_escape_string($conn, $_GET['client']);

// Ambil data dokumen untuk menghapus file jika terdapat file
$dokumenQuery = mysqli_query($conn, "SELECT * FROM dokumen WHERE id = '$id' AND id_client = '$id_client'");
$dokumen = mysqli_fetch_assoc($dokumenQuery);

if ($dokumen) {
    // Hapus file yang sudah ada
    if (!empty($dokumen['file_path']) && file_exists($dokumen['file_path'])) {
        unlink($dokumen['file_path']);
    }

    // Hapus record file dari database
    $delete = mysqli_query($conn, "DELETE FROM dokumen WHERE id = '$id'");
    
    if ($delete) {
        header("Location: view_dokumen.php?id=$id_client&status=deleted");
        exit;
    }
}

// Jika perintah gagal, dikembalikan ke halaman dokumen
header("Location: view_dokumen.php?id=$id_client");
exit;
?>
