<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header("Location: index.php");
    exit;
}

require_once 'services/koneksi.php';

// 1. Ambil parameter filter
$filterProduct = isset($_GET['product']) ? mysqli_real_escape_string($conn, $_GET['product']) : 'all';
$filterFaskes = isset($_GET['faskes']) ? mysqli_real_escape_string($conn, $_GET['faskes']) : 'all';
$filterEnginer = isset($_GET['enginer']) ? mysqli_real_escape_string($conn, $_GET['enginer']) : 'all';
$filterJenis = isset($_GET['jenis']) ? mysqli_real_escape_string($conn, $_GET['jenis']) : 'all';
$filterStatus = isset($_GET['status_task']) ? mysqli_real_escape_string($conn, $_GET['status_task']) : 'all';
$filterStart = isset($_GET['start_date']) ? mysqli_real_escape_string($conn, $_GET['start_date']) : '';
$filterEnd = isset($_GET['end_date']) ? mysqli_real_escape_string($conn, $_GET['end_date']) : '';

// 2. Logika Paginasi
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

// 3. Membangun Kondisi Filter
$conditions = [];
if ($filterProduct != 'all') $conditions[] = "product = '$filterProduct'";
if ($filterFaskes != 'all') $conditions[] = "faskes = '$filterFaskes'";
if ($filterEnginer != 'all') $conditions[] = "enginer = '$filterEnginer'";
if ($filterJenis != 'all') $conditions[] = "jenis = '$filterJenis'";
if ($filterStatus == 'completed') $conditions[] = "task_url != '-'";
if ($filterStatus == 'not') $conditions[] = "task_url = '-'";
if (!empty($filterStart) && !empty($filterEnd)) {
    $conditions[] = "tgl_release BETWEEN '$filterStart' AND '$filterEnd'";
}

$whereClause = "";
if (count($conditions) > 0) {
    $whereClause = " WHERE " . implode(" AND ", $conditions);
}

// 4. Hitung Total Data untuk Paginasi
$countQuery = "SELECT COUNT(*) as total FROM task" . $whereClause;
$countResult = mysqli_query($conn, $countQuery);
$totalData = mysqli_fetch_assoc($countResult)['total'];
$totalPages = ceil($totalData / $limit);

// 5. Query Utama
$query = "SELECT * FROM task" . $whereClause . " ORDER BY id DESC LIMIT $limit OFFSET $offset";
$resTask = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Daftar Task - Trustmedis</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

        body {
            font-family: 'Inter', sans-serif;
        }

        /* Radio Button Styling */
        .cek-radio-group {
            display: inline-flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 4px;
        }

        .cek-radio-group label {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            cursor: pointer;
            font-size: 11px;
            font-weight: 600;
            white-space: nowrap;
        }

        .cek-radio-group label.revisi {
            color: #D97706;
        }

        .cek-radio-group label.selesai {
            color: #059669;
        }

        .cek-radio-group input[type="radio"] {
            width: 13px;
            height: 13px;
            margin: 0;
            cursor: pointer;
            accent-color: currentColor;
        }

        /* Status Badge Styling */
        .badge-status {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            padding: 4px 12px;
            border-radius: 9999px;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            white-space: nowrap;
            min-width: 90px;
        }

        .badge-selesai {
            background: #D1FAE5;
            color: #065F46;
        }

        .badge-revisi {
            background: #FEF3C7;
            color: #92400E;
        }

        .badge-belum {
            background: #F3F4F6;
            color: #9CA3AF;
        }
    </style>
</head>

