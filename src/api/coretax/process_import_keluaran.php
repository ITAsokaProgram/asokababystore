<?php
session_start();
ini_set('display_errors', 0);
ini_set('memory_limit', '512M');
set_time_limit(300);
require_once __DIR__ . '/../../../aa_kon_sett.php';
require_once __DIR__ . '/../../auth/middleware_login.php';
require_once __DIR__ . '/../../../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
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
        $rows = $sheet->toArray(null, true, false, true);
    } catch (Exception $e) {
        throw new Exception("Gagal membaca file Excel: " . $e->getMessage());
    }
    if (empty($rows) || !isset($rows[1])) {
        throw new Exception("File Excel kosong atau tidak terbaca.");
    }
    $headerRow = $rows[1];
    $expectedHeaders = [
        'A' => 'NPWP Pembeli / Identitas lainnya',
        'B' => 'Nama Pembeli',
        'C' => 'Kode Transaksi',
        'D' => 'Nomor Faktur Pajak',
        'E' => 'Tanggal Faktur Pajak',
        'F' => 'Masa Pajak',
        'G' => 'Tahun',
        'H' => 'Status Faktur',
        'I' => 'ESignStatus',
        'J' => 'Harga Jual/Penggantian/DPP',
        'K' => 'DPP Nilai Lain/DPP',
        'L' => 'PPN',
        'M' => 'PPnBM',
        'N' => 'Penandatangan',
        'O' => 'Referensi',
        'P' => 'Dilaporkan oleh Penjual',
        'Q' => 'Dilaporkan oleh Pemungut PPN'
    ];
    foreach ($expectedHeaders as $col => $expectedText) {
        if (!isset($headerRow[$col])) {
            throw new Exception("Format Header Salah! Kolom $col ($expectedText) tidak ditemukan dalam file.");
        }
        $actualText = trim($headerRow[$col]);
        if (stripos($actualText, substr($expectedText, 0, 10)) === false) {
            throw new Exception("Format Header Salah! Kolom $col seharusnya '$expectedText', tapi tertulis '$actualText'.");
        }
    }
    $count_success = 0;
    $count_duplicate = 0;
    $count_fail = 0;
    $logs = [];
    $duplicate_rows = [];
    $sql = "INSERT INTO ff_coretax_keluaran (
        kode_store, npwp_pembeli, nama_pembeli, kode_transaksi, nsfp, tgl_faktur_pajak,
        masa_pajak, tahun, status_faktur, esign_status, 
        harga_jual, dpp_nilai_lain, ppn, ppnbm,
        penandatangan, referensi, dilaporkan_penjual, dilaporkan_pemungut
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if (!$stmt)
        throw new Exception("DB Error: " . $conn->error);
    foreach ($rows as $idx => $row) {
        if ($idx < 2)
            continue;
        $originalRowData = $row;
        $no_faktur = isset($row['D']) ? trim($row['D']) : '';
        if (empty($no_faktur))
            continue;
        $raw_npwp = preg_replace('/[^0-9]/', '', $row['A'] ?? '');
        $npwp_pembeli = formatNPWP($raw_npwp);
        $nama_pembeli = $row['B'] ?? '';
        $kode_transaksi = $row['C'] ?? '';
        $tgl_faktur = NULL;
        $val_tgl = $row['E'] ?? '';
        if (!empty($val_tgl)) {
            if (strpos($val_tgl, 'T') !== false) {
                $tgl_faktur = date('Y-m-d', strtotime($val_tgl));
            } else if (is_numeric($val_tgl)) {
                $tgl_faktur = Date::excelToDateTimeObject($val_tgl)->format('Y-m-d');
            } else {
                $tgl_faktur = date('Y-m-d', strtotime($val_tgl));
            }
        }
        $masa_pajak = $row['F'] ?? '';
        $tahun = (int) ($row['G'] ?? 0);
        $status_faktur = $row['H'] ?? '';
        $esign_status = $row['I'] ?? '';
        $harga_jual = (double) ($row['J'] ?? 0);
        $dpp_lain = (double) ($row['K'] ?? 0);
        $ppn = (double) ($row['L'] ?? 0);
        $ppnbm = (double) ($row['M'] ?? 0);
        $penandatangan = $row['N'] ?? '';
        $referensi = $row['O'] ?? '';
        $dilaporkan_penjual = is_truthy($row['P'] ?? '');
        $dilaporkan_pemungut = $row['Q'] ?? '';
        $stmt->bind_param(
            "ssssssssssddddssis",
            $kode_store_input,
            $npwp_pembeli,
            $nama_pembeli,
            $kode_transaksi,
            $no_faktur,
            $tgl_faktur,
            $masa_pajak,
            $tahun,
            $status_faktur,
            $esign_status,
            $harga_jual,
            $dpp_lain,
            $ppn,
            $ppnbm,
            $penandatangan,
            $referensi,
            $dilaporkan_penjual,
            $dilaporkan_pemungut
        );
        if ($stmt->execute()) {
            $count_success++;
        } else {
            if ($conn->errno == 1062) {
                $count_duplicate++;
                $duplicate_rows[] = $originalRowData;
            } else {
                $count_fail++;
                $logs[] = "Baris $idx Gagal (NSFP: $no_faktur): " . $stmt->error;
            }
        }
    }
    $duplicate_file_base64 = null;
    if (!empty($duplicate_rows)) {
        $spreadsheetDup = new Spreadsheet();
        $sheetDup = $spreadsheetDup->getActiveSheet();
        $sheetDup->setTitle('Data Duplikat');
        $colIndex = 1;
        foreach ($expectedHeaders as $headerText) {
            $sheetDup->setCellValueByColumnAndRow($colIndex, 1, $headerText);
            $colIndex++;
        }
        $rowIndex = 2;
        foreach ($duplicate_rows as $dRow) {
            $colIndex = 1;
            foreach (array_keys($expectedHeaders) as $colKey) {
                $val = $dRow[$colKey] ?? '';
                $sheetDup->setCellValueByColumnAndRow($colIndex, $rowIndex, $val);
                $colIndex++;
            }
            $rowIndex++;
        }
        $lastColumn = 'Q';
        $lastRow = $rowIndex - 1;
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => '000000']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'EEE0E5']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];
        $sheetDup->getStyle('A1:' . $lastColumn . '1')->applyFromArray($headerStyle);
        foreach (range('A', $lastColumn) as $columnID) {
            $sheetDup->getColumnDimension($columnID)->setAutoSize(true);
        }
        $writer = new Xlsx($spreadsheetDup);
        ob_start();
        $writer->save('php://output');
        $xlsData = ob_get_contents();
        ob_end_clean();
        $duplicate_file_base64 = base64_encode($xlsData);
    }
    $message = "<b>Proses Import Keluaran Selesai.</b>\n\n";
    $message .= "✅ Berhasil: $count_success\n";
    $message .= "⚠️ Duplikat (Dilewati): $count_duplicate\n";
    $message .= "❌ Gagal: $count_fail";
    echo json_encode([
        'success' => true,
        'message' => $message,
        'logs' => $logs,
        'has_duplicates' => ($count_duplicate > 0),
        'duplicate_file' => $duplicate_file_base64
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
    if ($v === 'false')
        return 0;
    return in_array($v, ['1', 'true', 'ya', 'yes', 'valid', 'sudah']) ? 1 : 0;
}
function formatNPWP($digits)
{
    if (empty($digits))
        return '';
    if (strlen($digits) == 15) {
        return substr($digits, 0, 2) . '.' . substr($digits, 2, 3) . '.' . substr($digits, 5, 3) . '.' . substr($digits, 8, 1) . '-' . substr($digits, 9, 3) . '.' . substr($digits, 12, 3);
    }
    if (strlen($digits) >= 16) {
        return substr($digits, 0, 2) . '.' . substr($digits, 2, 3) . '.' . substr($digits, 5, 3) . '.' . substr($digits, 8, 1) . '-' . substr($digits, 9, 3) . '.' . substr($digits, 12, 4);
    }
    return $digits;
}
?>