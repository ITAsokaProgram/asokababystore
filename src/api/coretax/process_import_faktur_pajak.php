<?php
session_start();
ini_set('display_errors', 0);
ini_set('memory_limit', '512M');
set_time_limit(300);

require_once __DIR__ . '/../../../aa_kon_sett.php';
require_once __DIR__ . '/../../auth/middleware_login.php';
require_once __DIR__ . '/../../../vendor/autoload.php'; // Pastikan path vendor autoload benar

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

    // Load Store Map
    $storeMap = [];
    $queryStore = $conn->query("SELECT Kd_Store, Nm_Alias FROM kode_store");
    if ($queryStore) {
        while ($rowStore = $queryStore->fetch_assoc()) {
            $key = strtoupper(trim($rowStore['Nm_Alias']));
            if (!empty($key))
                $storeMap[$key] = $rowStore['Kd_Store'];
        }
    }

    $count_success = 0;
    $count_fail = 0;
    $count_skip = 0;
    $logs = [];

    // Prepared Statements
    $stmtCheck = $conn->prepare("SELECT id FROM ff_faktur_pajak WHERE no_invoice = ? OR nsfp = ?");
    $stmtInsert = $conn->prepare("INSERT INTO ff_faktur_pajak 
        (tgl_faktur, no_invoice, nsfp, nama_supplier, kode_store, dpp, dpp_nilai_lain, ppn, total, kd_user) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    foreach ($rows as $idx => $row) {
        if ($idx < 2)
            continue; // Skip header

        // Mapping Kolom Excel (Sesuaikan dengan Template Import)
        // A: Tgl, B: Invoice, C: NSFP, D: Nama Supplier, E: Alias Cabang, F: DPP, G: DPP Lain, H: PPN
        $tgl_raw = $row['A'];
        $no_invoice = trim($row['B'] ?? '');
        $nsfp = trim($row['C'] ?? '');
        $nama_supplier = trim($row['D'] ?? '');
        $alias_store_input = trim($row['E'] ?? '');

        // Validasi Dasar
        if (empty($no_invoice) && empty($nsfp) && empty($nama_supplier))
            continue;

        if (empty($no_invoice) || empty($nsfp) || empty($alias_store_input)) {
            $count_fail++;
            $logs[] = "Baris $idx: Gagal - Invoice, NSFP, atau Cabang kosong.";
            continue;
        }

        // Cek Toko
        $lookupKey = strtoupper($alias_store_input);
        $kode_store_final = '';
        if (isset($storeMap[$lookupKey])) {
            $kode_store_final = $storeMap[$lookupKey];
        } else {
            $count_fail++;
            $logs[] = "Baris $idx: Gagal - Cabang '$alias_store_input' tidak dikenal.";
            continue;
        }

        // Angka
        $dpp = clean_excel_number($row['F'] ?? 0);
        $dpp_lain = clean_excel_number($row['G'] ?? 0);
        $ppn = clean_excel_number($row['H'] ?? 0);
        $total = $dpp + $ppn;

        // Tanggal
        $tgl_faktur = date('Y-m-d');
        try {
            if (!empty($tgl_raw)) {
                if (is_numeric($tgl_raw)) {
                    $tgl_faktur = Date::excelToDateTimeObject($tgl_raw)->format('Y-m-d');
                } else {
                    $ts = strtotime($tgl_raw);
                    if ($ts)
                        $tgl_faktur = date('Y-m-d', $ts);
                }
            }
        } catch (\Throwable $th) {
            // Default today
        }

        // Cek Duplikat (NSFP atau Invoice)
        $stmtCheck->bind_param("ss", $no_invoice, $nsfp);
        $stmtCheck->execute();
        $stmtCheck->store_result();
        if ($stmtCheck->num_rows > 0) {
            $count_skip++;
            continue;
        }

        // Insert
        $stmtInsert->bind_param(
            "sssssddddi",
            $tgl_faktur,
            $no_invoice,
            $nsfp,
            $nama_supplier,
            $kode_store_final,
            $dpp,
            $dpp_lain,
            $ppn,
            $total,
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