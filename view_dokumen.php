<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header("Location: index.php");
    exit;
}

require_once 'services/koneksi.php';

// Validasi ID Client
if (!isset($_GET['id'])) {
    header("Location: client.php");
    exit;
}
$id_client = mysqli_real_escape_string($conn, $_GET['id']);

// Ambil Data Client untuk Header
$qClient = mysqli_query($conn, "SELECT * FROM client WHERE id = '$id_client'");
$dataClient = mysqli_fetch_assoc($qClient);

if (!$dataClient) {
    header("Location: client.php");
    exit;
}

// QUERY UTAMA (PERBAIKAN NAMA KOLOM):
// Menggunakan 'created_at' bukan 'uploaded_at'
$qDoc = "SELECT dokumen.*, 
         GROUP_CONCAT(task.fitur SEPARATOR '<br>') as list_fitur,
         COUNT(task.id) as jumlah_task
         FROM dokumen 
         LEFT JOIN task ON task.id_dokumen = dokumen.id 
         WHERE dokumen.id_client = '$id_client' 
         GROUP BY dokumen.id
         ORDER BY dokumen.created_at DESC"; // <-- Perbaikan di sini

$resDoc = mysqli_query($conn, $qDoc);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dokumen - <?= htmlspecialchars($dataClient['nama']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; }
        .modal-enter { animation: fade-in-down 0.3s ease-out; }
        @keyframes fade-in-down {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
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
            <nav class="flex-1">
                <a href="client.php" class="flex items-center px-6 py-3 bg-[#00D285] text-white font-bold hover:bg-[#00b572] transition">
                    <i class="fas fa-arrow-left mr-3"></i> Kembali ke Client
                </a>
            </nav>
        </aside>

        <main class="flex-1 p-10">
            <div class="flex justify-between items-center mb-8">
                <div>
                    <h2 class="text-3xl font-bold text-gray-800">Dokumen Client</h2>
                    <p class="text-gray-500 mt-1">
                        <i class="fas fa-hospital-alt mr-1"></i> <?= htmlspecialchars($dataClient['nama']) ?> 
                        <span class="mx-2">•</span> 
                        <i class="fas fa-map-marker-alt mr-1"></i> <?= htmlspecialchars($dataClient['kota']) ?>
                    </p>
                </div>
                <a href="tambah_dokumen.php?id=<?= $id_client ?>" 
                   class="bg-[#003674] text-white px-5 py-2.5 rounded-lg hover:bg-[#002b55] transition shadow-md flex items-center">
                    <i class="fas fa-cloud-upload-alt mr-2"></i> Upload Dokumen
                </a>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <table class="w-full">
                    <thead class="bg-[#E9E9F2] border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Nama Dokumen</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Link Dokumen</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Download</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Tanggal Upload</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Task Terkait</th>
                            <th class="px-6 py-4 text-center text-xs font-bold text-gray-600 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php if(mysqli_num_rows($resDoc) > 0): ?>
                            <?php while($row = mysqli_fetch_assoc($resDoc)): ?>
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4">
                                    <div class="flex items-start">
                                        <?php
                                            if(pathinfo($row['file_path'], PATHINFO_EXTENSION) === 'pdf') {
                                                // Tampilkan ikon PDF khusus
                                        ?>
                                                <div class="flex-shrink-0 h-10 w-10 bg-red-100 rounded-lg flex items-center justify-center text-red-500 mr-3">
                                                    <i class="fas fa-file-pdf text-2xl"></i>
                                                </div>
                                        <?php
                                            } else {
                                                // Tampilkan ikon dokumen umum
                                                ?>
                                                <div class="flex-shrink-0 h-10 w-10 bg-blue-100 rounded-lg flex items-center justify-center text-blue-500 mr-3">
                                                    <i class="fas fa-file-alt text-2xl"></i>
                                                </div>
                                        <?php
                                            } 
                                        ?>
                                        <div>
                                            <span class="text-sm font-bold text-gray-800 block mb-1">
                                                <?= htmlspecialchars($row['judul']) ?>
                                            </span>
                                            <span class="text-[10px] <?= $row['jenis'] == 'UAT' ? 'bg-blue-100 text-blue-600' : 'bg-orange-100 text-orange-600' ?> px-2 py-0.5 rounded-full border border-gray-200 font-bold">
                                                <?= htmlspecialchars($row['jenis']) ?>
                                            </span>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-6 py-4">
                                    <?php if(!empty($row['doc_url'])): ?>
                                        <a href="<?= htmlspecialchars($row['doc_url']) ?>" target="_blank" class="inline-flex items-center text-blue-600 hover:text-blue-800 text-sm font-medium">
                                            <i class="fas fa-external-link-alt mr-2"></i> Buka Link
                                        </a>
                                    <?php else: ?>
                                        <span class="text-sm text-gray-400">-</span>
                                    <?php endif; ?>
                                </td>

                                <td class="px-6 py-4">
                                    <?php if(!empty($row['file_path']) && $row['file_path'] != '-'): ?>
                                        <a href="<?= htmlspecialchars($row['file_path']) ?>" download class="inline-flex items-center text-green-600 hover:text-green-800 text-sm font-medium">
                                            <i class="fas fa-download mr-2"></i> Download
                                        </a>
                                    <?php else: ?>
                                        <span class="text-sm text-gray-400">-</span>
                                    <?php endif; ?>
                                </td>

                                <td class="px-6 py-4 text-sm text-gray-500">
                                    <div class="flex flex-col">
                                        <span class="font-medium text-gray-700"><?= date('d M Y', strtotime($row['created_at'])) ?></span>
                                        <span class="text-xs">Pukul <?= date('H:i', strtotime($row['created_at'])) ?></span>
                                    </div>
                                </td>
                                
                                <td class="px-6 py-4">
                                    <?php if($row['list_fitur']): ?>
                                        <div class="bg-blue-50 border border-blue-100 rounded-lg p-3 relative group">
                                            <div class="flex justify-between items-center mb-2 pb-2 border-b border-blue-100">
                                                <span class="text-xs font-bold text-blue-800">
                                                    <i class="fas fa-link mr-1"></i> <?= $row['jumlah_task'] ?> Task Terhubung
                                                </span>
                                                <button onclick="openModal(<?= $row['id'] ?>, <?= $id_client ?>)" 
                                                        class="text-blue-400 hover:text-blue-700 transition" title="Edit Relasi Task">
                                                    <i class="fas fa-pencil-alt text-xs"></i>
                                                </button>
                                            </div>
                                            <div class="text-xs text-gray-700 leading-relaxed pl-2 border-l-2 border-blue-300">
                                                <?= $row['list_fitur'] // Sudah mengandung <br> dari query ?>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <button onclick="openModal(<?= $row['id'] ?>, <?= $id_client ?>)" 
                                                class="w-full py-2 px-3 border-2 border-dashed border-gray-300 rounded-lg text-gray-500 text-xs font-medium hover:border-blue-400 hover:text-blue-500 hover:bg-blue-50 transition flex items-center justify-center">
                                            <i class="fas fa-plus-circle mr-2"></i> Hubungkan Task
                                        </button>
                                    <?php endif; ?>
                                </td>

                                <td class="px-6 py-4 text-center">
                                    <a href="delete_dokumen.php?id=<?= $row['id'] ?>&client=<?= $id_client ?>" 
                                       onclick="return confirm('Apakah Anda yakin ingin menghapus dokumen ini? \n\nPERHATIAN: Task yang terhubung akan dilepas (tidak terhapus).')" 
                                       class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-red-50 text-red-500 hover:bg-red-100 hover:text-red-700 transition"
                                       title="Hapus Dokumen">
                                        <i class="fas fa-trash-alt text-sm"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center text-gray-400">
                                        <i class="far fa-folder-open text-4xl mb-3 opacity-50"></i>
                                        <p class="text-sm">Belum ada dokumen yang diunggah untuk client ini.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <div id="taskModal" class="fixed inset-0 bg-gray-900 bg-opacity-60 hidden z-50 flex justify-center items-center backdrop-blur-sm transition-opacity">
        <div class="bg-white w-full max-w-lg rounded-xl shadow-2xl modal-enter transform transition-all scale-100 mx-4 flex flex-col max-h-[90vh]">
            <div class="flex justify-between items-center p-5 border-b border-gray-100">
                <div>
                    <h3 class="text-lg font-bold text-gray-800">Pilih Task Terkait</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Centang task yang relevan dengan dokumen ini</p>
                </div>
                <button onclick="closeModal()" class="text-gray-400 hover:text-red-500 transition rounded-lg p-1 hover:bg-red-50">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <form action="services/simpan_link_task.php" method="POST" class="flex-1 overflow-hidden flex flex-col">
                <input type="hidden" name="id_dokumen" id="modalDocId">
                <input type="hidden" name="id_client" id="modalClientId">
                
                <div class="p-5 overflow-y-auto flex-1 bg-gray-50" id="taskListContainer">
                    <div class="flex flex-col items-center justify-center py-8 text-gray-400">
                        <i class="fas fa-circle-notch fa-spin text-3xl mb-3 text-[#003674]"></i>
                        <p class="text-sm">Sedang memuat daftar task...</p>
                    </div>
                </div>

                <div class="p-5 border-t border-gray-100 flex justify-end space-x-3 bg-white rounded-b-xl">
                    <button type="button" onclick="closeModal()" class="px-5 py-2.5 text-gray-600 text-sm font-semibold hover:bg-gray-100 rounded-lg transition">
                        Batal
                    </button>
                    <button type="submit" name="simpan_relasi" class="px-5 py-2.5 bg-[#003674] text-white text-sm font-bold rounded-lg hover:bg-[#002b55] shadow-lg hover:shadow-xl transition flex items-center">
                        <i class="fas fa-save mr-2"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function openModal(docId, clientId) {
        const modal = document.getElementById('taskModal');
        modal.classList.remove('hidden');
        document.getElementById('modalDocId').value = docId;
        document.getElementById('modalClientId').value = clientId;

        const container = document.getElementById('taskListContainer');
        container.innerHTML = `
            <div class="flex flex-col items-center justify-center py-10 text-gray-400">
                <i class="fas fa-circle-notch fa-spin text-3xl mb-3 text-[#003674]"></i>
                <p class="text-sm font-medium">Mengambil data task...</p>
            </div>
        `;

        fetch(`services/get_tasks_checkbox.php?client_id=${clientId}&doc_id=${docId}`)
            .then(response => response.json())
            .then(data => {
                container.innerHTML = '';
                if (data.length === 0) {
                    container.innerHTML = `
                        <div class="text-center py-8 px-4 border-2 border-dashed border-gray-200 rounded-xl">
                            <i class="fas fa-tasks text-gray-300 text-3xl mb-2"></i>
                            <p class="text-gray-500 text-sm font-medium">Tidak ada task yang tersedia.</p>
                            <p class="text-xs text-gray-400 mt-1">Semua task mungkin sudah selesai atau sudah dihubungkan ke dokumen lain.</p>
                        </div>
                    `;
                } else {
                    data.forEach(task => {
                        const isChecked = task.checked ? 'checked' : '';
                        const bgClass = task.checked ? 'bg-blue-50 border-blue-300 shadow-sm ring-1 ring-blue-200' : 'bg-white border-gray-200 hover:border-blue-300';
                        const item = `
                            <label class="flex items-start p-3 mb-2 border rounded-xl cursor-pointer transition-all duration-200 ${bgClass} group">
                                <div class="flex items-center h-5">
                                    <input type="checkbox" name="task_ids[]" value="${task.id}" ${isChecked} 
                                           class="w-5 h-5 text-[#003674] border-gray-300 rounded focus:ring-[#003674] focus:ring-offset-0 transition cursor-pointer">
                                </div>
                                <div class="ml-3 text-sm">
                                    <span class="block font-semibold text-gray-800 group-hover:text-[#003674] transition-colors">${task.fitur}</span>
                                    <div class="flex items-center mt-1 space-x-2">
                                        <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded bg-gray-200 text-gray-600">${task.jenis}</span>
                                        ${task.checked ? '<span class="text-[10px] text-blue-600 font-medium"><i class="fas fa-check-circle"></i> Terpilih</span>' : ''}
                                    </div>
                                </div>
                            </label>
                        `;
                        container.innerHTML += item;
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                container.innerHTML = `
                    <div class="text-center py-6 text-red-500">
                        <i class="fas fa-exclamation-triangle text-2xl mb-2"></i>
                        <p class="text-sm">Gagal memuat data task.</p>
                        <button onclick="openModal(${docId}, ${clientId})" class="mt-2 text-xs underline hover:text-red-700">Coba lagi</button>
                    </div>
                `;
            });
    }

    function closeModal() {
        document.getElementById('taskModal').classList.add('hidden');
    }

    document.getElementById('taskModal').addEventListener('click', function(e) {
        if (e.target === this) closeModal();
    });
    </script>

</body>
</html>