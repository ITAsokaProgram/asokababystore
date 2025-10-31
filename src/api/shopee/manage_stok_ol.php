<?php
session_start();
ini_set('display_errors', 0);
require_once __DIR__ . '/../../utils/Logger.php';
require_once __DIR__ . '/../../auth/middleware_login.php'; 
$logger = new AppLogger('shopee_manage_stok_ol.log');

try {
    include '../../../aa_kon_sett.php';
} catch (Throwable $t) {
    $logger->critical("Gagal memuat file koneksi: ". $t->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal Server Error: Gagal memuat file.']);
    exit();
}

header('Content-Type: application/json');
$user_kode = 'UNKNOWN';

try {
    $authHeader = null;
    if (function_exists('getallheaders')) {
        $headers = getallheaders();
        if (isset($headers['Authorization'])) {
            $authHeader = $headers['Authorization'];
        }
    }
    if ($authHeader === null && isset($_SERVER['HTTP_AUTHORIZATION'])) {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
    }
    if ($authHeader === null && isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
        $authHeader = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
    }
    if ($authHeader === null || !preg_match('/^Bearer\s(\S+)$/', $authHeader, $matches)) {
        http_response_code(401);
        $debug_msg = $authHeader === null ? 'Header Authorization tidak ditemukan oleh server.' : 'Format header salah.';
        echo json_encode(['success' => false, 'message' => "Token tidak ditemukan atau format salah. ($debug_msg)"]);
        exit;
    }
    $token = $matches[1]; 
    $decoded = verify_token($token);
    $isTokenValidAdmin = is_object($decoded) && isset($decoded->kode);
    if (!$isTokenValidAdmin) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Token tidak valid.']);
        exit;
    }
    $user_kode = $decoded->kode; 
} catch (Exception $e) {
    http_response_code(500);
    $logger->error("Token validation error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Token validation error: ' . $e->getMessage()]);
    exit;
}

if (!isset($conn) || !$conn instanceof mysqli) {
    $logger->critical("Objek koneksi database (\$conn) tidak ada.");
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Koneksi database tidak terinisialisasi.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $logger->warning("Method Not Allowed: ". $_SERVER['REQUEST_METHOD']);
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit();
}

$conn->begin_transaction();

try {
    
    $sku = trim($_POST['sku'] ?? ''); 
    $kd_store = trim($_POST['kd_store'] ?? '');
    $plu = trim($_POST['plu'] ?? '');
    $descp = trim($_POST['descp'] ?? '');
    $vendor = trim($_POST['vendor'] ?? '');
    $hrg_beli = (float)($_POST['hrg_beli'] ?? 0);
    $price = (float)($_POST['price'] ?? 0);

    $qty_rec = 0; 

    $ppn_rec = $hrg_beli * 0.11;
    $netto_rec = $hrg_beli + $ppn_rec;

    $avg_cost = 0;


    if (empty($kd_store) || empty($plu) || empty($sku) || empty($descp)) {
        throw new Exception("Validasi gagal: KD_STORE, PLU, SKU (item_n), dan Deskripsi wajib diisi.");
    }

    $sql_stok_ol = "INSERT INTO s_stok_ol
                        (KD_STORE, plu, ITEM_N, DESCP, VENDOR, avg_cost, hrg_beli, ppn, netto, price, Qty, Tgl_Entry)
                    VALUES
                        (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
                    ON DUPLICATE KEY UPDATE
                        ITEM_N = VALUES(ITEM_N),
                        DESCP = VALUES(DESCP),
                        VENDOR = VALUES(VENDOR),
                        avg_cost = VALUES(avg_cost), 
                        hrg_beli = VALUES(hrg_beli),
                        ppn = VALUES(ppn),           
                        netto = VALUES(netto),         
                        price = VALUES(price),
                        Tgl_Update = NOW()";

    $stmt_stok_ol = $conn->prepare($sql_stok_ol);
    if ($stmt_stok_ol === false) {
        throw new Exception("Database prepare failed (s_stok_ol): ". $conn->error);
    }

    $stmt_stok_ol->bind_param("sssssdddddi",
        $kd_store, $plu, $sku, $descp, $vendor,
        $avg_cost, 
        $hrg_beli,
        $ppn_rec,   
        $netto_rec, 
        $price,
        $qty_rec    
    );
    if (!$stmt_stok_ol->execute()) {
        throw new Exception("Gagal eksekusi query (s_stok_ol): ". $stmt_stok_ol->error);
    }
    $affected_rows = $stmt_stok_ol->affected_rows;
    $stmt_stok_ol->close();

    
    $message = "Produk (SKU: {$sku}) berhasil dimasukkan ke stok online";

    $conn->commit();
    echo json_encode([
        'success' => true,
        'message' => $message
    ]);

} catch (Throwable $t) {
    $conn->rollback();
    $logger->critical("🔥 FATAL ERROR (TRANSACTION ROLLED BACK): ". $t->getMessage(), [
        'file' => $t->getFile(),
        'line' => $t->getLine()
    ]);
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => "Terjadi kesalahan: ". $t->getMessage() 
    ]);
}
$conn->close();
?>