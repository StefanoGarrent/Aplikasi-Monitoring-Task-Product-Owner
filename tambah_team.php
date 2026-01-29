<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header("Location: index.php");
    exit;
}

require_once 'services/koneksi.php';

$message = "";
$status = "";

if (isset($_POST['submit'])) {
    $nama   = mysqli_real_escape_string($conn, $_POST['nama']);
    $alamat = mysqli_real_escape_string($conn, $_POST['alamat']);
    $no_hp  = mysqli_real_escape_string($conn, $_POST['no_hp']);
    $tim    = mysqli_real_escape_string($conn, $_POST['tim']);

    $check = mysqli_query($conn, "SELECT nama FROM team WHERE nama = '$nama'");
    
    if (mysqli_num_rows($check) > 0) {
        $message = "Nama anggota sudah terdaftar! Gunakan nama lain atau tambahkan inisial.";
        $status = "error";
    } else {
        $insert = mysqli_query($conn, "INSERT INTO team (nama, alamat, no_hp, tim) 
                                       VALUES ('$nama', '$alamat', '$no_hp', '$tim')");
        
        if ($insert) {
            header("Location: team.php?status=added");
            exit;
        } else {
            $message = "Gagal menambah data: " . mysqli_error($conn);
            $status = "error";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Anggota - Trustmedis</title>
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
                    <h2 class="text-2xl font-bold text-gray-800">Tambah Anggota Team</h2>
                    <p class="text-gray-500">Daftarkan personil baru ke dalam sistem</p>
                </div>
            </div>

            <?php if($message): ?>
                <div class="max-w-2xl mb-6 p-4 rounded-lg bg-red-50 border border-red-200 text-red-700 flex items-center">
                    <i class="fas fa-exclamation-circle mr-3"></i>
                    <?= $message ?>
                </div>
            <?php endif; ?>

            <div class="max-w-2xl bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
                <form action="" method="POST" class="p-8 space-y-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Nama Lengkap</label>
                        <input type="text" name="nama" placeholder="Contoh: Andi Pratama" required
                            class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#00D285] outline-none transition placeholder-gray-300">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Alamat</label>
                        <textarea name="alamat" rows="3" placeholder="Masukkan alamat lengkap..." required
                            class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#00D285] outline-none transition placeholder-gray-300"></textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Nomor HP / WA</label>
                            <input type="text" name="no_hp" placeholder="08123456789" required
                                class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#00D285] outline-none transition placeholder-gray-300">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Pilih Tim</label>
                            <select name="tim" required
                                class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#00D285] outline-none transition cursor-pointer">
                                <option value="" disabled selected>-- Pilih Tim --</option>
                                <option value="PRODUCT">PRODUCT</option>
                                <option value="ENGINER">ENGINER</option>
                            </select>
                        </div>
                    </div>

                    <div class="pt-4 flex items-center justify-end space-x-4 border-t border-gray-100">
                        <a href="team.php" class="text-gray-500 hover:text-gray-700 font-medium text-sm">Kembali</a>
                        <button type="submit" name="submit"
                            class="bg-[#00D285] text-white px-8 py-3 rounded-lg font-bold hover:bg-[#00b572] shadow-lg transition duration-200 flex items-center">
                            <i class="fas fa-save mr-2"></i> Simpan Anggota
                        </button>
                    </div>
                </form>
            </div>
        </main>
    </div>

</body>
</html>