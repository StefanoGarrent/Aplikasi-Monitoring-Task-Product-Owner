<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header("Location: index.php");
    exit;
}

require_once 'services/koneksi.php'; 

if (!isset($_GET['id'])) {
    header("Location: task.php");
    exit;
}

$id = mysqli_real_escape_string($conn, $_GET['id']);
$query = mysqli_query($conn, "SELECT * FROM task WHERE id = '$id'");
$data = mysqli_fetch_assoc($query);

if (!$data) {
    header("Location: task.php");
    exit;
}

$resProduct = mysqli_query($conn, "SELECT nama FROM team WHERE tim = 'PRODUCT'");
$resEnginer = mysqli_query($conn, "SELECT nama FROM team WHERE tim = 'ENGINER'");
$resFaskes = mysqli_query($conn, "SELECT nama FROM client");

$message = "";
if (isset($_POST['update'])) {
    $product   = mysqli_real_escape_string($conn, $_POST['product']);
    $faskes    = mysqli_real_escape_string($conn, $_POST['faskes']);
    $jenis     = mysqli_real_escape_string($conn, $_POST['jenis']); // Baru
    $fitur     = mysqli_real_escape_string($conn, $_POST['fitur']);
    $keterangan = mysqli_real_escape_string($conn, $_POST['keterangan']); // Baru
    $task_url  = mysqli_real_escape_string($conn, $_POST['task_url']);
    $enginer   = mysqli_real_escape_string($conn, $_POST['enginer']);
    $tgl_release = mysqli_real_escape_string($conn, $_POST['tgl_release']);

    $update = mysqli_query($conn, "UPDATE task SET 
                product = '$product',
                faskes = '$faskes',
                jenis = '$jenis',
                fitur = '$fitur', 
                keterangan = '$keterangan',
                task_url = '$task_url',
                enginer = '$enginer',
                tgl_release = '$tgl_release'
                WHERE id = '$id'");

    if ($update) {
        header("Location: task.php?status=success");
        exit;
    } else {
        $message = "Gagal memperbarui data: " . mysqli_error($conn);
    }
}
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
                <a href="client.php" class="flex items-center px-6 py-3 hover:bg-[#002b55] transition text-gray-300">
                    <i class="fas fa-hospital mr-4 w-5 text-center"></i> Client
                </a>
                <a href="task.php" class="flex items-center px-6 py-3 bg-[#00D285] text-white">
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
            <div class="mb-8 flex items-center">
                <a href="task.php" class="mr-4 text-gray-400 hover:text-[#003674] transition">
                    <i class="fas fa-arrow-left text-xl"></i>
                </a>
                <div>
                    <h2 class="text-2xl font-bold text-gray-800">Edit Task</h2>
                    <p class="text-gray-500">Perbarui informasi task</p>
                </div>
            </div>

            <div class="max-w-2xl bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
                <form action="" method="POST" class="p-8 space-y-6">
                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Pilih Tim Product</label>
                            <select name="product" required class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#00D285] outline-none transition cursor-pointer">
                                <?php while($row = mysqli_fetch_assoc($resProduct)): ?>
                                    <option value="<?= htmlspecialchars($row['nama']) ?>" <?= $data['product'] == $row['nama'] ? 'selected' : '' ?>><?= htmlspecialchars($row['nama']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Pilih Tim Enginer</label>
                            <select name="enginer" required class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#00D285] outline-none transition cursor-pointer">
                                <?php while($row = mysqli_fetch_assoc($resEnginer)): ?>
                                    <option value="<?= htmlspecialchars($row['nama']) ?>" <?= $data['enginer'] == $row['nama'] ? 'selected' : '' ?>><?= htmlspecialchars($row['nama']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Pilih Client Faskes</label>
                            <select name="faskes" required class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#00D285] outline-none transition cursor-pointer">
                                <?php while($row = mysqli_fetch_assoc($resFaskes)): ?>
                                    <option value="<?= htmlspecialchars($row['nama']) ?>" <?= $data['faskes'] == $row['nama'] ? 'selected' : '' ?>><?= htmlspecialchars($row['nama']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Jenis Task</label>
                            <select name="jenis" required class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#00D285] outline-none transition cursor-pointer">
                                <option value="" <?= $data['jenis'] == '' ? 'selected' : '' ?>>--Pilih Jenis Task--</option>
                                <option value="Fitur Berbayar" <?= $data['jenis'] == 'Fitur Berbayar' ? 'selected' : '' ?>>Fitur Berbayar</option>
                                <option value="Regulasi" <?= $data['jenis'] == 'Regulasi' ? 'selected' : '' ?>>Regulasi</option>
                                <option value="Saran Fitur" <?= $data['jenis'] == 'Saran Fitur' ? 'selected' : '' ?>>Saran Fitur</option>
                                <option value="Prioritas" <?= $data['jenis'] == 'Prioritas' ? 'selected' : '' ?>>Prioritas</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Tanggal Release</label>
                            <input type="date" name="tgl_release" value="<?= htmlspecialchars($data['tgl_release']) ?>" required class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#00D285] outline-none transition">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Request Fitur</label>
                            <input type="text" name="fitur" value="<?= htmlspecialchars($data['fitur']) ?>" required class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#00D285] outline-none transition">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Keterangan</label>
                        <textarea name="keterangan" rows="2" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#00D285] outline-none transition"><?= htmlspecialchars($data['keterangan'] ?? '-') ?></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Task URL</label>
                        <input type="text" name="task_url" value="<?= htmlspecialchars($data['task_url']) ?>" required class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#00D285] outline-none transition">
                    </div>

                    <div class="pt-4 flex items-center justify-end space-x-4 border-t border-gray-100">
                        <a href="task.php" class="text-gray-500 hover:text-gray-700 font-medium text-sm">Kembali</a>
                        <button type="submit" name="update" class="bg-[#00D285] text-white px-8 py-3 rounded-lg font-bold hover:bg-[#00b572] shadow-lg transition duration-200">
                            Update Data
                        </button>
                    </div>
                </form>
            </div>
        </main>
    </div>

</body>
</html>