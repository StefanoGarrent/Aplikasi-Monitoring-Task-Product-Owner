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

$resTaskFaskes = mysqli_query($conn, 
"SELECT c.nama as faskes, 
COUNT(t.id) as jumlah 
FROM client c
LEFT JOIN task t ON c.nama = t.faskes AND t.task_url = '-'
GROUP BY c.nama 
ORDER BY jumlah DESC"
);

$resTaskProduct = mysqli_query($conn, 
"SELECT tm.nama as product, COUNT(t.id) as jumlah 
FROM team tm
LEFT JOIN task t ON tm.nama = t.product AND t.task_url = '-'
WHERE tm.tim = 'PRODUCT'
GROUP BY tm.nama 
ORDER BY jumlah DESC"
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
        body { font-family: 'Inter', sans-serif; }
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

            <div class="grid grid-cols-2 gap-10">
                <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                    <div class="bg-[#E9E9F2] px-6 py-4">
                        <h4 class="text-gray-600 font-bold text-sm tracking-wider uppercase">Open Task (Faskes)</h4>
                    </div>
                    <div class="p-6 space-y-5">
                        <?php 
                            while($row = mysqli_fetch_assoc($resTaskFaskes)):
                            if($row['jumlah'] > 0): 
                        ?>
                        <div class="flex items-center space-x-4">
                            <span class="bg-[#00B4FF] text-white w-8 h-8 flex items-center justify-center rounded-full text-sm font-bold">
                                <?= $row['jumlah'] ?>
                            </span>
                            <span class="text-gray-700 font-medium uppercase"><?= $row['faskes'] ?></span>
                        </div>
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
                            while($row = mysqli_fetch_assoc($resTaskProduct)):
                            if($row['jumlah'] > 0): 
                        ?>
                        <div class="flex items-center space-x-4">
                            <span class="bg-[#00B4FF] text-white w-8 h-8 flex items-center justify-center rounded-full text-sm font-bold">
                                <?= $row['jumlah'] ?>
                            </span>
                            <span class="text-gray-700 font-medium uppercase"><?= $row['product'] ?></span>
                        </div>
                        <?php 
                            endif;
                            endwhile; 
                        ?>
                    </div>
                </div>
            </div>
        </main>
    </div>

</body>
</html>