<?php 
session_start();
if (!isset($_SESSION['loggedin'])) {
    header("Location: index.php");
    exit;
}

require_once 'services/koneksi.php';

if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    
    $getClient = mysqli_query($conn, "SELECT nama FROM client WHERE id = '$id'");
    
    if ($clientData = mysqli_fetch_assoc($getClient)) {
        $namaFaskes = mysqli_real_escape_string($conn, $clientData['nama']);
        
        mysqli_query($conn, "DELETE FROM task WHERE faskes = '$namaFaskes'");
    }
    
    $query = "DELETE FROM client WHERE id = '$id'";
    mysqli_query($conn, $query);
}

header("Location: client.php");
exit;
?>