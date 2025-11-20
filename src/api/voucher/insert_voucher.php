<?php
session_start();
ini_set('display_errors', 0);
require_once __DIR__ . '/../../../../aa_kon_sett.php';
require_once __DIR__ . '/../../../auth/middleware_login.php'; // Asumsi path middleware
require_once __DIR__ . '/../../../utils/Logger.php'; // Asumsi path logger

header('Content-Type: application/json');

// Helper function untuk padding 0 (e.g., 1 -> 00001)
function pad5($number)
{
    return str_pad($number, 5, '0', STR_PAD_LEFT);
}

$logger = new AppLogger('voucher_generator.log');
$user_kode = 'SYSTEM'; // Default jika auth gagal mengambil user

// Auth Check (Simplified for brevity)
try {
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    if (preg_match('/^Bearer\s(\S+)$/', $authHeader, $matches)) {
        $decoded = verify_token($matches[1]);
        if ($decoded && is_object($decoded)) {
            $user_kode = $decoded->kode ?? 'UNKNOWN';
        }
    }
} catch (Exception $e) {
    // Continue, but log auth issue
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);

    // Validasi Input Dasar
    if (empty($input['stores']) || empty($input['kode_manual']) || empty($input['nilai'])) {
        throw new Exception("Data tidak lengkap (Store, Kode Manual, atau Nilai kosong).");
    }

    $stores = $input['stores']; // Array of Kd_Store
    $pemilik = strtoupper(trim($input['pemilik']));
    $nilai = (float) $input['nilai'];
    $tgl_awal = $input['tgl_awal'] . ' 00:00:00'; // Tambah jam
    $tgl_akhir = $input['tgl_akhir'] . ' 23:59:59'; // Tambah jam akhir
    $kode_manual = strtoupper(trim($input['kode_manual']));
    $start_number = (int) $input['start_number'];
    $qty = (int) $input['qty'];

    $tgl_beli = date('Y-m-d H:i:s'); // Tanggal Generate

    $conn->begin_transaction();

    // 1. Ambil Data Alias Store untuk semua store yang dipilih
    $store_ids_str = implode(',', array_map(function ($id) use ($conn) {
        return "'" . $conn->real_escape_string($id) . "'";
    }, $stores));

    $q_store = $conn->query("SELECT Kd_Store, Nm_Alias FROM kode_store WHERE Kd_Store IN ($store_ids_str)");

    $store_aliases = [];
    while ($r = $q_store->fetch_assoc()) {
        $store_aliases[$r['Kd_Store']] = $r['Nm_Alias'];
    }

    $total_inserted = 0;
    $duplicates = 0;

    // Prepare Statement
    // Menggunakan INSERT IGNORE agar jika kode voucher (Primary Key) duplikat, row tersebut diskip tanpa error fatal.
    $stmt = $conn->prepare("INSERT IGNORE INTO voucher_copy (
        kd_voucher, nilai, pakai, sisa, 
        tgl_awal, tgl_akhir, kd_cust, flag, 
        tgl_beli, last_sold, pemilik, kd_store
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NULL, ?, ?)");

    if (!$stmt)
        throw new Exception("Prepare failed: " . $conn->error);

    $default_pakai = 0;
    $default_sisa = $nilai; // Sisa awal = Nilai voucher
    $default_cust = '999999999'; // Default kode customer umum
    $default_flag = 'False';

    // Loop Store
    foreach ($stores as $kd_store) {
        if (!isset($store_aliases[$kd_store]))
            continue;

        $alias = $store_aliases[$kd_store];

        // Loop Quantity
        for ($i = 0; $i < $qty; $i++) {
            // Logic Nomor Urut: Start Number + Iterasi
            $current_seq = $start_number + $i;
            $padded_seq = pad5($current_seq); // 00055

            // PERUBAHAN DISINI: Format dengan tanda hubung (-)
            // Format: ALIAS - KODEMANUAL - NOMOR
            // Contoh: ADET-LEBARAN-00001
            $kd_voucher_final = $alias . '-' . $kode_manual . '-' . $padded_seq;

            // Bind Param
            $stmt->bind_param(
                "sddssssssss",
                $kd_voucher_final,
                $nilai,
                $default_pakai,
                $default_sisa,
                $tgl_awal,
                $tgl_akhir,
                $default_cust,
                $default_flag,
                $tgl_beli,
                $pemilik,
                $kd_store
            );

            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                $total_inserted++;
            } else {
                $duplicates++;
            }
        }
    }

    $stmt->close();
    $conn->commit();

    $msg = "Berhasil membuat $total_inserted voucher.";
    if ($duplicates > 0) {
        $msg .= " ($duplicates voucher duplikat dilewati)";
    }

    echo json_encode([
        'success' => true,
        'message' => $msg,
        'data' => ['inserted' => $total_inserted, 'skipped' => $duplicates]
    ]);

} catch (Exception $e) {
    $conn->rollback();
    $logger->error("Voucher Insert Failed: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} finally {
    if (isset($conn))
        $conn->close();
}
?>