<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header("Location: index.php");
    exit;
}

require_once 'services/koneksi.php';

$resProduct = mysqli_query($conn, "SELECT COUNT(*) as total FROM team WHERE tim = 'PRODUCT'");
$countProduct = mysqli_fetch_assoc($resProduct)['total'];

$resEnginer = mysqli_query($conn, "SELECT COUNT(*) as total FROM team WHERE tim = 'ENGINER'");
$countEnginer = mysqli_fetch_assoc($resEnginer)['total'];

$resClient = mysqli_query($conn, "SELECT COUNT(*) as total FROM client");
$countClient = mysqli_fetch_assoc($resClient)['total'];



$resTaskFaskes = mysqli_query(
    $conn,
    "SELECT c.nama as faskes, 
COUNT(t.id) as jumlah 
FROM client c
LEFT JOIN task t ON c.nama = t.faskes AND t.task_url = '-' AND t.status_cek != 'Revisi'
GROUP BY c.nama 
ORDER BY jumlah DESC"
);

$resTaskProduct = mysqli_query(
    $conn,
    "SELECT tm.nama as product, COUNT(t.id) as jumlah 
FROM team tm
LEFT JOIN task t ON tm.nama = t.product AND t.task_url = '-' AND t.status_cek != 'Revisi'
WHERE tm.tim = 'PRODUCT'
GROUP BY tm.nama 
ORDER BY jumlah DESC"
);

$resTaskRevisi = mysqli_query(
    $conn,
    "SELECT c.nama as faskes, 
COUNT(t.id) as jumlah 
FROM client c
LEFT JOIN task t ON c.nama = t.faskes AND t.status_cek = 'Revisi'
GROUP BY c.nama 
ORDER BY jumlah DESC"
);

$resTaskOverdue = mysqli_query(
    $conn,
    "SELECT t.*, c.nama AS faskes_nama FROM task t
     LEFT JOIN client c ON t.faskes = c.nama
     WHERE t.status_cek != 'Selesai' 
     AND t.tgl_release IS NOT NULL 
     AND t.tgl_release > '2000-01-01' 
     AND t.tgl_release < CURDATE()
     ORDER BY t.tgl_release ASC" // Diurutkan dari yang paling lama lewat
);

$resTaskDueSoon = mysqli_query(
    $conn,
    "SELECT t.*, c.nama AS faskes_nama FROM task t
     LEFT JOIN client c ON t.faskes = c.nama
     WHERE t.status_cek != 'Selesai' 
     AND t.tgl_release IS NOT NULL 
     AND t.tgl_release > '2000-01-01' 
     AND t.tgl_release BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
     ORDER BY t.tgl_release ASC" // Diurutkan dari yang paling mepet (hari ini/besok)
);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitoring Task PO - Trustmedis</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>

