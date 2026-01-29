<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header("Location: index.php");
    exit;
}

require_once 'services/koneksi.php';

if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    $query = "DELETE FROM task WHERE id = '$id'";
    mysqli_query($conn, $query);
}

header("Location: task.php");
exit;