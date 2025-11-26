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
    if (empty($_POST['kode_store'])) {
        throw new Exception("Harap pilih Cabang/Store terlebih dahulu.");
    }
    $kode_store_input = trim($_POST['kode_store']);
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
    if (empty($rows) || !isset($rows[1])) {
        throw new Exception("File Excel kosong atau tidak terbaca.");
    }
    $headerRow = $rows[1];
    $expectedHeaders = [
        'A' => 'NPWP Penjual',
        'B' => 'Nama Penjual',
        'C' => 'Nomor Faktur Pajak',
        'D' => 'Tanggal Faktur Pajak',
        'E' => 'Masa Pajak',
        'F' => 'Tahun',
        'G' => 'Masa Pajak Pengkreditkan',
        'H' => 'Tahun Pajak Pengkreditan',
        'I' => 'Status Faktur',
        'J' => 'Harga Jual/Penggantian/DPP',
        'K' => 'DPP Nilai Lain/DPP',
        'L' => 'PPN',
        'M' => 'PPnBM',
        'N' => 'Perekam',
        'O' => 'Nomor SP2D',
        'P' => 'Valid',
        'Q' => 'Dilaporkan',
        'R' => 'Dilaporkan oleh Penjual'
    ];
    foreach ($expectedHeaders as $col => $expectedText) {
        $actualText = isset($headerRow[$col]) ? trim($headerRow[$col]) : '';
        if (strcasecmp($actualText, $expectedText) !== 0) {
            throw new Exception("Format Header Salah! Kolom $col seharusnya '$expectedText'. Pastikan menggunakan template yang sesuai.");
        }
    }
    $count_success = 0;
    $count_duplicate = 0;
    $count_fail = 0;
    $logs = [];
    $duplicate_examples = [];
    $sql = "INSERT INTO ff_coretax (
        kode_store, npwp_penjual, nama_penjual, nomor_faktur_pajak, tgl_faktur_pajak, 
        masa_pajak, tahun, masa_pajak_pengkreditkan, tahun_pajak_pengkreditan, 
        status_faktur, harga_jual, dpp_nilai_lain, ppn, ppnbm, 
        perekam, nomor_sp2d, valid, dilaporkan, dilaporkan_oleh_penjual
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if (!$stmt)
        throw new Exception("DB Error: " . $conn->error);
    foreach ($rows as $idx => $row) {
        if ($idx < 2)
            continue;
        $raw_npwp = preg_replace('/[^0-9]/', '', $row['A']);
        $npwp = formatNPWP($raw_npwp);
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
            "ssssssisisddddssiii",
            $kode_store_input,
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
            if ($conn->errno == 1062) {
                $count_duplicate++;
                if (count($duplicate_examples) < 3) {
                    $duplicate_examples[] = $no_faktur;
                }
            } else {
                $count_fail++;
                $logs[] = "Baris $idx Gagal: " . $stmt->error;
            }
        }
    }
    $message = "Proses Selesai.\n";
    $message .= "✅ Berhasil: $count_success\n";
    $message .= "⚠️ Duplikat (Dilewati): $count_duplicate\n";
    if ($count_duplicate > 0) {
        $message .= "   Data Duplikat: " . implode(", ", $duplicate_examples);
        if ($count_duplicate > 3) {
            $message .= ", dll...";
        }
        $message .= "\n";
    }
    $message .= "❌ Gagal: $count_fail";
    echo json_encode([
        'success' => true,
        'message' => $message,
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
function formatNPWP($digits)
{
    if (empty($digits))
        return '';
    if (strlen($digits) == 15) {
        return substr($digits, 0, 2) . '.' . substr($digits, 2, 3) . '.' . substr($digits, 5, 3) . '.' . substr($digits, 8, 1) . '-' . substr($digits, 9, 3) . '.' . substr($digits, 12, 3);
    }
    if (strlen($digits) == 16) {
        return substr($digits, 0, 2) . '.' . substr($digits, 2, 3) . '.' . substr($digits, 5, 3) . '.' . substr($digits, 8, 1) . '-' . substr($digits, 9, 3) . '.' . substr($digits, 12, 4);
    }
    return $digits;
}
?>