<body class="bg-[#F0F2F5]">

    <div class="flex min-h-screen">
        <aside class="w-64 bg-[#003674] text-white flex flex-col shadow-xl">
            <div class="p-6 mb-4">
                <img src="assets/logo.png" alt="Logo" class="w-full">
                <hr class="mt-4 border-gray-500 opacity-30">
            </div>

            <nav class="flex-1 space-y-1">
                <a href="home.php" class="flex items-center px-6 py-3 bg-[#00D285] text-white">
                    <i class="fas fa-home mr-4 w-5 text-center"></i> Home
                </a>
                <a href="team.php" class="flex items-center px-6 py-3 hover:bg-[#002b55] transition text-gray-300">
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
            <div class="grid grid-cols-3 gap-8 mb-10">
                <div class="bg-[#10B981] p-6 rounded-lg shadow-sm text-white h-32 flex flex-col justify-center">
                    <h3 class="text-3xl font-bold"><?= $countProduct ?></h3>
                    <p class="text-sm opacity-90">Jumlah Team Product</p>
                </div>
                <div class="bg-[#FF007A] p-6 rounded-lg shadow-sm text-white h-32 flex flex-col justify-center">
                    <h3 class="text-3xl font-bold"><?= $countEnginer ?></h3>
                    <p class="text-sm opacity-90">Jumlah Team Enginer</p>
                </div>
                <div class="bg-[#00B4FF] p-6 rounded-lg shadow-sm text-white h-32 flex flex-col justify-center">
                    <h3 class="text-3xl font-bold"><?= $countClient ?></h3>
                    <p class="text-sm opacity-90">Jumlah Faskes</p>
                </div>
            </div>

            <div class="grid grid-cols-3 gap-10">
                <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                    <div class="bg-[#E9E9F2] px-6 py-4">
                        <h4 class="text-gray-600 font-bold text-sm tracking-wider uppercase">Open Task (Faskes)</h4>
                    </div>
                    <div class="p-6 space-y-5">
                        <?php
                        while ($row = mysqli_fetch_assoc($resTaskFaskes)):
                            if ($row['jumlah'] > 0):
                        ?>
                                <a href="task.php?faskes=<?= urlencode($row['faskes']) ?>&status_task=not" class="flex items-center space-x-4 p-2 -m-2 rounded-lg hover:bg-blue-50 transition-all duration-200 group cursor-pointer">
                                    <span class="bg-[#00B4FF] text-white w-8 h-8 flex items-center justify-center rounded-full text-sm font-bold group-hover:scale-110 transition-transform">
                                        <?= $row['jumlah'] ?>
                                    </span>
                                    <span class="text-gray-700 font-medium uppercase group-hover:text-[#003674] transition-colors"><?= $row['faskes'] ?></span>
                                    <i class="fas fa-chevron-right text-gray-300 group-hover:text-[#00B4FF] ml-auto text-xs transition-colors"></i>
                                </a>
                        <?php
                            endif;
                        endwhile;
                        ?>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                    <div class="bg-[#E9E9F2] px-6 py-4">
                        <h4 class="text-gray-600 font-bold text-sm tracking-wider uppercase">Open Task (Product)</h4>
                    </div>
                    <div class="p-6 space-y-5">
                        <?php
                        while ($row = mysqli_fetch_assoc($resTaskProduct)):
                            if ($row['jumlah'] > 0):
                        ?>
                                <a href="task.php?product=<?= urlencode($row['product']) ?>&status_task=not" class="flex items-center space-x-4 p-2 -m-2 rounded-lg hover:bg-blue-50 transition-all duration-200 group cursor-pointer">
                                    <span class="bg-[#00B4FF] text-white w-8 h-8 flex items-center justify-center rounded-full text-sm font-bold group-hover:scale-110 transition-transform">
                                        <?= $row['jumlah'] ?>
                                    </span>
                                    <span class="text-gray-700 font-medium uppercase group-hover:text-[#003674] transition-colors"><?= $row['product'] ?></span>
                                    <i class="fas fa-chevron-right text-gray-300 group-hover:text-[#00B4FF] ml-auto text-xs transition-colors"></i>
                                </a>
                        <?php
                            endif;
                        endwhile;
                        ?>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                    <div class="bg-[#E9E9F2] px-6 py-4">
                        <h4 class="text-gray-600 font-bold text-sm tracking-wider uppercase">Revisi Task</h4>
                    </div>
                    <div class="p-6 space-y-5">
                        <?php
                        while ($row = mysqli_fetch_assoc($resTaskRevisi)):
                            if ($row['jumlah'] > 0):
                        ?>
                                <a href="task.php?faskes=<?= urlencode($row['faskes']) ?>&status_cek=Revisi" class="flex items-center space-x-4 p-2 -m-2 rounded-lg hover:bg-orange-50 transition-all duration-200 group cursor-pointer">
                                    <span class="bg-[#D97706] text-white w-8 h-8 flex items-center justify-center rounded-full text-sm font-bold group-hover:scale-110 transition-transform">
                                        <?= $row['jumlah'] ?>
                                    </span>
                                    <span class="text-gray-700 font-medium uppercase group-hover:text-[#92400E] transition-colors"><?= $row['faskes'] ?></span>
                                    <i class="fas fa-chevron-right text-gray-300 group-hover:text-[#D97706] ml-auto text-xs transition-colors"></i>
                                </a>
                        <?php
                            endif;
                        endwhile;
                        ?>
                    </div>
                </div>
            </div>
            <!-- Section Baru: Overdue dan Due Soon -->
            <div class="grid grid-cols-2 gap-10 mt-10">

                <!-- 1. Task Overdue List -->
                <div class="bg-white rounded-xl shadow-sm overflow-hidden flex flex-col max-h-[500px]">
                    <div class="bg-rose-100 px-6 py-4 flex justify-between items-center sticky top-0">
                        <h4 class="text-rose-800 font-bold text-sm tracking-wider uppercase">
                            <i class="fas fa-exclamation-triangle mr-2"></i> Task Overdue
                        </h4>
                        <span class="bg-rose-600 text-white text-xs font-bold px-2 py-1 rounded-full">
                            <?= mysqli_num_rows($resTaskOverdue) ?> Task
                        </span>
                    </div>
                    <div class="p-0 overflow-y-auto flex-1">
                        <?php if (mysqli_num_rows($resTaskOverdue) > 0): ?>
                            <ul class="divide-y divide-gray-100">
                                <?php while ($row = mysqli_fetch_assoc($resTaskOverdue)): ?>
                                    <li class="p-4 hover:bg-rose-50 transition duration-150">
                                        <div class="flex justify-between items-start mb-1">
                                            <a href="edit_task.php?id=<?= $row['id'] ?>" class="text-sm font-bold text-gray-800 hover:text-rose-600 transition">
                                                <?= htmlspecialchars($row['fitur'] ?? '-') ?>
                                            </a>
                                            <span class="text-[10px] font-bold px-2 py-1 rounded bg-rose-100 text-rose-700 whitespace-nowrap ml-2">
                                                <?= date('d M Y', strtotime($row['tgl_release'])) ?>
                                            </span>
                                        </div>
                                        <div class="flex items-center text-xs text-gray-500 mt-2 space-x-4">
                                            <span title="Faskes"><i class="fas fa-hospital text-gray-400 mr-1"></i> <?= htmlspecialchars($row['faskes_nama'] ?? $row['faskes']) ?></span>
                                            <span title="Enginer"><i class="fas fa-user-cog text-gray-400 mr-1"></i> <?= htmlspecialchars($row['enginer'] ?? '-') ?></span>
                                        </div>
                                    </li>
                                <?php endwhile; ?>
                            </ul>
                        <?php else: ?>
                            <div class="p-10 text-center text-gray-400 flex flex-col items-center justify-center">
                                <i class="fas fa-check-circle text-4xl mb-3 text-emerald-300"></i>
                                <p class="text-sm">Hebat! Tidak ada task yang overdue.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- 2. Task Due Soon List -->
                <div class="bg-white rounded-xl shadow-sm overflow-hidden flex flex-col max-h-[500px]">
                    <div class="bg-amber-100 px-6 py-4 flex justify-between items-center sticky top-0">
                        <h4 class="text-amber-800 font-bold text-sm tracking-wider uppercase">
                            <i class="fas fa-hourglass-half mr-2"></i> Task Due Soon (H-7)
                        </h4>
                        <span class="bg-amber-500 text-white text-xs font-bold px-2 py-1 rounded-full">
                            <?= mysqli_num_rows($resTaskDueSoon) ?> Task
                        </span>
                    </div>
                    <div class="p-0 overflow-y-auto flex-1">
                        <?php if (mysqli_num_rows($resTaskDueSoon) > 0): ?>
                            <ul class="divide-y divide-gray-100">
                                <?php while ($row = mysqli_fetch_assoc($resTaskDueSoon)): ?>
                                    <li class="p-4 hover:bg-amber-50 transition duration-150">
                                        <div class="flex justify-between items-start mb-1">
                                            <a href="edit_task.php?id=<?= $row['id'] ?>" class="text-sm font-bold text-gray-800 hover:text-amber-600 transition">
                                                <?= htmlspecialchars($row['fitur'] ?? '-') ?>
                                            </a>
                                            <span class="text-[10px] font-bold px-2 py-1 rounded bg-amber-100 text-amber-700 whitespace-nowrap ml-2">
                                                <?= date('d M Y', strtotime($row['tgl_release'])) ?>
                                            </span>
                                        </div>
                                        <div class="flex items-center text-xs text-gray-500 mt-2 space-x-4">
                                            <span title="Faskes"><i class="fas fa-hospital text-gray-400 mr-1"></i> <?= htmlspecialchars($row['faskes_nama'] ?? $row['faskes']) ?></span>
                                            <span title="Enginer"><i class="fas fa-user-cog text-gray-400 mr-1"></i> <?= htmlspecialchars($row['enginer'] ?? '-') ?></span>
                                        </div>
                                    </li>
                                <?php endwhile; ?>
                            </ul>
                        <?php else: ?>
                            <div class="p-10 text-center text-gray-400 flex flex-col items-center justify-center">
                                <i class="fas fa-calendar-check text-4xl mb-3 text-gray-300"></i>
                                <p class="text-sm">Belum ada task yang mendekati deadline.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>

</body>

</html>