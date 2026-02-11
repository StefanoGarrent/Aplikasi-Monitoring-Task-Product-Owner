<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header("Location: index.php");
    exit;
}

require_once 'services/koneksi.php';

$message = "";
$status = "";

$resProduct = mysqli_query($conn, "SELECT nama FROM team WHERE tim = 'PRODUCT'");
$resEnginer = mysqli_query($conn, "SELECT nama FROM team WHERE tim = 'ENGINER'");
$resFaskes  = mysqli_query($conn, "SELECT nama FROM client");

if (isset($_POST['submit'])) {
    $product  = mysqli_real_escape_string($conn, $_POST['product']);
    $faskes   = mysqli_real_escape_string($conn, $_POST['faskes']);
    $jenis    = mysqli_real_escape_string($conn, $_POST['jenis']);
    $fitur    = mysqli_real_escape_string($conn, $_POST['fitur']);
    $keterangan = mysqli_real_escape_string($conn, $_POST['keterangan']);
    $task     = mysqli_real_escape_string($conn, $_POST['task_url']);
    $enginer  = mysqli_real_escape_string($conn, $_POST['enginer']);
    $tgl_release = mysqli_real_escape_string($conn, $_POST['tgl_release']);

    $insert = mysqli_query($conn, "INSERT INTO task (product, faskes, jenis, fitur, keterangan, task_url, enginer, tgl_release, status_cek) 
                                   VALUES ('$product', '$faskes', '$jenis', '$fitur', '$keterangan', '$task', '$enginer', '$tgl_release', 'Belum di cek')");

    if ($insert) {
        header("Location: task.php?status=added");
        exit;
    } else {
        $message = "Gagal menambah data: " . mysqli_error($conn);
        $status = "error";
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

        body {
            font-family: 'Inter', sans-serif;
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
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <a href="task.php" class="mr-4 text-gray-400 hover:text-[#003674] transition">
                            <i class="fas fa-arrow-left text-xl"></i>
                        </a>
                        <div>
                            <h2 class="text-2xl font-bold text-gray-800">Tambah Task</h2>
                            <p class="text-gray-500">Tambahkan task baru ke dalam sistem</p>
                        </div>
                    </div>

                    <!-- Tombol Import File -->
                    <button onclick="openImportModal()" class="bg-[#00D285] text-white px-6 py-3 rounded-lg font-semibold hover:bg-[#00b572] shadow-lg transition duration-200 flex items-center">
                        <i class="fas fa-file-import mr-2"></i>
                        Import File
                    </button>
                </div>
            </div>

            <!-- Modal Import File -->
            <div id="importModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden items-center justify-center">
                <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full mx-4 transform transition-all">
                    <!-- Modal Header -->
                    <div class="bg-gradient-to-r from-[#003674] to-[#00D285] text-white px-6 py-4 rounded-t-2xl flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="bg-white bg-opacity-20 rounded-lg p-2">
                                <i class="fas fa-file-excel text-2xl"></i>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold">Import Data Task</h3>
                                <p class="text-sm text-gray-100">Upload file CSV atau Excel</p>
                            </div>
                        </div>
                        <button onclick="closeImportModal()" class="text-white hover:text-gray-200 transition">
                            <i class="fas fa-times text-2xl"></i>
                        </button>
                    </div>

                    <!-- Modal Body -->
                    <div class="p-6">
                        <form action="services/import_tasks.php" method="POST" enctype="multipart/form-data" id="importForm">
                            <div class="space-y-6">
                                <!-- File Upload Area -->
                                <div class="border-2 border-dashed border-gray-300 rounded-xl p-8 text-center hover:border-[#00D285] transition">
                                    <label for="file_input" class="cursor-pointer block">
                                        <input type="file" name="import_file" id="file_input" accept=".csv,.xlsx,.xls" required
                                            class="hidden" onchange="displayFileName(this)">
                                        <div id="file_upload_area">
                                            <i class="fas fa-cloud-upload-alt text-5xl text-gray-400 mb-4"></i>
                                            <p class="text-lg font-semibold text-gray-700 mb-2">Klik untuk pilih file</p>
                                            <p class="text-sm text-gray-500">atau drag & drop file disini</p>
                                            <p class="text-xs text-gray-400 mt-2">Format: CSV (.csv) atau Excel (.xlsx, .xls)</p>
                                        </div>
                                        <div id="file_selected" class="hidden">
                                            <i class="fas fa-file-excel text-5xl text-[#00D285] mb-4"></i>
                                            <p id="file_name" class="text-lg font-semibold text-gray-700"></p>
                                            <p id="file_size" class="text-sm text-gray-500"></p>
                                            <button type="button" onclick="resetFile()" class="mt-3 text-sm text-red-500 hover:text-red-700">
                                                <i class="fas fa-times-circle mr-1"></i>Hapus file
                                            </button>
                                        </div>
                                    </label>
                                </div>

                                <!-- Info & Template Download -->
                                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                    <div class="flex items-start space-x-3">
                                        <i class="fas fa-info-circle text-blue-500 mt-1"></i>
                                        <div class="flex-1">
                                            <p class="text-sm text-gray-700 font-medium mb-2">Format Kolom yang Diperlukan:</p>
                                            <p class="text-xs text-gray-600 mb-3">Product, Faskes, Jenis, Fitur, Keterangan, Enginer, Tanggal Release</p>
                                            <div class="flex items-center gap-4">
                                                <a href="assets/template_import_task.csv" download class="inline-flex items-center text-sm text-[#00D285] hover:text-[#00b572] font-semibold">
                                                    <i class="fas fa-file-csv mr-2"></i>Download Template CSV
                                                </a>
                                                <span class="text-gray-400">|</span>
                                                <a href="assets/template_import_task.xlsx" download class="inline-flex items-center text-sm text-[#003674] hover:text-[#002b55] font-semibold">
                                                    <i class="fas fa-file-excel mr-2"></i>Download Template Excel
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-200">
                                    <button type="button" onclick="closeImportModal()" class="px-6 py-2.5 text-gray-700 hover:text-gray-900 font-medium transition">
                                        Batal
                                    </button>
                                    <button type="submit" name="import" class="bg-[#003674] text-white px-8 py-2.5 rounded-lg font-semibold hover:bg-[#002b55] shadow-md transition duration-200 flex items-center">
                                        <i class="fas fa-cloud-upload-alt mr-2"></i>
                                        Upload & Import
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <script>
                function openImportModal() {
                    document.getElementById('importModal').classList.remove('hidden');
                    document.getElementById('importModal').classList.add('flex');
                }

                function closeImportModal() {
                    document.getElementById('importModal').classList.add('hidden');
                    document.getElementById('importModal').classList.remove('flex');
                    resetFile();
                }

                function displayFileName(input) {
                    if (input.files[0]) {
                        const fileName = input.files[0].name;
                        const fileSize = (input.files[0].size / 1024).toFixed(2);

                        document.getElementById('file_upload_area').classList.add('hidden');
                        document.getElementById('file_selected').classList.remove('hidden');
                        document.getElementById('file_name').textContent = fileName;
                        document.getElementById('file_size').textContent = `Ukuran: ${fileSize} KB`;
                    }
                }

                function resetFile() {
                    document.getElementById('file_input').value = '';
                    document.getElementById('file_upload_area').classList.remove('hidden');
                    document.getElementById('file_selected').classList.add('hidden');
                }

                // Close modal when clicking outside
                document.getElementById('importModal')?.addEventListener('click', function(e) {
                    if (e.target === this) {
                        closeImportModal();
                    }
                });

                // Close modal with ESC key
                document.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape') {
                        closeImportModal();
                    }
                });
            </script>

            <?php if ($message): ?>
                <div class="max-w-2xl mb-6 p-4 rounded-lg bg-red-50 border border-red-200 text-red-700 flex items-center text-sm">
                    <i class="fas fa-exclamation-circle mr-3"></i>
                    <?= $message ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['status'])): ?>
                <?php if ($_GET['status'] == 'error'): ?>
                    <div class="max-w-2xl mb-6 p-4 rounded-lg bg-red-50 border border-red-200 text-red-700 flex items-center text-sm">
                        <i class="fas fa-exclamation-circle mr-3"></i>
                        <?= isset($_GET['msg']) ? htmlspecialchars($_GET['msg']) : 'Terjadi kesalahan saat import!' ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <div class="max-w-2xl bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
                <form action="" method="POST" class="p-8 space-y-6">
                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Pilih Tim Product</label>
                            <select name="product" required class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#00D285] outline-none transition">
                                <option value="" disabled selected>-- Pilih Product --</option>
                                <?php while ($row = mysqli_fetch_assoc($resProduct)): ?>
                                    <option value="<?= htmlspecialchars($row['nama']) ?>"><?= htmlspecialchars($row['nama']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Pilih Tim Enginer</label>
                            <select name="enginer" required class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#00D285] outline-none transition">
                                <option value="" disabled selected>-- Pilih Enginer --</option>
                                <?php while ($row = mysqli_fetch_assoc($resEnginer)): ?>
                                    <option value="<?= htmlspecialchars($row['nama']) ?>"><?= htmlspecialchars($row['nama']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Pilih Client Faskes</label>
                            <select name="faskes" required class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#00D285] outline-none transition">
                                <option value="" disabled selected>-- Pilih Faskes --</option>
                                <?php while ($row = mysqli_fetch_assoc($resFaskes)): ?>
                                    <option value="<?= htmlspecialchars($row['nama']) ?>"><?= htmlspecialchars($row['nama']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Jenis Task</label>
                            <select name="jenis" required class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#00D285] outline-none transition">
                                <option value="" disabled selected>-- Pilih Jenis --</option>
                                <option value="Fitur Berbayar">Fitur Berbayar</option>
                                <option value="Regulasi">Regulasi</option>
                                <option value="Saran Fitur">Saran Fitur</option>
                                <option value="Prioritas">Prioritas</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Tanggal Release</label>
                            <input type="date" name="tgl_release" required class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#00D285] outline-none transition">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Request Fitur</label>
                            <input type="text" name="fitur" placeholder="Nama fitur..." required class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#00D285] outline-none transition">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Keterangan</label>
                        <textarea name="keterangan" rows="2" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#00D285] outline-none transition">-</textarea>
                    </div>

                    <input type="hidden" name="task_url" value="-">

                    <div class="pt-4 flex items-center justify-end space-x-4 border-t border-gray-100">
                        <a href="task.php" class="text-gray-500 hover:text-gray-700 font-medium text-sm">Kembali</a>
                        <button type="submit" name="submit" class="bg-[#00D285] text-white px-8 py-3 rounded-lg font-bold hover:bg-[#00b572] shadow-lg transition duration-200">
                            Simpan Data
                        </button>
                    </div>
                </form>
            </div>
        </main>
    </div>

</body>

</html>