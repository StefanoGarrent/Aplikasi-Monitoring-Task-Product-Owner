<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header("Location: index.php");
    exit;
}

require_once 'services/koneksi.php'; 

$filterTipe = isset($_GET['tipe']) ? mysqli_real_escape_string($conn, $_GET['tipe']) : 'ALL';
$filterKota = isset($_GET['kota']) ? mysqli_real_escape_string($conn, $_GET['kota']) : 'ALL';

$conditions = [];

if ($filterTipe !== 'ALL' && $filterTipe !== 'TIPE') {
    $conditions[] = "tipe = '$filterTipe'";
}

if ($filterKota !== 'ALL' && $filterKota !== 'KOTA') {
    $conditions[] = "kota = '$filterKota'";
}

$limit = 10; 
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

$whereSQL = '';
if (count($conditions) > 0) {
    $whereSQL = 'WHERE ' . implode(' AND ', $conditions);
}
$query = "SELECT * FROM client $whereSQL ORDER BY nama ASC LIMIT $limit OFFSET $offset";
$resClient = mysqli_query($conn, $query);

$countQuery = "SELECT COUNT(*) AS total FROM client $whereSQL";
$countResult = mysqli_query($conn, $countQuery);
$countRow = mysqli_fetch_assoc($countResult);
$totalData = $countRow['total'];
$totalPages = ceil($totalData / $limit);


