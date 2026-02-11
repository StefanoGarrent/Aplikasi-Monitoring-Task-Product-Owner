<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header("Location: ../index.php");
    exit;
}

require_once 'koneksi.php';

if (isset($_POST['import']) && isset($_FILES['import_file'])) {
    $file = $_FILES['import_file'];
    $fileName = $file['name'];
    $fileTmp = $file['tmp_name'];
    $fileError = $file['error'];

    // Validasi error upload
    if ($fileError !== 0) {
        header("Location: ../tambah_task.php?status=error&msg=Error saat upload file");
        exit;
    }

    // Validasi ekstensi file
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    $allowedExt = ['csv', 'xlsx', 'xls'];

    if (!in_array($fileExt, $allowedExt)) {
        header("Location: ../tambah_task.php?status=error&msg=Format file tidak valid. Gunakan CSV atau Excel (.xlsx, .xls)");
        exit;
    }

    $imported = 0;
    $failed = 0;
    $errors = [];

    // Proses file CSV
    if ($fileExt === 'csv') {
        if (($handle = fopen($fileTmp, "r")) !== FALSE) {
            $row = 0;
            while (($data = fgetcsv($handle, 1000, ",", '"', "\\")) !== FALSE) {
                $row++;

                // Skip header row
                if ($row === 1) continue;

                // Validasi jumlah kolom
                if (count($data) < 7) {
                    $failed++;
                    $errors[] = "Baris $row: Data tidak lengkap";
                    continue;
                }

                $product = mysqli_real_escape_string($conn, trim($data[0]));
                $faskes = mysqli_real_escape_string($conn, trim($data[1]));
                $jenis = mysqli_real_escape_string($conn, trim($data[2]));
                $fitur = mysqli_real_escape_string($conn, trim($data[3]));
                $keterangan = mysqli_real_escape_string($conn, trim($data[4]));
                $enginer = mysqli_real_escape_string($conn, trim($data[5]));
                $tgl_release = mysqli_real_escape_string($conn, trim($data[6]));

                // Validasi data tidak kosong
                if (empty($product) || empty($faskes) || empty($jenis) || empty($fitur) || empty($enginer) || empty($tgl_release)) {
                    $failed++;
                    $errors[] = "Baris $row: Ada kolom yang kosong";
                    continue;
                }

                // Validasi jenis task
                $jenisValid = ['Fitur Berbayar', 'Regulasi', 'Saran Fitur', 'Prioritas'];
                if (!in_array($jenis, $jenisValid)) {
                    $failed++;
                    $errors[] = "Baris $row: Jenis task '$jenis' tidak valid";
                    continue;
                }

                // Validasi Product ada di database
                $checkProduct = mysqli_query($conn, "SELECT nama FROM team WHERE nama = '$product' AND tim = 'PRODUCT'");
                if (mysqli_num_rows($checkProduct) == 0) {
                    $failed++;
                    $errors[] = "Baris $row: Product '$product' tidak ditemukan di database";
                    continue;
                }

                // Validasi Faskes ada di database
                $checkFaskes = mysqli_query($conn, "SELECT nama FROM client WHERE nama = '$faskes'");
                if (mysqli_num_rows($checkFaskes) == 0) {
                    $failed++;
                    $errors[] = "Baris $row: Client/Faskes '$faskes' tidak ditemukan di database";
                    continue;
                }

                // Validasi Enginer ada di database
                $checkEnginer = mysqli_query($conn, "SELECT nama FROM team WHERE nama = '$enginer' AND tim = 'ENGINER'");
                if (mysqli_num_rows($checkEnginer) == 0) {
                    $failed++;
                    $errors[] = "Baris $row: Enginer '$enginer' tidak ditemukan di database";
                    continue;
                }

                // Validasi format tanggal
                $date = DateTime::createFromFormat('Y-m-d', $tgl_release);
                if (!$date || $date->format('Y-m-d') !== $tgl_release) {
                    // Coba format lain
                    $date = DateTime::createFromFormat('d/m/Y', $tgl_release);
                    if ($date) {
                        $tgl_release = $date->format('Y-m-d');
                    } else {
                        $failed++;
                        $errors[] = "Baris $row: Format tanggal salah (gunakan YYYY-MM-DD atau DD/MM/YYYY)";
                        continue;
                    }
                }

                // Insert ke database
                $query = "INSERT INTO task (product, faskes, jenis, fitur, keterangan, task_url, enginer, tgl_release, status_cek) 
                         VALUES ('$product', '$faskes', '$jenis', '$fitur', '$keterangan', '-', '$enginer', '$tgl_release', 'Belum di cek')";

                if (mysqli_query($conn, $query)) {
                    $imported++;
                } else {
                    $failed++;
                    $errors[] = "Baris $row: " . mysqli_error($conn);
                }
            }
            fclose($handle);
        }
    }
    // Proses file Excel
    else if ($fileExt === 'xlsx' || $fileExt === 'xls') {
        // Cek apakah library PhpSpreadsheet tersedia
        $autoloadPath = __DIR__ . '/../vendor/autoload.php';

        if (!file_exists($autoloadPath)) {
            header("Location: ../tambah_task.php?status=error&msg=" . urlencode("Library PhpSpreadsheet belum terinstall. Jalankan: composer require phpoffice/phpspreadsheet"));
            exit;
        }

        require_once $autoloadPath;

        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($fileTmp);
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();

            foreach ($rows as $index => $data) {
                $row = $index + 1;

                // Skip header row
                if ($row === 1) continue;

                // Skip baris kosong
                if (empty(array_filter($data))) continue;

                // Validasi jumlah kolom
                if (count($data) < 7) {
                    $failed++;
                    $errors[] = "Baris $row: Data tidak lengkap";
                    continue;
                }

                $product = mysqli_real_escape_string($conn, trim($data[0]));
                $faskes = mysqli_real_escape_string($conn, trim($data[1]));
                $jenis = mysqli_real_escape_string($conn, trim($data[2]));
                $fitur = mysqli_real_escape_string($conn, trim($data[3]));
                $keterangan = mysqli_real_escape_string($conn, trim($data[4]));
                $enginer = mysqli_real_escape_string($conn, trim($data[5]));
                $tgl_release = trim($data[6]);

                // Handle Excel date format (serial number)
                if (is_numeric($tgl_release) && $tgl_release > 0) {
                    try {
                        $dateObj = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($tgl_release);
                        $tgl_release = $dateObj->format('Y-m-d');
                    } catch (Exception $e) {
                        $failed++;
                        $errors[] = "Baris $row: Format tanggal Excel tidak valid";
                        continue;
                    }
                } else {
                    // Handle text date format
                    $date = DateTime::createFromFormat('Y-m-d', $tgl_release);
                    if (!$date || $date->format('Y-m-d') !== $tgl_release) {
                        $date = DateTime::createFromFormat('d/m/Y', $tgl_release);
                        if ($date) {
                            $tgl_release = $date->format('Y-m-d');
                        } else {
                            $failed++;
                            $errors[] = "Baris $row: Format tanggal salah (gunakan YYYY-MM-DD atau DD/MM/YYYY)";
                            continue;
                        }
                    }
                }

                // Validasi data tidak kosong
                if (empty($product) || empty($faskes) || empty($jenis) || empty($fitur) || empty($enginer) || empty($tgl_release)) {
                    $failed++;
                    $errors[] = "Baris $row: Ada kolom yang kosong";
                    continue;
                }

                // Validasi jenis task
                $jenisValid = ['Fitur Berbayar', 'Regulasi', 'Saran Fitur', 'Prioritas'];
                if (!in_array($jenis, $jenisValid)) {
                    $failed++;
                    $errors[] = "Baris $row: Jenis task '$jenis' tidak valid";
                    continue;
                }

                // Validasi Product ada di database
                $checkProduct = mysqli_query($conn, "SELECT nama FROM team WHERE nama = '$product' AND tim = 'PRODUCT'");
                if (mysqli_num_rows($checkProduct) == 0) {
                    $failed++;
                    $errors[] = "Baris $row: Product '$product' tidak ditemukan di database";
                    continue;
                }

                // Validasi Faskes ada di database
                $checkFaskes = mysqli_query($conn, "SELECT nama FROM client WHERE nama = '$faskes'");
                if (mysqli_num_rows($checkFaskes) == 0) {
                    $failed++;
                    $errors[] = "Baris $row: Client/Faskes '$faskes' tidak ditemukan di database";
                    continue;
                }

                // Validasi Enginer ada di database
                $checkEnginer = mysqli_query($conn, "SELECT nama FROM team WHERE nama = '$enginer' AND tim = 'ENGINER'");
                if (mysqli_num_rows($checkEnginer) == 0) {
                    $failed++;
                    $errors[] = "Baris $row: Enginer '$enginer' tidak ditemukan di database";
                    continue;
                }

                // Insert ke database
                $query = "INSERT INTO task (product, faskes, jenis, fitur, keterangan, task_url, enginer, tgl_release, status_cek) 
                         VALUES ('$product', '$faskes', '$jenis', '$fitur', '$keterangan', '-', '$enginer', '$tgl_release', 'Belum di cek')";

                if (mysqli_query($conn, $query)) {
                    $imported++;
                } else {
                    $failed++;
                    $errors[] = "Baris $row: " . mysqli_error($conn);
                }
            }
        } catch (Exception $e) {
            header("Location: ../tambah_task.php?status=error&msg=" . urlencode("Error membaca file Excel: " . $e->getMessage()));
            exit;
        }
    }

    // Redirect dengan status
    if ($imported > 0) {
        $msg = "$imported data berhasil diimport";
        if ($failed > 0) {
            $msg .= ", $failed data gagal";
        }
        header("Location: ../task.php?status=imported&msg=" . urlencode($msg));
    } else {
        $errorMsg = "Import gagal. ";
        if (!empty($errors)) {
            $errorMsg .= implode("; ", array_slice($errors, 0, 3));
        }
        header("Location: ../tambah_task.php?status=error&msg=" . urlencode($errorMsg));
    }
    exit;
} else {
    header("Location: ../tambah_task.php");
    exit;
}
