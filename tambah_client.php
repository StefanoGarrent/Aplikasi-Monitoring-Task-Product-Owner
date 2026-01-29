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
    
    // Logika Konversi Kota: surabaya -> Surabaya
    $kotaInput = mysqli_real_escape_string($conn, $_POST['kota']);
    $kota   = ucwords(strtolower($kotaInput));

    $tipe   = mysqli_real_escape_string($conn, $_POST['tipe']);
    $pic    = mysqli_real_escape_string($conn, $_POST['pic']);
    $no_pic = mysqli_real_escape_string($conn, $_POST['no_pic']);

    // Cek duplikasi nama faskes
    $check = mysqli_query($conn, "SELECT nama FROM client WHERE nama = '$nama'");
    
    if (mysqli_num_rows($check) > 0) {
        $message = "Nama Faskes sudah terdaftar! Gunakan nama lain atau tambahkan pembeda.";
        $status = "error";
    } else {
        $insert = mysqli_query($conn, "INSERT INTO client (nama, alamat, kota, tipe, pic, no_pic) 
                                       VALUES ('$nama', '$alamat', '$kota', '$tipe', '$pic', '$no_pic')");
        
        if ($insert) {
            header("Location: client.php?status=added");
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
    <title>Tambah Client - Trustmedis</title>
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
            <div class="mb-8 flex items-center">
                <a href="client.php" class="mr-4 text-gray-400 hover:text-[#003674] transition">
                    <i class="fas fa-arrow-left text-xl"></i>
                </a>
                <div>
                    <h2 class="text-2xl font-bold text-gray-800">Tambah Client Faskes</h2>
                    <p class="text-gray-500">Daftarkan faskes baru ke dalam sistem monitoring</p>
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
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Nama Faskes</label>
                        <input type="text" name="nama" placeholder="Contoh: RS Permata Bunda" required
                            class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#00D285] outline-none transition">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Alamat Lengkap</label>
                        <textarea name="alamat" rows="3" placeholder="Jl. Raya No. 123..." required
                            class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#00D285] outline-none transition"></textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Kota</label>
                            <input type="text" name="kota" placeholder="Contoh: surabaya" required
                                style="text-transform: capitalize;"
                                class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#00D285] outline-none transition">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Tipe Faskes</label>
                            <select name="tipe" required
                                class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#00D285] outline-none transition cursor-pointer">
                                <option value="" disabled selected>-- Pilih Tipe --</option>
                                <option value="A">TIPE A</option>
                                <option value="B">TIPE B</option>
                                <option value="C">TIPE C</option>
                                <option value="PRATAMA">TIPE PRATAMA</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-6 pt-4 border-t border-gray-100">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Nama PIC</label>
                            <input type="text" name="pic" placeholder="Nama penanggung jawab" required
                                class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#00D285] outline-none transition">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">No. HP PIC</label>
                            <input type="text" name="no_pic" placeholder="0812xxxx" required
                                class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#00D285] outline-none transition">
                        </div>
                    </div>

                    <div class="pt-4 flex items-center justify-end space-x-4">
                        <a href="client.php" class="text-gray-500 hover:text-gray-700 font-medium text-sm">Batal</a>
                        <button type="submit" name="submit"
                            class="bg-[#00D285] text-white px-10 py-3 rounded-lg font-bold hover:bg-[#00b572] shadow-lg transition flex items-center">
                            <i class="fas fa-save mr-2"></i> Simpan Client
                        </button>
                    </div>
                </form>
            </div>
        </main>
    </div>

</body>
</html>