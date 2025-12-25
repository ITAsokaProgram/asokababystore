<?php
session_start();
ini_set('display_errors', 0);
ini_set('memory_limit', '512M');
set_time_limit(300);

require_once __DIR__ . '/../../../aa_kon_sett.php';
require_once __DIR__ . '/../../auth/middleware_login.php';
require_once __DIR__ . '/../../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;

header('Content-Type: application/json');

function clean_excel_number($val)
{
    if (empty($val))
        return 0;
    $cleaned = preg_replace('/[^0-9.,-]/', '', $val);
    if ($cleaned === '')
        return false;

    // Convert format 1.000,00 atau 1,000.00 ke float standard
    if (strpos($cleaned, '.') !== false && strpos($cleaned, ',') !== false) {
        $cleaned = str_replace('.', '', $cleaned);
        $cleaned = str_replace(',', '.', $cleaned);
    } elseif (strpos($cleaned, ',') !== false) {
        $cleaned = str_replace(',', '.', $cleaned);
    }
    return (double) $cleaned;
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST')
        throw new Exception('Method Not Allowed');

    // Auth Check
    $kd_user = 0;
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    if (preg_match('/^Bearer\s(\S+)$/', $authHeader, $matches)) {
        $token = $matches[1];
        $verif = verify_token($token);
        if ($verif)
            $kd_user = $verif->id ?? $verif->kode ?? 0;
    }
    if ($kd_user == 0 && isset($_SESSION['user_id'])) {
        $kd_user = $_SESSION['user_id'];
    }

    if (!isset($_FILES['file_excel']) || $_FILES['file_excel']['error'] != UPLOAD_ERR_OK) {
        throw new Exception("Gagal upload file.");
    }

    $fileTmpPath = $_FILES['file_excel']['tmp_name'];

    try {
        $spreadsheet = IOFactory::load($fileTmpPath);
        $rows = $spreadsheet->getActiveSheet()->toArray(null, true, false, true);
    } catch (Exception $e) {
        throw new Exception("File Excel corrupt atau format salah.");
    }

    // Mapping Kode Store (Alias -> ID)
    $storeMap = [];
    $queryStore = $conn->query("SELECT Kd_Store, Nm_Alias FROM kode_store");
    if ($queryStore) {
        while ($rowStore = $queryStore->fetch_assoc()) {
            $key = strtoupper(trim($rowStore['Nm_Alias']));
            if (!empty($key)) {
                $storeMap[$key] = $rowStore['Kd_Store'];
            }
        }
    }

    $count_success = 0;
    $count_fail = 0;
    $count_skip = 0;
    $logs = [];

    // Cek Duplikat di Buku Besar
    $stmtCheck = $conn->prepare("SELECT id FROM buku_besar WHERE no_faktur = ? AND kode_store = ? AND total_bayar = ?");

    // Insert Query
    $stmtInsert = $conn->prepare("INSERT INTO buku_besar 
        (tanggal_bayar, no_faktur, nama_supplier, kode_store, ket, potongan, total_bayar, tgl_nota, kd_user) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

    /* MAPPING KOLOM EXCEL (Berdasarkan instruksi JS):
       A: Tgl Bayar
       B: No Faktur
       C: Nama Supplier
       D: Nm Cabang (Alias)
       E: Keterangan
       F: Potongan
       G: Total Bayar
    */

    foreach ($rows as $idx => $row) {
        if ($idx < 2)
            continue; // Skip Header

        $tgl_raw = $row['A'];
        $no_faktur = trim($row['B'] ?? '');
        $nama_supplier = trim($row['C'] ?? '');
        $alias_store = trim($row['D'] ?? '');
        $ket = trim($row['E'] ?? '');
        $potongan = clean_excel_number($row['F'] ?? 0);
        $total_bayar = clean_excel_number($row['G'] ?? 0);

        // Validasi Wajib
        if (empty($no_faktur) && empty($nama_supplier) && empty($alias_store))
            continue; // Baris kosong

        if (empty($no_faktur) || empty($alias_store)) {
            $count_fail++;
            $logs[] = "Baris $idx: Gagal - No Faktur dan Nama Cabang wajib diisi.";
            continue;
        }

        // Cek Store
        $lookupKey = strtoupper($alias_store);
        $kode_store_final = '';
        if (isset($storeMap[$lookupKey])) {
            $kode_store_final = $storeMap[$lookupKey];
        } else {
            $count_fail++;
            $logs[] = "Baris $idx: Gagal - Cabang '$alias_store' tidak dikenali.";
            continue;
        }

        // Cek Angka
        if ($potongan === false || $total_bayar === false) {
            $count_fail++;
            $logs[] = "Baris $idx: Gagal - Format angka salah.";
            continue;
        }

        // Parse Tanggal Bayar
        $tgl_bayar = date('Y-m-d');
        try {
            if (!empty($tgl_raw)) {
                if (is_numeric($tgl_raw)) {
                    $tgl_bayar = Date::excelToDateTimeObject($tgl_raw)->format('Y-m-d');
                } else {
                    $ts = strtotime($tgl_raw);
                    if ($ts)
                        $tgl_bayar = date('Y-m-d', $ts);
                }
            }
        } catch (\Throwable $th) {
            $logs[] = "Baris $idx: Warning - Format tanggal salah, menggunakan hari ini.";
        }

        // Set Tgl Nota default sama dengan Tgl Bayar (karena tidak ada di excel map simple)
        $tgl_nota = $tgl_bayar;

        // Cek Duplikat (Strict check: Faktur + Store + Nominal sama)
        $stmtCheck->bind_param("ssd", $no_faktur, $kode_store_final, $total_bayar);
        $stmtCheck->execute();
        $stmtCheck->store_result();
        if ($stmtCheck->num_rows > 0) {
            $count_skip++;
            continue;
        }

        // Insert
        $stmtInsert->bind_param(
            "sssssddsi",
            $tgl_bayar,
            $no_faktur,
            $nama_supplier,
            $kode_store_final,
            $ket,
            $potongan,
            $total_bayar,
            $tgl_nota,
            $kd_user
        );

        if ($stmtInsert->execute()) {
            $count_success++;
        } else {
            $count_fail++;
            $logs[] = "Baris $idx: SQL Error - " . $stmtInsert->error;
        }
    }

    $msg = "<b>Import Selesai</b>\n";
    $msg .= "✅ Berhasil: $count_success\n";
    $msg .= "⏩ Skip (Duplikat): $count_skip\n";
    $msg .= "❌ Gagal: $count_fail";

    echo json_encode([
        'success' => true,
        'message' => $msg,
        'logs' => $logs
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>