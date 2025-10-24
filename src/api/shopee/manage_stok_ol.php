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
    $logger->info("Token valid. User '{$user_kode}' mengakses.");
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
    $logger->info("Memulai request manage stok online...", $_POST);
    $mode = $_POST['mode'] ?? 'add';
    $sku = trim($_POST['sku'] ?? ''); 
    $kd_store = trim($_POST['kd_store'] ?? '');
    $plu = trim($_POST['plu'] ?? '');
    $descp = trim($_POST['descp'] ?? '');
    $vendor = trim($_POST['vendor'] ?? '');
    $hrg_beli = (float)($_POST['hrg_beli'] ?? 0);
    $price = (float)($_POST['price'] ?? 0);
    $qty_rec = (float)($_POST['qty_rec'] ?? 0);
    $kode_supp = trim($_POST['kode_supp'] ?? $vendor);
    $avg_cost = (float)($_POST['avg_cost'] ?? 0);
    $ppn_rec = (float)($_POST['ppn'] ?? 0);
    $netto_rec = (float)($_POST['netto'] ?? 0);
    $net_price = (float)($_POST['net_price'] ?? 0);
    $admin_s = (float)($_POST['admin_s'] ?? 0);
    $ongkir = (float)($_POST['ongkir'] ?? 0);
    $promo = (float)($_POST['promo'] ?? 0);
    $biaya_psn = (float)($_POST['biaya_psn'] ?? 0);
    $kode_kasir = $user_kode; 
    $no_lpb = trim($_POST['no_lpb'] ?? ''); 
    if (empty($kd_store) || empty($plu) || empty($sku) || empty($descp)) {
        throw new Exception("Validasi gagal: KD_STORE, PLU, SKU (item_n), dan Deskripsi wajib diisi.");
    }
    $sql_stok_ol = "";
    if ($mode === 'add') {
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
                            Qty = VALUES(Qty), 
                            Tgl_Update = NOW()";
    } else {
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
                            Qty = Qty + VALUES(Qty), 
                            Tgl_Update = NOW()";
    }
    $stmt_stok_ol = $conn->prepare($sql_stok_ol);
    if ($stmt_stok_ol === false) {
        throw new Exception("Database prepare failed (s_stok_ol): ". $conn->error);
    }
    $stmt_stok_ol->bind_param("sssssdddddd", 
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
    $logger->info("STEP 1 (s_stok_ol) berhasil. Mode: $mode. Affected rows: $affected_rows");
    $message = '';
    if ($mode === 'edit' && $qty_rec > 0) {
        if (empty($no_lpb)) {
            throw new Exception("Validasi gagal: No LPB wajib diisi jika QTY Diterima > 0.");
        }
        $prefix = $kd_store . "-RC-" . $kode_kasir . "_";
        $sql_get_last_num = "SELECT MAX(CAST(SUBSTRING_INDEX(no_faktur, '_', -1) AS UNSIGNED)) as last_num 
                             FROM s_receipt 
                             WHERE no_faktur LIKE ? 
                             FOR UPDATE";
        $stmt_num = $conn->prepare($sql_get_last_num);
        if ($stmt_num === false) {
            throw new Exception("Database prepare failed (get_last_num): ". $conn->error);
        }
        $like_prefix = $prefix . '%';
        $stmt_num->bind_param("s", $like_prefix);
        if (!$stmt_num->execute()) {
            throw new Exception("Gagal eksekusi query (get_last_num): ". $stmt_num->error);
        }
        $result = $stmt_num->get_result();
        $row = $result->fetch_assoc();
        $stmt_num->close();
        $last_num = $row['last_num'] ?? 0; 
        $next_num = (int)$last_num + 1;
        $padded_num = str_pad($next_num, 5, '0', STR_PAD_LEFT);
        $no_faktur = $prefix . $padded_num;
        $barcode = $plu; 
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
            throw new Exception("Database prepare failed (s_receipt): ". $conn->error);
        }
        $stmt_receipt->bind_param("sssssdddddddddddsss", 
            $kd_store, $no_faktur, $plu, $barcode, $descp,
            $avg_cost, $hrg_beli, $ppn_rec, $netto_rec, $admin_s,
            $ongkir, $promo, $biaya_psn, $price, $net_price,
            $qty_rec, 
            $no_lpb, $kode_kasir, $kode_supp
        );
        if (!$stmt_receipt->execute()) {
            throw new Exception("Gagal eksekusi query (s_receipt): ". $stmt_receipt->error);
        }
        $stmt_receipt->close();
        $logger->info("STEP 2 (s_receipt) berhasil. No Faktur: {$no_faktur}. QTY {$qty_rec} dicatat oleh kasir {$kode_kasir}.");
        $message = "Data info (s_stok_ol) diupdate DAN {$qty_rec} QTY dicatat di (s_receipt).";
    } else {
        if ($mode === 'add') {
             $logger->info("STEP 2 (s_receipt) dilewati karena mode 'add'.");
             $message = "Produk (SKU: {$sku}) berhasil dimasukkan ke stok online dengan QTY 0.";
        } else {
             if (!empty($no_lpb)) {
                 throw new Exception("Validasi gagal: QTY Diterima tidak boleh 0 jika No LPB diisi.");
             }
             $logger->info("STEP 2 (s_receipt) dilewati karena QTY_REC = 0.");
             $message = "Data info stok online berhasil diupdate. Tidak ada QTY baru yang dicatat.";
        }
    }
    $conn->commit();
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