<?php
session_start();
ini_set('display_errors', 0);
require_once __DIR__ . '/../../utils/Logger.php';
require_once __DIR__ . '/../../auth/middleware_login.php';
$logger = new AppLogger('shopee_bulk_terima.log');

try {
    include '../../../aa_kon_sett.php';
} catch (Throwable $t) {
    $logger->critical("Gagal memuat file koneksi: " . $t->getMessage());
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
    if (!is_object($decoded) || !isset($decoded->kode)) {
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


$data = json_decode(file_get_contents('php://input'), true);
if ($data === null) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid JSON data.']);
    exit();
}
$conn->begin_transaction();
try {
    $kd_store = $data['kd_store'] ?? '';
    $no_lpb = $data['no_lpb'] ?? '';
    
    
    $items = $data['items'] ?? [];

    
    if (empty($kd_store) || empty($no_lpb) || empty($items)) {
        throw new Exception("Validasi gagal: kd_store, no_lpb, dan items wajib diisi.");
    }

    $kode_kasir = (int)$user_kode;

    
    $prefix = $kd_store . "-RC-" . $kode_kasir . "-";
    $sql_get_last_num = "SELECT MAX(CAST(SUBSTRING_INDEX(no_faktur, '-', -1) AS UNSIGNED)) as last_num 
                         FROM s_receipt 
                         WHERE no_faktur LIKE ? 
                         FOR UPDATE";
    $stmt_num = $conn->prepare($sql_get_last_num);
    $like_prefix = $prefix . '%';
    $stmt_num->bind_param("s", $like_prefix);
    $stmt_num->execute();
    $result = $stmt_num->get_result();
    $row = $result->fetch_assoc();
    $stmt_num->close();

    $last_num = $row['last_num'] ?? 0;
    $next_num = (int)$last_num + 1;
    $padded_num = str_pad($next_num, 5, '0', STR_PAD_LEFT);
    $no_faktur = $prefix . $padded_num; 


    
    $sql_receipt = "INSERT INTO s_receipt 
                        (kd_store, no_faktur, plu, barcode, descp, 
                         avg_cost, hrg_beli, ppn, netto, admin_s, 
                         ongkir, promo, biaya_psn, price, net_price, 
                         QTY_REC, tgl_pesan, no_lpb, kode_kasir, kode_supp, 
                         jam) 
                    VALUES 
                        (?, ?, ?, ?, ?, 
                         ?, ?, ?, ?, ?, 
                         ?, ?, ?, ?, ?, 
                         ?, NOW(), ?, ?, ?, 
                         NOW())";
    $stmt_receipt = $conn->prepare($sql_receipt);
    if ($stmt_receipt === false) {
        throw new Exception("Database prepare failed (s_receipt): " . $conn->error);
    }

    
    $sql_stok_update = "UPDATE s_stok_ol 
                        SET Qty = Qty + ?, Tgl_Update = NOW() 
                        WHERE KD_STORE = ? AND plu = ?";
    $stmt_stok = $conn->prepare($sql_stok_update);
    if ($stmt_stok === false) {
        throw new Exception("Database prepare failed (s_stok_ol update): " . $conn->error);
    }

    $total_items_processed = 0;

    
    foreach ($items as $item) {
        
        $kode_supp = $item['kode_supp'] ?? '';
        if (empty($kode_supp)) {
            
            continue; 
        }

        $plu = $item['plu'];
        $barcode = $item['barcode']; 
        $descp = $item['descp'];
        $avg_cost = (float)($item['avg_cost'] ?? 0);
        $hrg_beli = (float)($item['hrg_beli'] ?? 0);
        $ppn = (float)($item['ppn'] ?? 0);
        $netto = (float)($item['netto'] ?? 0);
        $admin_s = (float)($item['admin_s'] ?? 0);
        $ongkir = (float)($item['ongkir'] ?? 0);
        $promo = (float)($item['promo'] ?? 0);
        $biaya_psn = (float)($item['biaya_psn'] ?? 0);
        $price = (float)($item['price'] ?? 0);
        $net_price = (float)($item['net_price'] ?? $price); 
        $qty_rec = (float)($item['qty_rec'] ?? 0);

        if ($qty_rec <= 0) {
            continue; 
        }

        
        
        $stmt_receipt->bind_param(
            "sssssdddddddddddsss",
            $kd_store,
            $no_faktur,
            $plu,
            $barcode,
            $descp,
            $avg_cost,
            $hrg_beli,
            $ppn,
            $netto,
            $admin_s,
            $ongkir,
            $promo,
            $biaya_psn,
            $price,
            $net_price,
            $qty_rec,
            $no_lpb,
            $kode_kasir,
            $kode_supp 
        );
        if (!$stmt_receipt->execute()) {
            throw new Exception("Gagal eksekusi query (s_receipt) untuk PLU $plu: " . $stmt_receipt->error);
        }

        
        $stmt_stok->bind_param("dss", $qty_rec, $kd_store, $plu);
        if (!$stmt_stok->execute()) {
            throw new Exception("Gagal eksekusi query (s_stok_ol update) untuk PLU $plu: " . $stmt_stok->error);
        }

        $total_items_processed++;
    }

    $stmt_receipt->close();
    $stmt_stok->close();

    if ($total_items_processed == 0) {
        throw new Exception("Tidak ada item yang diproses (QTY Terima mungkin 0 atau vendor tidak ada).");
    }

    
    $conn->commit();
    echo json_encode([
        'success' => true,
        'message' => "Berhasil! $total_items_processed item telah dicatat dengan No. Faktur: $no_faktur"
    ]);
} catch (Throwable $t) {
    $conn->rollback();
    $logger->critical("🔥 FATAL ERROR (TRANSACTION ROLLED BACK): " . $t->getMessage(), [
        'file' => $t->getFile(),
        'line' => $t->getLine()
    ]);
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => "Terjadi kesalahan: " . $t->getMessage()
    ]);
}

$conn->close();
?>