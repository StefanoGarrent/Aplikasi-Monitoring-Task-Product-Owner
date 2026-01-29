<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header("Location: index.php");
    exit;
}

require_once 'services/koneksi.php';

if (!isset($_GET['id'])) {
    header("Location: team.php");
    exit;
}

$id = mysqli_real_escape_string($conn, $_GET['id']);
$query = mysqli_query($conn, "SELECT * FROM team WHERE id = '$id'");
$data = mysqli_fetch_assoc($query);

if (!$data) {
    header("Location: team.php");
    exit;
}

$message = "";
if (isset($_POST['update'])) {
    $nama   = mysqli_real_escape_string($conn, $_POST['nama']);
    $alamat = mysqli_real_escape_string($conn, $_POST['alamat']);
    $no_hp  = mysqli_real_escape_string($conn, $_POST['no_hp']);

    $update = mysqli_query($conn, "UPDATE team SET 
                nama = '$nama', 
                alamat = '$alamat', 
                no_hp = '$no_hp'
                WHERE id = '$id'");

    if ($update) {
        header("Location: team.php?status=success");
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
    <title>Edit Team - Trustmedis</title>
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
            <div class="mb-8 flex items-center">
                <a href="team.php" class="mr-4 text-gray-400 hover:text-[#003674] transition">
                    <i class="fas fa-arrow-left text-xl"></i>
                </a>
                <div>
                    <h2 class="text-2xl font-bold text-gray-800">Edit Anggota Tim</h2>
                    <p class="text-gray-500">Sesuaikan informasi dasar anggota</p>
                </div>
            </div>

            <div class="max-w-2xl bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
                <form action="" method="POST" class="p-8 space-y-6">
                    <input type="hidden" name="id" value="<?= $data['id'] ?>">

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Nama Lengkap</label>
                        <input type="text" name="nama" value="<?= htmlspecialchars($data['nama']) ?>" required
                            class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#00D285] outline-none transition">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Alamat</label>
                        <textarea name="alamat" rows="3" required
                            class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#00D285] outline-none transition"><?= htmlspecialchars($data['alamat']) ?></textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Nomor HP</label>
                            <input type="text" name="no_hp" value="<?= htmlspecialchars($data['no_hp']) ?>" required
                                class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#00D285] outline-none transition">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2 text-gray-400">Tim</label>
                            <input type="text" value="<?= $data['tim'] ?>" disabled
                                class="w-full px-4 py-3 rounded-lg border border-gray-200 bg-gray-100 text-gray-500 cursor-not-allowed">
                        </div>
                    </div>

                    <div class="pt-4 flex items-center justify-end space-x-4">
                        <a href="team.php" class="text-gray-500 hover:text-gray-700 font-medium text-sm">Kembali</a>
                        <button type="submit" name="update"
                            class="bg-[#003674] text-white px-8 py-3 rounded-lg font-bold hover:bg-[#002b55] shadow-lg transition duration-200">
                            Update Data
                        </button>
                    </div>
                </form>
            </div>
        </main>
    </div>

</body>
</html>