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
try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST')
        throw new Exception('Method Not Allowed');
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
    /* FORMAT EXCEL YANG DIHARAPKAN (MULAI BARIS 2):
       A: Tgl Nota (YYYY-MM-DD atau Excel Date)
       B: No Invoice
       C: Kode Supplier
       D: Nama Supplier
       E: Kode Store (Wajib valid)
       F: Status (PKP/NON PKP)
       G: DPP
       H: PPN
    */
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
        $no_invoice = trim($row['B']);
        $kode_supplier = trim($row['C']);
        $nama_supplier = trim($row['D']);
        $kode_store = trim($row['E']);
        $status = strtoupper(trim($row['F']));
        $dpp = (double) $row['G'];
        $dpp_lain = (double) $row['H'];
        $ppn = (double) $row['I'];
        $total = $dpp + $ppn;
        if (empty($no_invoice) || empty($nama_supplier) || empty($kode_store)) {
            $count_fail++;
            continue;
        }
        $tgl_nota = date('Y-m-d');
        if (!empty($tgl_raw)) {
            if (is_numeric($tgl_raw)) {
                $tgl_nota = Date::excelToDateTimeObject($tgl_raw)->format('Y-m-d');
            } else {
                $ts = strtotime($tgl_raw);
                if ($ts)
                    $tgl_nota = date('Y-m-d', $ts);
            }
        }
        $stmtCheck->bind_param("s", $no_invoice);
        $stmtCheck->execute();
        $stmtCheck->store_result();
        if ($stmtCheck->num_rows > 0) {
            $count_skip++;
            continue;
        }
        if (!in_array($status, ['PKP', 'NON PKP', 'BTKP']))
            $status = 'PKP';
        $stmtInsert->bind_param(
            "ssssssddddi",
            $tgl_nota,
            $no_invoice,
            $kode_supplier,
            $nama_supplier,
            $kode_store,
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
            $logs[] = "Baris $idx Gagal: " . $stmtInsert->error;
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