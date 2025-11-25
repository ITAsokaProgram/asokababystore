<?php
session_start();
ini_set('display_errors', 0);
ini_set('memory_limit', '256M');
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
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    if (!preg_match('/^Bearer\s(\S+)$/', $authHeader, $matches)) {
        http_response_code(401);
        throw new Exception('Token tidak ditemukan');
    }
    if (!isset($_FILES['file_excel']) || $_FILES['file_excel']['error'] != UPLOAD_ERR_OK) {
        throw new Exception("Gagal upload file atau file tidak ada.");
    }
    $fileTmpPath = $_FILES['file_excel']['tmp_name'];
    $fileExt = strtolower(pathinfo($_FILES['file_excel']['name'], PATHINFO_EXTENSION));
    if (!in_array($fileExt, ['xlsx', 'xls', 'csv'])) {
        throw new Exception("Format file harus Excel (.xlsx, .xls) atau CSV.");
    }
    try {
        $spreadsheet = IOFactory::load($fileTmpPath);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, true);
    } catch (Exception $e) {
        throw new Exception("Gagal membaca file Excel: " . $e->getMessage());
    }
    $count_success = 0;
    $count_skip = 0;
    $logs = [];
    $sql = "INSERT INTO ff_coretax (
        npwp_penjual, nama_penjual, nomor_faktur_pajak, tgl_faktur_pajak, 
        masa_pajak, tahun, masa_pajak_pengkreditkan, tahun_pajak_pengkreditan, 
        status_faktur, harga_jual, dpp_nilai_lain, ppn, ppnbm, 
        perekam, nomor_sp2d, valid, dilaporkan, dilaporkan_oleh_penjual
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE 
        status_faktur = VALUES(status_faktur),
        valid = VALUES(valid),
        dilaporkan = VALUES(dilaporkan),
        harga_jual = VALUES(harga_jual),
        ppn = VALUES(ppn)
    ";
    $stmt = $conn->prepare($sql);
    if (!$stmt)
        throw new Exception("DB Error: " . $conn->error);
    foreach ($rows as $idx => $row) {
        if ($idx < 2)
            continue;
        $npwp = $row['A'];
        $nama_penjual = $row['B'];
        $no_faktur = trim($row['C']);
        if (empty($no_faktur))
            continue;
        $tgl_faktur = NULL;
        if (!empty($row['D'])) {
            if (is_numeric($row['D'])) {
                $tgl_faktur = Date::excelToDateTimeObject($row['D'])->format('Y-m-d');
            } else {
                $raw_date = str_replace('/', '-', $row['D']);
                $tgl_faktur = date('Y-m-d', strtotime($raw_date));
            }
        }
        $masa_pajak = $row['E'];
        $tahun = (int) $row['F'];
        $masa_kredit = $row['G'];
        $tahun_kredit = (int) $row['H'];
        $status = $row['I'];
        $harga_jual = (double) $row['J'];
        $dpp_lain = (double) $row['K'];
        $ppn = (double) $row['L'];
        $ppnbm = (double) $row['M'];
        $perekam = $row['N'];
        $no_sp2d = $row['O'];
        $valid = is_truthy($row['P']);
        $dilaporkan = is_truthy($row['Q']);
        $dilapor_oleh = is_truthy($row['R']);
        $stmt->bind_param(
            "sssssisisddddssiii",
            $npwp,
            $nama_penjual,
            $no_faktur,
            $tgl_faktur,
            $masa_pajak,
            $tahun,
            $masa_kredit,
            $tahun_kredit,
            $status,
            $harga_jual,
            $dpp_lain,
            $ppn,
            $ppnbm,
            $perekam,
            $no_sp2d,
            $valid,
            $dilaporkan,
            $dilapor_oleh
        );
        if ($stmt->execute()) {
            $count_success++;
        } else {
            $count_skip++;
            $logs[] = "Row $idx Gagal: " . $stmt->error;
        }
    }
    echo json_encode([
        'success' => true,
        'message' => "Proses Selesai. Berhasil: $count_success, Gagal: $count_skip",
        'logs' => $logs
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
function is_truthy($val)
{
    $v = strtolower(trim((string) $val));
    return in_array($v, ['1', 'true', 'ya', 'yes', 'valid', 'sudah']) ? 1 : 0;
}
?>