?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Management - Trustmedis</title>
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

        <main class="flex-1 p-10">
            <div class="flex justify-between items-end mb-8">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800">Daftar Client</h2>
                    <p class="text-gray-500 mt-1">Kelola data client faskes dan PIC terkait</p>
                </div>
                <a href="tambah_client.php" class="bg-[#003674] text-white px-5 py-2.5 rounded-lg hover:bg-[#002b55] transition flex items-center shadow-md">
                    <i class="fas fa-plus mr-2 text-sm"></i> Tambah Client
                </a>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse">
                        <thead class="bg-[#E9E9F2]">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider w-16">NO</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">NAMA</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">ALAMAT</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider w-44">
                                    <div class="flex items-center">
                                        <select onchange="window.location.href='client.php?tipe=<?= $filterTipe ?>&kota=' + this.value" 
                                                class="bg-transparent border-none font-bold text-gray-600 text-xs uppercase p-0 focus:ring-0 cursor-pointer">
                                            <option value="ALL" <?= $filterKota == 'ALL' ? 'selected' : '' ?>>KOTA (SEMUA)</option>
                                            <?php
                                            $kotaResult = mysqli_query($conn, "SELECT DISTINCT kota FROM client ORDER BY kota ASC");
                                            while ($kotaRow = mysqli_fetch_assoc($kotaResult)):
                                            ?>
                                            <option value="<?= htmlspecialchars($kotaRow['kota']) ?>" <?= $filterKota == $kotaRow['kota'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($kotaRow['kota']) ?>
                                            </option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider w-40">
                                    <div class="flex items-center">
                                        <select onchange="window.location.href='client.php?kota=<?= $filterKota ?>&tipe=' + this.value" 
                                                class="bg-transparent border-none font-bold text-gray-600 text-xs uppercase p-0 focus:ring-0 cursor-pointer">
                                            <option value="ALL" <?= $filterTipe == 'ALL' ? 'selected' : '' ?>>TIPE (SEMUA)</option>
                                            <option value="A" <?= $filterTipe == 'A' ? 'selected' : '' ?>>TIPE A</option>
                                            <option value="B" <?= $filterTipe == 'B' ? 'selected' : '' ?>>TIPE B</option>
                                            <option value="C" <?= $filterTipe == 'C' ? 'selected' : '' ?>>TIPE C</option>
                                            <option value="PRATAMA" <?= $filterTipe == 'PRATAMA' ? 'selected' : '' ?>>TIPE PRATAMA</option>
                                        </select>
                                    </div>
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">PIC</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">NO PIC</th>
                                <th class="px-6 py-4 text-center text-xs font-bold text-gray-600 uppercase tracking-wider w-32">Dokumen</th>
                                <th class="px-6 py-4 text-center text-xs font-bold text-gray-600 uppercase tracking-wider w-32">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php 
                            $no = 1;
                            if (mysqli_num_rows($resClient) > 0):
                                while($row = mysqli_fetch_assoc($resClient)):
                            ?>
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 text-sm text-gray-500 font-medium"><?= $no++ ?></td>
                                <td class="px-6 py-4 text-sm font-semibold text-gray-900">
                                    <?= htmlspecialchars($row['nama']) ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    <?= htmlspecialchars($row['alamat']) ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700 font-medium">
                                    <i class="fas text-red-400 mr-2"></i><?= htmlspecialchars($row['kota']) ?>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-3 py-1 text-[10px] font-bold rounded-full bg-purple-50 text-purple-700 border border-purple-200">
                                        <?= htmlspecialchars($row['tipe']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm font-semibold text-gray-800 uppercase">
                                    <?= htmlspecialchars($row['pic']) ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    <i class="fab text-green-500 mr-1"></i><?= htmlspecialchars($row['no_pic']) ?>
                                </td>

                                <td class="px-6 py-4 text-center text-sm font-medium">
                                    <div class="flex justify-center space-x-3">
                                        <a href="view_dokumen.php?id=<?= $row['id'] ?>" class="text-green-600 hover:text-green-800 transition" title="Lihat Dokumen">
                                            <i class="fas fa-folder-open"></i>
                                        </a>
                                        <a href="tambah_dokumen.php?id=<?= $row['id'] ?> ">
                                            <i class="fas fa-plus text-blue-600 hover:text-blue-800 transition" title="Tambah Dokumen"></i>
                                        </a>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center text-sm font-medium">
                                    <div class="flex justify-center space-x-3">
                                        <a href="edit_client.php?id=<?= $row['id'] ?>" class="text-blue-600 hover:text-blue-800 transition" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="delete_client.php?id=<?= $row['id'] ?>" 
                                           class="text-red-500 hover:text-red-700 transition" 
                                           onclick="return confirm('Yakin ingin menghapus <?= addslashes($row['nama']) ?>?')" title="Hapus">
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
                                <td colspan="8" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center text-gray-400">
                                        <i class="fas fa-folder-open text-4xl mb-3"></i>
                                        <p class="italic font-medium">Data client tidak ditemukan untuk filter ini.</p>
                                        <a href="client.php" class="text-blue-500 text-sm mt-2 hover:underline">Reset Filter</a>
                                    </div>
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div> 

            <?php if ($totalPages > 1): ?>
                <div class="mt-6 flex justify-between items-center bg-white p-4 rounded-lg border border-gray-200 shadow-sm">
                    <div class="text-xs text-gray-500 font-medium">
                        Menampilkan <span class="text-gray-800"><?= $offset + 1 ?></span> - <span class="text-gray-800"><?= min($offset + $limit, $totalData) ?></span> dari <span class="text-gray-800"><?= $totalData ?></span> task
                    </div>
                    <div class="flex space-x-1">
                        <?php if ($page > 1): ?>
                            <button onclick="changePage(<?= $page - 1 ?>)" class="px-3 py-1 text-xs border rounded bg-white text-gray-600 hover:bg-gray-50 transition">Prev</button>
                        <?php endif; ?>
                        <?php 
                        $startRange = max(1, $page - 2);
                        $endRange = min($totalPages, $page + 2);
                        for ($i = $startRange; $i <= $endRange; $i++): 
                        ?>
                            <button onclick="changePage(<?= $i ?>)" class="px-3 py-1 text-xs border rounded transition <?= $i == $page ? 'bg-[#003674] text-white border-[#003674]' : 'bg-white text-gray-600 hover:bg-gray-50' ?>">
                                <?= $i ?>
                            </button>
                        <?php endfor; ?>
                        <?php if ($page < $totalPages): ?>
                            <button onclick="changePage(<?= $page + 1 ?>)" class="px-3 py-1 text-xs border rounded bg-white text-gray-600 hover:bg-gray-50 transition">Next</button>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
                
        </main>
    </div>
    

    <script>
    function updateFilter(key, val) {
        let url = new URL(window.location.href);
        url.searchParams.set(key, val);
        url.searchParams.set('page', '1'); 
        window.location.href = url.href;
    }
    function changePage(pageNum) {
        let url = new URL(window.location.href);
        url.searchParams.set('page', pageNum);
        window.location.href = url.href;
    }
    </script>

</body>
</html>