<body class="bg-[#F0F2F5] flex">
    <aside class="w-64 min-h-screen bg-[#003674] text-white flex flex-col shadow-xl shrink-0">
        <div class="p-6 mb-4">
            <img src="assets/logo.png" alt="Logo" class="w-full">
            <hr class="mt-4 border-gray-500 opacity-30">
        </div>
        <nav class="flex-1 space-y-1">
            <a href="home.php" class="flex items-center px-6 py-3 hover:bg-[#002b55] transition text-gray-300"><i class="fas fa-home mr-4 w-5 text-center"></i> Home</a>
            <a href="team.php" class="flex items-center px-6 py-3 hover:bg-[#002b55] transition text-gray-300"><i class="fas fa-user-friends mr-4 w-5 text-center"></i> Team</a>
            <a href="client.php" class="flex items-center px-6 py-3 hover:bg-[#002b55] transition text-gray-300"><i class="fas fa-hospital mr-4 w-5 text-center"></i> Client</a>
            <a href="task.php" class="flex items-center px-6 py-3 bg-[#00D285] text-white font-semibold"><i class="fas fa-clipboard-list mr-4 w-5 text-center"></i> Task</a>
        </nav>
    </aside>

    <main class="flex-1 p-10">
        <div class="flex justify-between items-end mb-8">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Monitoring Task</h2>
            </div>
            <a href="tambah_task.php" class="bg-[#003674] text-white px-5 py-2.5 rounded-lg hover:bg-[#002b55] flex items-center shadow-md transition"><i class="fas fa-plus mr-2 text-sm"></i> Tambah Task</a>
        </div>

        <?php if (isset($_GET['status'])): ?>
            <?php if ($_GET['status'] == 'added'): ?>
                <div class="mb-6 p-4 rounded-lg bg-green-50 border border-green-200 text-green-700 flex items-center text-sm">
                    <i class="fas fa-check-circle mr-3 text-lg"></i>
                    Data task berhasil ditambahkan!
                </div>
            <?php elseif ($_GET['status'] == 'imported'): ?>
                <div class="mb-6 p-4 rounded-lg bg-green-50 border border-green-200 text-green-700 flex items-center text-sm">
                    <i class="fas fa-file-import mr-3 text-lg"></i>
                    <?= isset($_GET['msg']) ? htmlspecialchars($_GET['msg']) : 'Data berhasil diimport!' ?>
                </div>
            <?php elseif ($_GET['status'] == 'error'): ?>
                <div class="mb-6 p-4 rounded-lg bg-red-50 border border-red-200 text-red-700 flex items-center text-sm">
                    <i class="fas fa-exclamation-circle mr-3 text-lg"></i>
                    <?= isset($_GET['msg']) ? htmlspecialchars($_GET['msg']) : 'Terjadi kesalahan!' ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <!-- Filter Section -->
        <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden mb-6">
            <!-- Header Filter -->
            <div class="bg-gradient-to-r from-[#003674] to-[#00D285] px-6 py-4 flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="bg-white bg-opacity-20 rounded-lg p-2">
                        <i class="fas fa-filter text-white text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-white font-bold text-lg">Filter Data Task</h3>
                        <p class="text-white text-xs opacity-90">Gunakan filter untuk mempersempit pencarian</p>
                    </div>
                </div>
                <a href="task.php" class="bg-white bg-opacity-20 hover:bg-opacity-30 text-white px-4 py-2 rounded-lg text-sm font-semibold transition flex items-center space-x-2">
                    <i class="fas fa-redo text-xs"></i>
                    <span>Reset</span>
                </a>
            </div>

            <!-- Filter Form -->
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                    <!-- Filter Product -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-2 flex items-center">
                            <i class="fas fa-user-tie text-[#003674] mr-2 text-sm"></i>
                            Product
                        </label>
                        <select onchange="updateFilter('product', this.value)" class="w-full px-4 py-2.5 border-2 border-gray-300 rounded-lg text-sm bg-white focus:border-[#00D285] focus:ring-2 focus:ring-[#00D285] focus:ring-opacity-20 outline-none transition">
                            <option value="all">Semua Product</option>
                            <?php $pRes = mysqli_query($conn, "SELECT nama FROM team WHERE tim='PRODUCT' ORDER BY nama");
                            while ($p = mysqli_fetch_assoc($pRes))
                                echo "<option value='{$p['nama']}' " . ($filterProduct == $p['nama'] ? 'selected' : '') . ">{$p['nama']}</option>"; ?>
                        </select>
                    </div>

                    <!-- Filter Faskes -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-2 flex items-center">
                            <i class="fas fa-hospital text-[#003674] mr-2 text-sm"></i>
                            Client / Faskes
                        </label>
                        <select onchange="updateFilter('faskes', this.value)" class="w-full px-4 py-2.5 border-2 border-gray-300 rounded-lg text-sm bg-white focus:border-[#00D285] focus:ring-2 focus:ring-[#00D285] focus:ring-opacity-20 outline-none transition">
                            <option value="all">Semua Faskes</option>
                            <?php $fRes = mysqli_query($conn, "SELECT nama FROM client ORDER BY nama");
                            while ($f = mysqli_fetch_assoc($fRes))
                                echo "<option value='{$f['nama']}' " . ($filterFaskes == $f['nama'] ? 'selected' : '') . ">{$f['nama']}</option>"; ?>
                        </select>
                    </div>

                    <!-- Filter Enginer -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-2 flex items-center">
                            <i class="fas fa-code text-[#003674] mr-2 text-sm"></i>
                            Enginer
                        </label>
                        <select onchange="updateFilter('enginer', this.value)" class="w-full px-4 py-2.5 border-2 border-gray-300 rounded-lg text-sm bg-white focus:border-[#00D285] focus:ring-2 focus:ring-[#00D285] focus:ring-opacity-20 outline-none transition">
                            <option value="all">Semua Enginer</option>
                            <?php $eRes = mysqli_query($conn, "SELECT nama FROM team WHERE tim='ENGINER' ORDER BY nama");
                            while ($e = mysqli_fetch_assoc($eRes))
                                echo "<option value='{$e['nama']}' " . ($filterEnginer == $e['nama'] ? 'selected' : '') . ">{$e['nama']}</option>"; ?>
                        </select>
                    </div>

                    <!-- Filter Jenis Task -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-2 flex items-center">
                            <i class="fas fa-tags text-[#003674] mr-2 text-sm"></i>
                            Jenis Task
                        </label>
                        <select onchange="updateFilter('jenis', this.value)" class="w-full px-4 py-2.5 border-2 border-gray-300 rounded-lg text-sm bg-white focus:border-[#00D285] focus:ring-2 focus:ring-[#00D285] focus:ring-opacity-20 outline-none transition">
                            <option value="all">Semua Jenis</option>
                            <option value="Fitur Berbayar" <?= $filterJenis == 'Fitur Berbayar' ? 'selected' : '' ?>>Fitur Berbayar</option>
                            <option value="Regulasi" <?= $filterJenis == 'Regulasi' ? 'selected' : '' ?>>Regulasi</option>
                            <option value="Saran Fitur" <?= $filterJenis == 'Saran Fitur' ? 'selected' : '' ?>>Saran Fitur</option>
                            <option value="Prioritas" <?= $filterJenis == 'Prioritas' ? 'selected' : '' ?>>Prioritas</option>
                        </select>
                    </div>

                    <!-- Filter Status Link -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-2 flex items-center">
                            <i class="fas fa-link text-[#003674] mr-2 text-sm"></i>
                            Status Link
                        </label>
                        <select onchange="updateFilter('status_task', this.value)" class="w-full px-4 py-2.5 border-2 border-gray-300 rounded-lg text-sm bg-white focus:border-[#00D285] focus:ring-2 focus:ring-[#00D285] focus:ring-opacity-20 outline-none transition">
                            <option value="all" <?= $filterStatus == 'all' ? 'selected' : '' ?>>Semua Status</option>
                            <option value="completed" <?= $filterStatus == 'completed' ? 'selected' : '' ?>>Ada Link</option>
                            <option value="not" <?= $filterStatus == 'not' ? 'selected' : '' ?>>Belum Ada Link</option>
                        </select>
                    </div>

                    <!-- Filter Tanggal Dari -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-2 flex items-center">
                            <i class="fas fa-calendar-alt text-[#003674] mr-2 text-sm"></i>
                            Tanggal Dari
                        </label>
                        <input type="date" value="<?= $filterStart ?>" onchange="updateFilter('start_date', this.value)"
                            class="w-full px-4 py-2.5 border-2 border-gray-300 rounded-lg text-sm bg-white focus:border-[#00D285] focus:ring-2 focus:ring-[#00D285] focus:ring-opacity-20 outline-none transition">
                    </div>

                    <!-- Filter Tanggal Sampai -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-2 flex items-center">
                            <i class="fas fa-calendar-check text-[#003674] mr-2 text-sm"></i>
                            Tanggal Sampai
                        </label>
                        <input type="date" value="<?= $filterEnd ?>" onchange="updateFilter('end_date', this.value)"
                            class="w-full px-4 py-2.5 border-2 border-gray-300 rounded-lg text-sm bg-white focus:border-[#00D285] focus:ring-2 focus:ring-[#00D285] focus:ring-opacity-20 outline-none transition">
                    </div>
                </div>

                <!-- Active Filters Badge -->
                <?php
                $activeFilters = 0;
                if ($filterProduct != 'all') $activeFilters++;
                if ($filterFaskes != 'all') $activeFilters++;
                if ($filterEnginer != 'all') $activeFilters++;
                if ($filterJenis != 'all') $activeFilters++;
                if ($filterStatus != 'all') $activeFilters++;
                if (!empty($filterStart) && !empty($filterEnd)) $activeFilters++;

                if ($activeFilters > 0): ?>
                    <div class="mt-4 pt-4 border-t border-gray-200">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">
                                <i class="fas fa-check-circle text-[#00D285] mr-2"></i>
                                <strong><?= $activeFilters ?></strong> filter aktif |
                                Menampilkan <strong><?= $totalData ?></strong> dari total data
                            </span>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <table class="w-full border-collapse">
                <thead class="bg-[#E9E9F2] border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-4 text-left text-[10px] font-bold text-gray-600 uppercase tracking-widest">Product</th>
                        <th class="px-4 py-4 text-left text-[10px] font-bold text-gray-600 uppercase tracking-widest">Faskes</th>
                        <th class="px-4 py-4 text-left text-[10px] font-bold text-gray-600 uppercase tracking-widest">Fitur</th>
                        <th class="px-4 py-4 text-left text-[10px] font-bold text-gray-600 uppercase tracking-widest">Task (URL)</th>
                        <th class="px-4 py-4 text-left text-[10px] font-bold text-gray-600 uppercase tracking-widest">Jenis / Keterangan</th>
                        <th class="px-4 py-4 text-left text-[10px] font-bold text-gray-600 uppercase tracking-widest">Enginer</th>
                        <th class="px-4 py-4 text-center text-[10px] font-bold text-gray-600 uppercase tracking-widest whitespace-nowrap">Tanggal Release</th>
                        <th class="px-4 py-4 text-center text-[10px] font-bold text-gray-600 uppercase tracking-widest">Cek</th>
                        <th class="px-4 py-4 text-center text-[10px] font-bold text-gray-600 uppercase tracking-widest">Status</th>
                        <th class="px-4 py-4 text-center text-[10px] font-bold text-gray-600 uppercase tracking-widest">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php if (mysqli_num_rows($resTask) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($resTask)): ?>
                            <tr class="<?= $row['status_cek'] == 'Selesai' ? 'bg-green-100/50 hover:bg-green-200/50' : ($row['status_cek'] == 'Revisi' ? 'bg-orange-100/50 hover:bg-orange-200/50' : 'hover:bg-gray-50') ?> transition duration-150">
                                <td class="px-4 py-4 text-xs font-bold text-gray-800"><?= htmlspecialchars($row['product'] ?? '-') ?></td>
                                <td class="px-4 py-4 text-xs font-semibold text-gray-900"><?= htmlspecialchars($row['faskes'] ?? '-') ?></td>
                                <td class="px-4 py-4 text-xs text-gray-700 leading-relaxed"><?= htmlspecialchars($row['fitur'] ?? '-') ?></td>
                                <td class="px-4 py-4 text-xs">
                                    <?php if ($row['task_url'] != '-'): ?>
                                        <a href="<?= htmlspecialchars($row['task_url']) ?>" target="_blank" class="text-blue-600 hover:text-blue-800 font-medium underline flex items-center">
                                            <i class="fas fa-external-link-alt mr-1 text-[10px]"></i>Link
                                        </a>
                                    <?php else: ?>
                                        <span class="text-gray-400 font-medium">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-4">
                                    <div class="text-[10px] text-purple-700 font-bold uppercase mb-1"><?= htmlspecialchars($row['jenis'] ?? '-') ?></div>
                                    <div class="text-[10px] text-gray-500 italic"><?= htmlspecialchars($row['keterangan'] ?? '-') ?></div>
                                </td>
                                <td class="px-4 py-4 text-xs font-semibold text-gray-600"><?= htmlspecialchars($row['enginer'] ?? '-') ?></td>
                                <td class="px-4 py-4 text-center text-xs font-medium text-gray-700">
                                    <?= ($row['tgl_release'] && $row['tgl_release'] != '0000-00-00') ? date('d-m-Y', strtotime($row['tgl_release'])) : '<span class="text-gray-400 italic">-</span>' ?>
                                </td>
                                <td class="px-4 py-4 text-center align-middle">
                                    <?php if ($row['task_url'] != '-'): ?>
                                        <div class="cek-radio-group">
                                            <label class="revisi">
                                                <input type="radio" name="status_cek_<?= $row['id'] ?>" value="Revisi" <?= $row['status_cek'] == 'Revisi' ? 'checked' : '' ?> data-prev="<?= $row['status_cek'] ?>" onclick="toggleCek(this, <?= $row['id'] ?>, 'Revisi')">
                                                Revisi
                                            </label>
                                            <label class="selesai">
                                                <input type="radio" name="status_cek_<?= $row['id'] ?>" value="Selesai" <?= $row['status_cek'] == 'Selesai' ? 'checked' : '' ?> data-prev="<?= $row['status_cek'] ?>" onclick="toggleCek(this, <?= $row['id'] ?>, 'Selesai')">
                                                Selesai
                                            </label>
                                        </div>
                                    <?php else: ?>
                                        <i class="fas fa-lock text-gray-300 text-xs" title="Link belum tersedia"></i>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-4 text-center align-middle" id="badge-status-<?= $row['id'] ?>">
                                    <?php if ($row['status_cek'] == 'Selesai'): ?>
                                        <span class="badge-status badge-selesai"><i class="fas fa-check-circle"></i> Selesai</span>
                                    <?php elseif ($row['status_cek'] == 'Revisi'): ?>
                                        <span class="badge-status badge-revisi"><i class="fas fa-sync-alt"></i> Revisi</span>
                                    <?php else: ?>
                                        <span class="badge-status badge-belum"><i class="fas fa-clock"></i> Belum di cek</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-4 text-center">
                                    <div class="flex justify-center items-center space-x-3">
                                        <a href="edit_task.php?id=<?= $row['id'] ?>" class="text-blue-500 hover:text-blue-700 transition" title="Edit Task"><i class="fas fa-edit"></i></a>
                                        <a href="delete_task.php?id=<?= $row['id'] ?>" class="text-red-400 hover:text-red-600 transition" title="Hapus Task" onclick="return confirm('Hapus task ini?')"><i class="fas fa-trash"></i></a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="10" class="p-10 text-center text-gray-400 italic text-sm">Data tidak ditemukan</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
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

        function toggleCek(radio, id, value) {
            let radios = document.querySelectorAll('input[name="status_cek_' + id + '"]');
            let prevStatus = radio.dataset.prev;
            let newStatus;

            // Jika klik radio yang sudah terpilih → batalkan (kembali ke Belum di cek)
            if (prevStatus === value) {
                radio.checked = false;
                newStatus = 'Belum di cek';
            } else {
                newStatus = value;
            }

            // Update data-prev di semua radio untuk task ini
            radios.forEach(r => r.dataset.prev = newStatus);

            // Kirim ke server
            fetch(`services/update_status.php?id=${id}&status=${encodeURIComponent(newStatus)}`)
                .then(res => {
                    if (!res.ok) {
                        alert('Gagal update status!');
                        return;
                    }

                    // Update badge
                    let badgeEl = document.getElementById('badge-status-' + id);
                    if (newStatus === 'Selesai') {
                        badgeEl.innerHTML = '<span class="badge-status badge-selesai"><i class="fas fa-check-circle"></i> Selesai</span>';
                    } else if (newStatus === 'Revisi') {
                        badgeEl.innerHTML = '<span class="badge-status badge-revisi"><i class="fas fa-sync-alt"></i> Revisi</span>';
                    } else {
                        badgeEl.innerHTML = '<span class="badge-status badge-belum"><i class="fas fa-clock"></i> Belum di cek</span>';
                    }

                    // Update row highlight
                    let row = badgeEl.closest('tr');
                    if (newStatus === 'Selesai') {
                        row.className = 'bg-green-100/50 hover:bg-green-200/50 transition duration-150';
                    } else if (newStatus === 'Revisi') {
                        row.className = 'bg-orange-100/50 hover:bg-orange-200/50 transition duration-150';
                    } else {
                        row.className = 'hover:bg-gray-50 transition duration-150';
                    }
                });
        }
    </script>
</body>

</html>