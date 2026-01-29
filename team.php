<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header("Location: index.php");
    exit;
}

require_once 'services/koneksi.php'; 

$filterTim = isset($_GET['tim']) ? mysqli_real_escape_string($conn, $_GET['tim']) : 'ALL';

if ($filterTim == 'ALL' || $filterTim == 'TIM') {
    $query = "SELECT * FROM team ORDER BY tim ASC, nama ASC";
} else {
    $query = "SELECT * FROM team WHERE tim = '$filterTim' ORDER BY nama ASC";
}

$resTeam = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Team Management - Trustmedis</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; }
        select:focus { 
            outline: none; 
            box-shadow: none; 
        }
    </style>
</head>
<body class="bg-[#F0F2F5]">

    <div class="flex min-h-screen">
        <aside class="w-64 bg-[#003674] text-white flex flex-col shadow-xl shrink-0">
            <div class="p-6 mb-4">
                <img src="assets/logo.png" alt="Logo" class="w-full">
                <hr class="mt-4 border-gray-500 opacity-30">
            </div>

            <nav class="flex-1 space-y-1">
                <a href="home.php" class="flex items-center px-6 py-3 hover:bg-[#002b55] transition text-gray-300">
                    <i class="fas fa-home mr-4 w-5 text-center"></i> Home
                </a>
                <a href="team.php" class="flex items-center px-6 py-3 bg-[#00D285] text-white font-semibold">
                    <i class="fas fa-user-friends mr-4 w-5 text-center"></i> Team
                </a>
                <a href="client.php" class="flex items-center px-6 py-3 hover:bg-[#002b55] transition text-gray-300">
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

        <main class="flex-1 p-10">
            <div class="flex justify-between items-end mb-8">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800">Daftar Team</h2>
                    <p class="text-gray-500 mt-1">Kelola data personil Product dan Engineering</p>
                </div>
                <a href="tambah_team.php" class="bg-[#003674] text-white px-5 py-2.5 rounded-lg hover:bg-[#002b55] transition flex items-center shadow-md">
                    <i class="fas fa-plus mr-2 text-sm"></i> Tambah Anggota
                </a>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse">
                        <thead class="bg-[#E9E9F2]">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider w-16">No</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Nama</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Alamat</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">NO HP</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider w-40">
                                    <div class="flex items-center">
                                        <select onchange="window.location.href='team.php?tim=' + this.value" class="bg-transparent border-none font-bold text-gray-600 text-xs uppercase p-0 focus:ring-0 cursor-pointer">
                                            <option value="ALL" <?= $filterTim == 'ALL' ? 'selected' : '' ?>>TIM</option>
                                            <option value="PRODUCT" <?= $filterTim == 'PRODUCT' ? 'selected' : '' ?>>PRODUCT</option>
                                            <option value="ENGINER" <?= $filterTim == 'ENGINER' ? 'selected' : '' ?>>ENGINER</option>
                                        </select>
                                    </div>
                                </th>
                                <th class="px-6 py-4 text-center text-xs font-bold text-gray-600 uppercase tracking-wider w-32">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php 
                            $no = 1;
                            if (mysqli_num_rows($resTeam) > 0):
                                while($row = mysqli_fetch_assoc($resTeam)): 
                            ?>
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 text-sm text-gray-500 font-medium"><?= $no++ ?></td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-semibold text-gray-900"><?= htmlspecialchars($row['nama']) ?></div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    <?= htmlspecialchars($row['alamat']) ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700">
                                    <?= htmlspecialchars($row['no_hp']) ?>
                                </td>
                                <td class="px-6 py-4">
                                    <?php if($row['tim'] == 'PRODUCT'): ?>
                                        <span class="px-3 py-1 text-[10px] font-bold rounded-full bg-emerald-100 text-emerald-700 border border-emerald-200">PRODUCT</span>
                                    <?php else: ?>
                                        <span class="px-3 py-1 text-[10px] font-bold rounded-full bg-blue-100 text-blue-700 border border-blue-200">ENGINER</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-center text-sm font-medium">
                                    <div class="flex justify-center space-x-3">
                                        <a href="edit_team.php?id=<?= $row['id'] ?>" class="text-blue-600 hover:text-blue-800 transition p-1 shadow-sm" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="delete_team.php?id=<?= $row['id'] ?>" 
                                           class="text-red-500 hover:text-red-700 transition p-1 shadow-sm" 
                                           title="Hapus"
                                           onclick="return confirm('Apakah Anda yakin ingin menghapus <?= addslashes($row['nama']) ?> dari sistem? Semua riwayat task terkait akan diset menjadi NULL.')">
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
                                <td colspan="6" class="px-6 py-10 text-center text-gray-400 italic">Data team tidak ditemukan.</td>
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