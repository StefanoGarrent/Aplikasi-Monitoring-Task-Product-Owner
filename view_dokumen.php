<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header("Location: index.php");
    exit;
}

require_once 'services/koneksi.php';

// Validasi ID client
if (!isset($_GET['id'])) {
    header("Location: client.php");
    exit;
}

$id_client = mysqli_real_escape_string($conn, $_GET['id']);

// Ambil data client dari database
$clientQuery = mysqli_query($conn, "SELECT * FROM client WHERE id = '$id_client'");
$client = mysqli_fetch_assoc($clientQuery);

if (!$client) {
    header("Location: client.php");
    exit;
}

// Ambil daftar dokumen milik client
$dokumenQuery = mysqli_query($conn, "SELECT * FROM dokumen WHERE id_client = '$id_client' ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dokumen <?= htmlspecialchars($client['nama']) ?> - Trustmedis</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-[#F0F2F5]">

    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <aside class="w-64 bg-[#003674] text-white flex flex-col shadow-xl shrink-0">
            <div class="p-6 mb-4">
                <img src="assets/logo.png" alt="Logo" class="w-full">
                <hr class="mt-4 border-gray-500 opacity-30">
            </div>

            <nav class="flex-1 space-y-1">
                <a href="home.php" class="flex items-center px-6 py-3 hover:bg-[#002b55] transition text-gray-300">
                    <i class="fas fa-home mr-4 w-5 text-center"></i> Home
                </a>
                <a href="team.php" class="flex items-center px-6 py-3 hover:bg-[#002b55] transition text-gray-300">
                    <i class="fas fa-user-friends mr-4 w-5 text-center"></i> Team
                </a>
                <a href="client.php" class="flex items-center px-6 py-3 bg-[#00D285] text-white font-semibold shadow-lg">
                    <i class="fas fa-hospital mr-4 w-5 text-center"></i> Client
                </a>
                <a href="task.php" class="flex items-center px-6 py-3 hover:bg-[#002b55] transition text-gray-300">
                    <i class="fas fa-clipboard-list mr-4 w-5 text-center"></i> Task
                </a>
            </nav>

            <div class="p-6 border-t border-gray-500 border-opacity-30">
                <a href="services/logout.php" class="flex items-center text-gray-300 hover:text-white transition">
                    <i class="fas fa-sign-out-alt mr-4 w-5 text-center"></i> Logout
                </a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 p-10">
            <!-- Header -->
            <div class="flex justify-between items-end mb-8">
                <div>
                    <a href="client.php" class="text-blue-600 hover:underline text-sm mb-2 inline-block">
                        <i class="fas fa-arrow-left mr-1"></i> Kembali ke Daftar Client
                    </a>
                    <h2 class="text-2xl font-bold text-gray-800">Dokumen Client</h2>
                    <p class="text-gray-500 mt-1">
                        <i class="fas fa-hospital mr-1"></i> <?= htmlspecialchars($client['nama']) ?> - <?= htmlspecialchars($client['kota']) ?>
                    </p>
                </div>
                <a href="tambah_dokumen.php?id=<?= $id_client ?>" class="bg-[#003674] text-white px-5 py-2.5 rounded-lg hover:bg-[#002b55] transition flex items-center shadow-md">
                    <i class="fas fa-plus mr-2 text-sm"></i> Tambah Dokumen
                </a>
            </div>

            <!-- Status Messages -->
            <?php if(isset($_GET['status'])): ?>
                <?php if($_GET['status'] == 'added'): ?>
                    <div class="mb-6 p-4 rounded-lg bg-green-50 border border-green-200 text-green-700 flex items-center text-sm">
                        <i class="fas fa-check-circle mr-3 text-lg"></i>
                        Dokumen berhasil ditambahkan!
                    </div>
                <?php elseif($_GET['status'] == 'updated'): ?>
                    <div class="mb-6 p-4 rounded-lg bg-blue-50 border border-blue-200 text-blue-700 flex items-center text-sm">
                        <i class="fas fa-check-circle mr-3 text-lg"></i>
                        Dokumen berhasil diperbarui!
                    </div>
                <?php elseif($_GET['status'] == 'deleted'): ?>
                    <div class="mb-6 p-4 rounded-lg bg-yellow-50 border border-yellow-200 text-yellow-700 flex items-center text-sm">
                        <i class="fas fa-trash mr-3 text-lg"></i>
                        Dokumen berhasil dihapus!
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <!-- Table Dokumen -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse">
                        <thead class="bg-[#E9E9F2]">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider w-16">NO</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">JUDUL DOKUMEN</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider w-32">JENIS</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">LINK</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">FILE</th>
                                <th class="px-6 py-4 text-center text-xs font-bold text-gray-600 uppercase tracking-wider w-32">AKSI</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php 
                            $no = 1;
                            if (mysqli_num_rows($dokumenQuery) > 0):
                                while($row = mysqli_fetch_assoc($dokumenQuery)):
                            ?>
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 text-sm text-gray-500 font-medium"><?= $no++ ?></td>
                                <td class="px-6 py-4 text-sm font-semibold text-gray-900">
                                    <?= htmlspecialchars($row['judul']) ?>
                                </td>
                                <td class="px-6 py-4">
                                    <?php if($row['jenis'] == 'UAT'): ?>
                                        <span class="px-3 py-1 text-[10px] font-bold rounded-full bg-blue-50 text-blue-700 border border-blue-200">
                                            UAT
                                        </span>
                                    <?php else: ?>
                                        <span class="px-3 py-1 text-[10px] font-bold rounded-full bg-orange-50 text-orange-700 border border-orange-200">
                                            MOM
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <?php if(!empty($row['doc_url'])): ?>
                                        <a href="<?= htmlspecialchars($row['doc_url']) ?>" target="_blank" class="text-blue-600 hover:text-blue-800 hover:underline flex items-center">
                                            <i class="fas fa-external-link-alt mr-2"></i> Buka Link
                                        </a>
                                    <?php else: ?>
                                        <span class="text-gray-400">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <?php if(!empty($row['file_path'])): ?>
                                        <a href="<?= htmlspecialchars($row['file_path']) ?>" target="_blank" class="text-green-600 hover:text-green-800 hover:underline flex items-center">
                                            <i class="fas fa-file-download mr-2"></i> Download
                                        </a>
                                    <?php else: ?>
                                        <span class="text-gray-400">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-center text-sm font-medium">
                                    <div class="flex justify-center space-x-3">
                                        <a href="edit_dokumen.php?id=<?= $row['id'] ?>&client=<?= $id_client ?>" class="text-blue-600 hover:text-blue-800 transition" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="delete_dokumen.php?id=<?= $row['id'] ?>&client=<?= $id_client ?>" 
                                           class="text-red-500 hover:text-red-700 transition" 
                                           onclick="return confirm('Yakin ingin menghapus dokumen <?= addslashes($row['judul']) ?>?')" title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php 
                                endwhile; 
                            else:
                            ?>
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center text-gray-400">
                                        <i class="fas fa-folder-open text-4xl mb-3"></i>
                                        <p class="italic font-medium">Belum ada dokumen untuk client ini.</p>
                                        <a href="tambah_dokumen.php?id=<?= $id_client ?>" class="text-blue-500 text-sm mt-2 hover:underline">
                                            <i class="fas fa-plus mr-1"></i> Tambah Dokumen Pertama
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div> 
        </main>
    </div>

</body>
</html>
