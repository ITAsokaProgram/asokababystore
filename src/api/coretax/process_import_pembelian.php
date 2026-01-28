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
    $kd_user = 0;
    $verif = authenticate_request();

    $kd_user = $verif->id ?? $verif->kode ?? 0;
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
    $stmtCheck = $conn->prepare("SELECT id FROM ff_pembelian WHERE no_invoice = ?");
    $stmtInsert = $conn->prepare("INSERT INTO ff_pembelian 
        (tgl_nota, no_invoice, kode_supplier, nama_supplier, kode_store, status, dpp, dpp_nilai_lain, ppn, total_terima_fp, kd_user) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($rows as $idx => $row) {
        if ($idx < 2)
            continue;
        $tgl_raw = $row['A'];
        $no_invoice = trim($row['B'] ?? '');
        $kode_supplier = trim($row['C'] ?? '');
        $nama_supplier = trim($row['D'] ?? '');
        $alias_store_input = trim($row['E'] ?? '');
        $status = strtoupper(trim($row['F'] ?? ''));
        if (empty($no_invoice) && empty($nama_supplier) && empty($alias_store_input)) {
            continue;
        }
        if (empty($no_invoice) || empty($nama_supplier) || empty($alias_store_input)) {
            $count_fail++;
            $logs[] = "Baris $idx: Gagal - No Invoice, Nama Supplier, atau Nama Cabang tidak boleh kosong.";
            continue;
        }
        $lookupKey = strtoupper($alias_store_input);
        $kode_store_final = '';
        if (isset($storeMap[$lookupKey])) {
            $kode_store_final = $storeMap[$lookupKey];
        } else {
            $count_fail++;
            $logs[] = "Baris $idx: Gagal - Cabang dengan nama '$alias_store_input' tidak ditemukan di database.";
            continue;
        }
        $dpp = clean_excel_number($row['G'] ?? 0);
        $dpp_lain = clean_excel_number($row['H'] ?? 0);
        $ppn = clean_excel_number($row['I'] ?? 0);
        if ($dpp === false || $dpp_lain === false || $ppn === false) {
            $count_fail++;
            $logs[] = "Baris $idx: Gagal - Format angka pada DPP atau PPN salah (harus numerik).";
            continue;
        }
        $total = $dpp + $ppn;
        $tgl_nota = date('Y-m-d');
        try {
            if (!empty($tgl_raw)) {
                if (is_numeric($tgl_raw)) {
                    $tgl_nota = Date::excelToDateTimeObject($tgl_raw)->format('Y-m-d');
                } else {
                    $ts = strtotime($tgl_raw);
                    if ($ts)
                        $tgl_nota = date('Y-m-d', $ts);
                }
            }
        } catch (\Throwable $th) {
            $logs[] = "Baris $idx: Warning - Format tanggal salah, menggunakan hari ini.";
        }
        $stmtCheck->bind_param("s", $no_invoice);
        $stmtCheck->execute();
        $stmtCheck->store_result();
        if ($stmtCheck->num_rows > 0) {
            $count_skip++;
            continue;
        }
        if (!in_array($status, ['PKP', 'NON PKP', 'BTKP'])) {
            $status = 'PKP';
        }
        $stmtInsert->bind_param(
            "ssssssddddi",
            $tgl_nota,
            $no_invoice,
            $kode_supplier,
            $nama_supplier,
            $kode_store_final,
            $status,
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
    $msg .= "⏩ Skip (Sudah Ada): $count_skip\n";
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