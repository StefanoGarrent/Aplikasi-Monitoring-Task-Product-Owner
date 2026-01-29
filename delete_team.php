<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header("Location: index.php");
    exit;
}

require_once 'services/koneksi.php';

if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    
    $getTeam = mysqli_query($conn, "SELECT nama, tim FROM team WHERE id = '$id'");
    
    if ($teamData = mysqli_fetch_assoc($getTeam)) {
        $namaTeam = mysqli_real_escape_string($conn, $teamData['nama']);
        $timType = $teamData['tim'];
        
        mysqli_query($conn, "SET FOREIGN_KEY_CHECKS=0");
        
        if ($timType == 'PRODUCT') {
            mysqli_query($conn, "UPDATE task SET product = '-' WHERE product = '$namaTeam'");
        } elseif ($timType == 'ENGINER') {
            mysqli_query($conn, "UPDATE task SET enginer = '-' WHERE enginer = '$namaTeam'");
        }
        
        $query = "DELETE FROM team WHERE id = '$id'";
        mysqli_query($conn, $query);
        
        mysqli_query($conn, "SET FOREIGN_KEY_CHECKS=1");
    }
}

header("Location: team.php");
exit;