<?php
session_start();
ini_set('display_errors', 0);
require_once __DIR__ . '/../../utils/Logger.php';
require_once __DIR__ . '/../../auth/middleware_login.php'; 
$logger = new AppLogger('shopee_delete_stok_ol.log');

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
        echo json_encode(['success' => false, 'message' => "Token tidak ditemukan atau format salah."]);
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
    // $logger->info("Token valid. User '{$user_kode}' mengakses delete_stok_ol.");
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

$data = json_decode(file_get_contents('php://input'), true);

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    $logger->warning("Request body JSON tidak valid.");
    echo json_encode(['success' => false, 'message' => 'Request body JSON tidak valid.']);
    exit;
}

$conn->begin_transaction();

try {
    $plu = $data['plu'] ?? null;
    $kd_store = $data['kd_store'] ?? null;

    if (empty($plu) || empty($kd_store)) {
        throw new Exception("Validasi gagal: PLU dan KD_STORE wajib diisi.");
    }
    
    // $logger->info("Memulai request HAPUS stok online...", $data);

    $sql_delete = "DELETE FROM s_stok_ol WHERE plu = ? AND KD_STORE = ?";
    $stmt_delete = $conn->prepare($sql_delete);
    if ($stmt_delete === false) {
        throw new Exception("Database prepare failed (s_stok_ol delete): ". $conn->error);
    }
    
    $stmt_delete->bind_param("ss", $plu, $kd_store);
    
    if (!$stmt_delete->execute()) {
        throw new Exception("Gagal eksekusi query (s_stok_ol delete): ". $stmt_delete->error);
    }
    
    $affected_rows = $stmt_delete->affected_rows;
    $stmt_delete->close();

    if ($affected_rows === 0) {
        throw new Exception("Tidak ada data yang dihapus. PLU '{$plu}' di KD_STORE '{$kd_store}' tidak ditemukan.");
    }

    $conn->commit();
    $message = "Produk (PLU: {$plu}) berhasil dihapus dari stok online (s_stok_ol).";
    $logger->success("Transaksi berhasil di-commit. ". $message);
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