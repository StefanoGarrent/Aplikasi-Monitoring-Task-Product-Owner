<?php
session_start();
require_once 'koneksi.php';

if (isset($_POST['simpan_relasi'])) {
    $id_dokumen = mysqli_real_escape_string($conn, $_POST['id_dokumen']);
    $id_client = mysqli_real_escape_string($conn, $_POST['id_client']);
    
    // 1. Reset dulu semua task yang sebelumnya terhubung ke dokumen ini menjadi NULL
    // Ini penting untuk menangani task yang di-uncheck
    mysqli_query($conn, "UPDATE task SET id_dokumen = NULL WHERE id_dokumen = '$id_dokumen'");

    // 2. Update task yang baru dipilih (jika ada)
    if (isset($_POST['task_ids']) && is_array($_POST['task_ids'])) {
        $ids = implode(",", array_map('intval', $_POST['task_ids']));
        $queryUpdate = "UPDATE task SET id_dokumen = '$id_dokumen' WHERE id IN ($ids)";
        
        if (mysqli_query($conn, $queryUpdate)) {
            header("Location: ../view_dokumen.php?id=$id_client&status=success");
        } else {
            echo "Error: " . mysqli_error($conn);
        }
    } else {
        // Jika tidak ada checkbox dipilih, berarti semua relasi dihapus (sudah dihandle di langkah 1)
        header("Location: ../view_dokumen.php?id=$id_client&status=cleared");
    }
}
?>