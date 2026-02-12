<?php
require_once 'koneksi.php';

if (isset($_GET['client_id']) && isset($_GET['doc_id'])) {
    $clientId = mysqli_real_escape_string($conn, $_GET['client_id']);
    $docId = mysqli_real_escape_string($conn, $_GET['doc_id']);

    // 1. Ambil Nama Client
    $qClient = mysqli_query($conn, "SELECT nama FROM client WHERE id = '$clientId'");
    $rowClient = mysqli_fetch_assoc($qClient);
    $namaFaskes = $rowClient['nama'];

    // 2. Query Task: 
    // Ambil task milik faskes ini
    // DAN (task tersebut belum punya dokumen ATAU task tersebut milik dokumen ini)
    // DAN (task belum selesai)
    $query = "SELECT id, fitur, jenis, id_dokumen FROM task 
              WHERE faskes = '$namaFaskes' 
              AND (id_dokumen IS NULL OR id_dokumen = '$docId')
              AND status_cek != 'Selesai'
              ORDER BY id DESC";

    $result = mysqli_query($conn, $query);

    $tasks = [];
    while ($row = mysqli_fetch_assoc($result)) {
        // Tandai jika task ini milik dokumen yang sedang dibuka
        $row['checked'] = ($row['id_dokumen'] == $docId);
        $tasks[] = $row;
    }

    echo json_encode($tasks);
}
