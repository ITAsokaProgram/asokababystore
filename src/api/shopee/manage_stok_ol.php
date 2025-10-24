<?php
session_start();

ini_set('display_errors', 0);
require_once __DIR__ . '/../../utils/Logger.php';
$logger = new AppLogger('shopee_manage_stok_ol.log');

try {
    include '../../../aa_kon_sett.php';
} catch (Throwable $t) {
    $logger->critical("Gagal memuat file koneksi: " . $t->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal Server Error: Gagal memuat file.']);
    exit();
}

header('Content-Type: application/json');

try {
    $logger->info("Memulai request manage stok online...");

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $logger->warning("Method Not Allowed: " . $_SERVER['REQUEST_METHOD']);
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
        exit();
    }

    if (!isset($conn) || !$conn instanceof mysqli) {
        $logger->critical("Objek koneksi database (\$conn) tidak ada.");
        throw new Exception("Koneksi database tidak terinisialisasi.");
    }

    // Ambil data dari POST
    $mode = $_POST['mode'] ?? 'add';
    $sku = trim($_POST['sku'] ?? ''); // ini item_n
    $kd_store = trim($_POST['kd_store'] ?? '');
    $plu = trim($_POST['plu'] ?? '');
    $descp = trim($_POST['descp'] ?? '');
    $vendor = trim($_POST['vendor'] ?? '');
    $hrg_beli = (float)($_POST['hrg_beli'] ?? 0);
    $price = (float)($_POST['price'] ?? 0);

    $logger->info("Payload diterima:", $_POST);

    // Validasi
    if (empty($kd_store) || empty($plu) || empty($sku) || empty($descp)) {
        $logger->warning("Validasi gagal: KD_STORE, PLU, SKU, atau DESCP kosong.");
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Validasi gagal: KD_STORE, PLU, SKU (item_n), dan Deskripsi wajib diisi.']);
        exit();
    }

    // Query INSERT ... ON DUPLICATE KEY UPDATE
    // Primary Key s_stok_ol adalah (KD_STORE, plu)
    $sql = "INSERT INTO s_stok_ol 
                (KD_STORE, plu, ITEM_N, DESCP, VENDOR, hrg_beli, price, Tgl_Entry) 
            VALUES 
                (?, ?, ?, ?, ?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE 
                ITEM_N = VALUES(ITEM_N),
                DESCP = VALUES(DESCP),
                VENDOR = VALUES(VENDOR),
                hrg_beli = VALUES(hrg_beli),
                price = VALUES(price),
                Tgl_Update = NOW()";
    
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        $logger->error("Database prepare failed: " . $conn->error);
        throw new Exception("Database prepare failed: " . $conn->error);
    }

    // Tipe data: s, s, s, s, s, d, d
    $stmt->bind_param("sssssdd", $kd_store, $plu, $sku, $descp, $vendor, $hrg_beli, $price);
    
    if (!$stmt->execute()) {
        $logger->error("Gagal eksekusi query: " . $stmt->error);
        throw new Exception("Gagal eksekusi query: " . $stmt->error);
    }

    $affected_rows = $stmt->affected_rows;
    $stmt->close();

    $message = '';
    // affected_rows: 1 = INSERT, 2 = UPDATE, 0 = Tidak ada perubahan
    if ($affected_rows === 1) {
        $message = "Produk (SKU: {$sku}) berhasil dimasukkan ke stok online.";
    } else if ($affected_rows === 2) { 
        $message = "Data stok online (SKU: {$sku}) berhasil diperbarui.";
    } else { 
        $message = "Data tidak berubah (data yang diinput sama dengan di database).";
    }

    $logger->success("Eksekusi berhasil. " . $message, ['affected_rows' => $affected_rows]);

    echo json_encode([
        'success' => true,
        'message' => $message
    ]);

} catch (Throwable $t) {
    $logger->critical("🔥 FATAL ERROR (Throwable): " . $t->getMessage(), [
        'file' => $t->getFile(),
        'line' => $t->getLine()
    ]);
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => "Terjadi kesalahan internal server: " . $t->getMessage()
    ]);
}
?>