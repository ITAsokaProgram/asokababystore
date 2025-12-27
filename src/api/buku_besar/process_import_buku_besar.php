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
    if ($val === null || $val === '')
        return null;
    $cleaned = preg_replace('/[^0-9.,-]/', '', $val);
    if ($cleaned === '')
        return null;
    if (strpos($cleaned, '.') !== false && strpos($cleaned, ',') !== false) {
        $cleaned = str_replace('.', '', $cleaned);
        $cleaned = str_replace(',', '.', $cleaned);
    } elseif (strpos($cleaned, ',') !== false) {
        $cleaned = str_replace(',', '.', $cleaned);
    }
    return (double) $cleaned;
}

function parse_excel_date($val)
{
    if (empty($val))
        return null;
    try {
        if (is_numeric($val)) {
            return Date::excelToDateTimeObject($val)->format('Y-m-d');
        } else {
            $ts = strtotime($val);
            return $ts ? date('Y-m-d', $ts) : null;
        }
    } catch (\Throwable $th) {
        return null;
    }
}

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

    $stmtCheck = $conn->prepare("SELECT id FROM buku_besar WHERE no_faktur = ? AND kode_store = ? AND total_bayar = ?");

    // UPDATE SQL: Tambahkan kolom status dan top
    $stmtInsert = $conn->prepare("INSERT INTO buku_besar 
        (tanggal_bayar, tgl_nota, no_faktur, kode_supplier, nama_supplier, 
         kode_store, store_bayar, nilai_faktur, potongan, ket_potongan, 
         total_bayar, ket, kd_user, status, top) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    foreach ($rows as $idx => $row) {
        if ($idx < 2)
            continue;

        $raw_tgl_bayar = $row['A'];
        $raw_tgl_nota = $row['B'];
        $no_faktur = trim($row['C'] ?? '');
        $kode_supplier = trim($row['D'] ?? '');
        $nama_supplier = trim($row['E'] ?? '');
        $alias_store = trim($row['F'] ?? '');
        $alias_bayar = trim($row['G'] ?? '');
        $raw_nilai = $row['H'];
        $raw_potongan = $row['I'];
        $ket_potongan = trim($row['J'] ?? '');
        $raw_total = $row['K'];
        $ket = trim($row['L'] ?? ''); // Ini sekarang MOP

        // --- DATA BARU ---
        $status = trim($row['M'] ?? ''); // Kolom M: Status
        $raw_top = $row['N'] ?? '';      // Kolom N: TOP (Tanggal Jatuh Tempo)

        if (empty($no_faktur) && empty($alias_store))
            continue;

        if (empty($no_faktur)) {
            $count_fail++;
            $logs[] = "Baris $idx: Gagal - No Faktur wajib diisi.";
            continue;
        }

        $tgl_bayar = parse_excel_date($raw_tgl_bayar);
        $tgl_nota = parse_excel_date($raw_tgl_nota);

        // Parse TOP sebagai tanggal
        $top = parse_excel_date($raw_top);

        $lookupKey = strtoupper($alias_store);
        $kode_store_final = isset($storeMap[$lookupKey]) ? $storeMap[$lookupKey] : null;

        if (!$kode_store_final) {
            $count_fail++;
            $logs[] = "Baris $idx: Gagal - Cabang Inv '$alias_store' tidak dikenali.";
            continue;
        }

        $kode_store_bayar_final = null;
        if (!empty($alias_bayar)) {
            $kode_store_bayar_final = $alias_bayar;
        }

        $nilai_faktur = clean_excel_number($raw_nilai);
        $potongan = clean_excel_number($raw_potongan) ?? 0;
        $total_bayar = clean_excel_number($raw_total) ?? 0;

        if ($nilai_faktur === null || $nilai_faktur == 0) {
            $count_fail++;
            $logs[] = "Baris $idx: Gagal - Nilai Faktur wajib diisi.";
            continue;
        }

        $kode_supplier = ($kode_supplier === '') ? null : $kode_supplier;
        $nama_supplier = ($nama_supplier === '') ? null : $nama_supplier;
        $ket_potongan = ($ket_potongan === '') ? null : $ket_potongan;
        $ket = ($ket === '') ? null : $ket;
        $status = ($status === '') ? null : $status;
        // $top sudah null jika parsing gagal atau kosong, jadi aman

        $stmtCheck->bind_param("ssd", $no_faktur, $kode_store_final, $total_bayar);
        $stmtCheck->execute();
        $stmtCheck->store_result();

        if ($stmtCheck->num_rows > 0) {
            $count_skip++;
            continue;
        }

        // UPDATE BIND PARAM: Total 15 variabel (tambah 2 string 'ss' di akhir untuk status & top)
        $stmtInsert->bind_param(
            "sssssssddsdsiss", // Updated types
            $tgl_bayar,
            $tgl_nota,
            $no_faktur,
            $kode_supplier,
            $nama_supplier,
            $kode_store_final,
            $kode_store_bayar_final,
            $nilai_faktur,
            $potongan,
            $ket_potongan,
            $total_bayar,
            $ket,
            $kd_user,
            $status, // Baru
            $top     // Baru
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
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>