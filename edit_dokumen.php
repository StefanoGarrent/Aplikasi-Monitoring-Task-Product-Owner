<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header("Location: index.php");
    exit;
}

require_once 'services/koneksi.php';

// Validasi ID Dokumen dan Client
if (!isset($_GET['id']) || !isset($_GET['client'])) {
    header("Location: client.php");
    exit;
}

$id = mysqli_real_escape_string($conn, $_GET['id']);
$id_client = mysqli_real_escape_string($conn, $_GET['client']);

// Ambil data dokumen
$dokumenQuery = mysqli_query($conn, "SELECT * FROM dokumen WHERE id = '$id' AND id_client = '$id_client'");
$dokumen = mysqli_fetch_assoc($dokumenQuery);

if (!$dokumen) {
    header("Location: view_dokumen.php?id=$id_client");
    exit;
}

// Ambil data client
$clientQuery = mysqli_query($conn, "SELECT * FROM client WHERE id = '$id_client'");
$client = mysqli_fetch_assoc($clientQuery);

$message = "";
$messageType = "";

// Proses update dokumen
if (isset($_POST['submit'])) {
    $judul = mysqli_real_escape_string($conn, $_POST['judul']);
    $jenis = mysqli_real_escape_string($conn, $_POST['jenis']);
    $doc_url = mysqli_real_escape_string($conn, $_POST['doc_url']);
    $file_path = $dokumen['file_path']; // Keep existing file by default

    // Handle file upload (jika ada file baru)
    if (isset($_FILES['file_upload']) && $_FILES['file_upload']['error'] == 0) {
        $allowed_types = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        $file_type = $_FILES['file_upload']['type'];
        $file_size = $_FILES['file_upload']['size'];
        $max_size = 10 * 1024 * 1024; // 10MB

        if (in_array($file_type, $allowed_types)) {
            if ($file_size <= $max_size) {
                // Buat folder uploads jika belum ada
                $upload_dir = "uploads/dokumen/";
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }

                // Hapus file lama jika ada
                if (!empty($dokumen['file_path']) && file_exists($dokumen['file_path'])) {
                    unlink($dokumen['file_path']);
                }

                // Generate nama file unik
                $file_ext = pathinfo($_FILES['file_upload']['name'], PATHINFO_EXTENSION);
                $new_filename = $judul . "_" . $id_client . "_" . time() . "." . $file_ext;
                $target_path = $upload_dir . $new_filename;

                if (move_uploaded_file($_FILES['file_upload']['tmp_name'], $target_path)) {
                    $file_path = $target_path;
                } else {
                    $message = "Gagal mengupload file!";
                    $messageType = "error";
                }
            } else {
                $message = "Ukuran file terlalu besar! Maksimal 10MB.";
                $messageType = "error";
            }
        } else {
            $message = "Tipe file tidak diizinkan! Hanya PDF dan DOC/DOCX yang diperbolehkan.";
            $messageType = "error";
        }
    }

    // Handle hapus file existing
    if (isset($_POST['remove_file']) && $_POST['remove_file'] == '1') {
        if (!empty($dokumen['file_path']) && file_exists($dokumen['file_path'])) {
            unlink($dokumen['file_path']);
        }
        $file_path = "";
    }

    // Jika tidak ada error, update database
    if (empty($message)) {
        $update = mysqli_query($conn, "UPDATE dokumen SET 
                                       judul = '$judul', 
                                       jenis = '$jenis', 
                                       doc_url = '$doc_url', 
                                       file_path = '$file_path' 
                                       WHERE id = '$id'");
        
        if ($update) {
            header("Location: view_dokumen.php?id=$id_client&status=updated");
            exit;
        } else {
            $message = "Gagal memperbarui dokumen: " . mysqli_error($conn);
            $messageType = "error";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Dokumen - <?= htmlspecialchars($client['nama']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-[#F0F2F5]">

    <div class="flex min-h-screen">
        <!-- Sidebar -->
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

        <!-- Main Content -->
        <main class="flex-1 p-10">
            <div class="max-w-2xl mx-auto">
                <!-- Header -->
                <div class="mb-8">
                    <a href="view_dokumen.php?id=<?= $id_client ?>" class="text-blue-600 hover:underline text-sm mb-2 inline-block">
                        <i class="fas fa-arrow-left mr-1"></i> Kembali ke Daftar Dokumen
                    </a>
                    <h2 class="text-2xl font-bold text-gray-800">Edit Dokumen</h2>
                    <p class="text-gray-500 mt-1">
                        <i class="fas fa-hospital mr-1"></i> <?= htmlspecialchars($client['nama']) ?>
                    </p>
                </div>

                <!-- Error Message -->
                <?php if(!empty($message)): ?>
                    <div class="mb-6 p-4 rounded-lg <?= $messageType == 'error' ? 'bg-red-50 border border-red-200 text-red-700' : 'bg-green-50 border border-green-200 text-green-700' ?> flex items-center text-sm">
                        <i class="fas <?= $messageType == 'error' ? 'fa-exclamation-circle' : 'fa-check-circle' ?> mr-3 text-lg"></i>
                        <?= $message ?>
                    </div>
                <?php endif; ?>

                <!-- Form -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
                    <form method="POST" enctype="multipart/form-data">
                        <!-- Judul Dokumen -->
                        <div class="mb-6">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Judul Dokumen <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="judul" required
                                   value="<?= htmlspecialchars($dokumen['judul']) ?>"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00D285] focus:border-[#00D285] transition"
                                   placeholder="Masukkan judul dokumen">
                        </div>

                        <!-- Jenis Dokumen -->
                        <div class="mb-6">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Jenis Dokumen <span class="text-red-500">*</span>
                            </label>
                            <select name="jenis" required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00D285] focus:border-[#00D285] transition bg-white">
                                <option value="">-- Pilih Jenis --</option>
                                <option value="UAT" <?= $dokumen['jenis'] == 'UAT' ? 'selected' : '' ?>>UAT</option>
                                <option value="MOM" <?= $dokumen['jenis'] == 'MOM' ? 'selected' : '' ?>>MOM</option>
                            </select>
                        </div>

                        <!-- Link URL -->
                        <div class="mb-6">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Link URL <span class="text-gray-400 font-normal">(Opsional)</span>
                            </label>
                            <input type="url" name="doc_url"
                                   value="<?= htmlspecialchars($dokumen['doc_url']) ?>"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00D285] focus:border-[#00D285] transition"
                                   placeholder="https://example.com/dokumen">
                            <p class="text-xs text-gray-500 mt-1">Masukkan link eksternal jika ada (Google Drive, dll)</p>
                        </div>

                        <!-- Current File -->
                        <?php if(!empty($dokumen['file_path'])): ?>
                        <div class="mb-4">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                File Saat Ini
                            </label>
                            <div class="flex items-center justify-between bg-gray-50 p-4 rounded-lg border border-gray-200">
                                <div class="flex items-center">
                                    <i class="fas fa-file-alt text-blue-500 mr-3 text-lg"></i>
                                    <a href="<?= htmlspecialchars($dokumen['file_path']) ?>" target="_blank" class="text-blue-600 hover:underline">
                                        <?= basename($dokumen['file_path']) ?>
                                    </a>
                                </div>
                                <label class="flex items-center cursor-pointer text-red-500 hover:text-red-700">
                                    <input type="checkbox" name="remove_file" value="1" class="mr-2">
                                    <i class="fas fa-trash mr-1"></i> Hapus File
                                </label>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Upload File -->
                        <div class="mb-8">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                <?= !empty($dokumen['file_path']) ? 'Ganti File' : 'Upload File' ?> <span class="text-gray-400 font-normal">(Opsional)</span>
                            </label>
                            <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-[#00D285] transition">
                                <input type="file" name="file_upload" id="file_upload" class="hidden" accept=".pdf,.doc,.docx">
                                <label for="file_upload" class="cursor-pointer">
                                    <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 mb-3"></i>
                                    <p class="text-sm text-gray-600">Klik untuk memilih file baru</p>
                                    <p class="text-xs text-gray-400 mt-1">PDF, DOC, DOCX (Maks. 10MB)</p>
                                </label>
                                <p id="file-name" class="text-sm text-green-600 mt-2 hidden"></p>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="flex justify-end space-x-4">
                            <a href="view_dokumen.php?id=<?= $id_client ?>" 
                               class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition font-medium">
                                Batal
                            </a>
                            <button type="submit" name="submit"
                                    class="px-6 py-3 bg-[#003674] text-white rounded-lg hover:bg-[#002b55] transition font-medium">
                                <i class="fas fa-save mr-2"></i> Update Dokumen
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Show selected file name
        document.getElementById('file_upload').addEventListener('change', function(e) {
            const fileName = e.target.files[0]?.name;
            const fileNameDisplay = document.getElementById('file-name');
            if (fileName) {
                fileNameDisplay.textContent = 'File baru dipilih: ' + fileName;
                fileNameDisplay.classList.remove('hidden');
            } else {
                fileNameDisplay.classList.add('hidden');
            }
        });
    </script>

</body>
</html>
