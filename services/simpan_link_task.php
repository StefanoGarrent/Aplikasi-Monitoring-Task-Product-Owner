<?php
session_start();
require_once 'koneksi.php';

if (isset($_POST['simpan_relasi'])) {
    $id_dokumen = mysqli_real_escape_string($conn, $_POST['id_dokumen']);
    $id_client = mysqli_real_escape_string($conn, $_POST['id_client']);

    // 1. Reset semua task yang sebelumnya terhubung ke dokumen ini (relasi + status)
    mysqli_query($conn, "UPDATE task SET id_dokumen = NULL, status_cek = 'Belum di cek' WHERE id_dokumen = '$id_dokumen'");

    // 2. Update task yang dipilih (jika ada)
    if (isset($_POST['task_ids']) && is_array($_POST['task_ids'])) {
        foreach ($_POST['task_ids'] as $taskId) {
            $taskId = intval($taskId);

            // Ambil status dari radio button (jika dipilih)
            $status = 'Belum di cek'; // default
            if (isset($_POST['status_task'][$taskId])) {
                $statusInput = mysqli_real_escape_string($conn, $_POST['status_task'][$taskId]);
                if (in_array($statusInput, ['Revisi', 'Selesai'])) {
                    $status = $statusInput;
                }
            }

            // Update relasi dokumen + status per task
            mysqli_query($conn, "UPDATE task SET id_dokumen = '$id_dokumen', status_cek = '$status' WHERE id = $taskId");
        }

        header("Location: ../view_dokumen.php?id=$id_client&status=success");
    } else {
        // Tidak ada task dipilih — semua relasi dihapus (sudah di langkah 1)
        header("Location: ../view_dokumen.php?id=$id_client&status=cleared");
    }
}
