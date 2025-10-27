<?php

function calculate_fields($hrg_beli, $price) {
    $hrg_beli = (float)$hrg_beli;
    $price = (float)$price;

    $ppn = $hrg_beli * 0.11; 
    $netto = $hrg_beli + $ppn;
    $admin_s = $hrg_beli * 0.01; 
    $ongkir = $price * 0.12; 
    
    $promo = $hrg_beli * 0.005; 
    $biaya_psn = 150; 
    
    $avg_cost = $netto + $admin_s + $ongkir + $biaya_psn - $promo;
    $net_price = $avg_cost; 
    
    return [
        'ppn' => $ppn,
        'netto' => $netto,
        'admin_s' => $admin_s,
        'ongkir' => $ongkir,
        'promo' => $promo,
        'biaya_psn' => $biaya_psn,
        'avg_cost' => $avg_cost,
        'net_price' => $net_price
    ];
}

session_start();
ini_set('display_errors', 0);
require_once __DIR__ . '/../../utils/Logger.php';
require_once __DIR__ . '/../../auth/middleware_login.php';
$logger = new AppLogger('shopee_temp_receipt.log');
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
    $logger->info("Token valid. User '{$user_kode}' mengakses temp handler.");
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
$action = $_GET['action'] ?? '';
$data = json_decode(file_get_contents('php://input'), true);
$kode_kasir_int = (int)$user_kode;
$kd_store = '9998'; 
try {
    switch ($action) {
        case 'get':
            $stmt = $conn->prepare("SELECT * FROM s_receipt_temp WHERE kd_store = ? AND kode_kasir = ? ORDER BY descp ASC");
            $stmt->bind_param("si", $kd_store, $kode_kasir_int);
            $stmt->execute();
            $result = $stmt->get_result();
            $items = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            echo json_encode(['success' => true, 'items' => $items]);
            break;
        case 'add_by_plu':
            $plu = $data['plu'] ?? '';
            $vendor = $data['vendor'] ?? '';
            if (empty($plu) || empty($vendor)) {
                throw new Exception("PLU dan Vendor wajib diisi.");
            }
            if ($vendor === 'ALL') {
                throw new Exception("Pilih vendor spesifik, bukan 'Semua Vendor'.");
            }
            $stmt_find = $conn->prepare("SELECT * FROM s_stok_ol WHERE KD_STORE = ? AND plu = ? AND VENDOR = ?");
            $stmt_find->bind_param("sss", $kd_store, $plu, $vendor);
            $stmt_find->execute();
            $result_find = $stmt_find->get_result();
            if ($result_find->num_rows === 0) {
                throw new Exception("Produk dengan PLU '$plu' dan Vendor '$vendor' tidak ditemukan di stok.");
            }
            $item = $result_find->fetch_assoc();
            $stmt_find->close();
            $stmt_check = $conn->prepare("SELECT plu FROM s_receipt_temp WHERE kd_store = ? AND kode_kasir = ? AND plu = ?");
            $stmt_check->bind_param("sis", $kd_store, $kode_kasir_int, $item['plu']);
            $stmt_check->execute();
            $result_check = $stmt_check->get_result();
            if ($result_check->num_rows > 0) {
                 throw new Exception("Item '{$item['DESCP']}' (PLU: {$item['plu']}) sudah ada di keranjang.");
            }
            $stmt_check->close();

            
            $plu_val = $item['plu'];
            $descp_val = $item['DESCP'];
            $hrg_beli_val = (float)$item['hrg_beli'];
            $price_val = (float)$item['price'];
            $vendor_val = $item['VENDOR'];

            
            $calcs = calculate_fields($hrg_beli_val, $price_val);

            $stmt_insert = $conn->prepare("INSERT INTO s_receipt_temp 
                (kd_store, no_faktur, plu, barcode, descp, 
                avg_cost, hrg_beli, ppn, netto, admin_s, 
                ongkir, promo, biaya_psn, price, net_price, 
                QTY_REC, tgl_pesan, no_lpb, kode_kasir, kode_supp, jam) 
                VALUES (?, '', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, NOW(), '', ?, ?, NOW())");
            
            $stmt_insert->bind_param("sssssdddddddddis", 
                $kd_store,
                $plu_val,
                $plu_val, 
                $descp_val,
                $calcs['avg_cost'], 
                $hrg_beli_val,
                $calcs['ppn'], 
                $calcs['netto'], 
                $calcs['admin_s'], 
                $calcs['ongkir'], 
                $calcs['promo'], 
                $calcs['biaya_psn'], 
                $price_val,
                $calcs['net_price'], 
                $kode_kasir_int,
                $vendor_val
            );
            

            $stmt_insert->execute();
            $stmt_insert->close();
            echo json_encode(['success' => true, 'message' => "Item '{$item['DESCP']}' berhasil ditambahkan."]);
            break;
        case 'add':
            $item_data_from_client = $data['item'];
            if (empty($item_data_from_client) || empty($item_data_from_client['plu']) || empty($item_data_from_client['VENDOR'])) {
                throw new Exception("Data item tidak lengkap (PLU atau VENDOR kosong).");
            }
            
            $plu_from_client = $item_data_from_client['plu'];
            $vendor_from_client = $item_data_from_client['VENDOR'];
            $descp_from_client = $item_data_from_client['DESCP']; // Ambil deskripsi dari client sebagai fallback

            // 1. Periksa duplikat di keranjang
            $stmt_check = $conn->prepare("SELECT plu FROM s_receipt_temp WHERE kd_store = ? AND kode_kasir = ? AND plu = ?");
            $stmt_check->bind_param("sis", $kd_store, $kode_kasir_int, $plu_from_client);
            $stmt_check->execute();
            $result_check = $stmt_check->get_result();
            if ($result_check->num_rows > 0) {
                 throw new Exception("Item '{$descp_from_client}' (PLU: {$plu_from_client}) sudah ada di keranjang.");
            }
            $stmt_check->close();

            $hrg_beli_val = 0.0;
            $price_val = 0.0;
            $descp_val = $descp_from_client;
            $stmt_find = $conn->prepare("SELECT hrg_beli, price, DESCP FROM s_stok_ol WHERE KD_STORE = ? AND plu = ? AND VENDOR = ?");
            $stmt_find->bind_param("sss", $kd_store, $plu_from_client, $vendor_from_client);
            $stmt_find->execute();
            $result_find = $stmt_find->get_result();
            if ($item_db = $result_find->fetch_assoc()) {
                $hrg_beli_val = (float)$item_db['hrg_beli'];
                $price_val = (float)$item_db['price'];     
                $descp_val = $item_db['DESCP'];           
            }
            $stmt_find->close();

            
            $calcs = calculate_fields($hrg_beli_val, $price_val);

            $stmt = $conn->prepare("INSERT INTO s_receipt_temp 
                (kd_store, no_faktur, plu, barcode, descp, 
                avg_cost, hrg_beli, ppn, netto, admin_s, 
                ongkir, promo, biaya_psn, price, net_price, 
                QTY_REC, tgl_pesan, no_lpb, kode_kasir, kode_supp, jam) 
                VALUES (?, '', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, NOW(), '', ?, ?, NOW())");
            
            $stmt->bind_param("sssssdddddddddis", 
                $kd_store,
                $plu_from_client, 
                $plu_from_client, 
                $descp_val,       
                $calcs['avg_cost'], 
                $hrg_beli_val,    
                $calcs['ppn'], 
                $calcs['netto'], 
                $calcs['admin_s'], 
                $calcs['ongkir'], 
                $calcs['promo'], 
                $calcs['biaya_psn'], 
                $price_val,       
                $calcs['net_price'], 
                $kode_kasir_int,
                $vendor_from_client
            );
            
            $stmt->execute();
            $stmt->close();
            echo json_encode(['success' => true, 'message' => "Item '{$descp_val}' berhasil ditambahkan."]);
            break;
        case 'update':
            
            $plu = $data['plu'] ?? '';
            $qty = (float)($data['qty_rec'] ?? 0);
            $hrg_beli = (float)($data['hrg_beli'] ?? 0);
            $price = (float)($data['price'] ?? 0);

            if (empty($plu)) {
                throw new Exception("PLU tidak ditemukan untuk update.");
            }

            
            $calcs = calculate_fields($hrg_beli, $price);

            $stmt = $conn->prepare("UPDATE s_receipt_temp 
                                    SET 
                                        QTY_REC = ?, 
                                        hrg_beli = ?, 
                                        price = ?, 
                                        ppn = ?, 
                                        netto = ?, 
                                        admin_s = ?, 
                                        ongkir = ?, 
                                        promo = ?, 
                                        biaya_psn = ?, 
                                        avg_cost = ?, 
                                        net_price = ? 
                                    WHERE kd_store = ? AND kode_kasir = ? AND plu = ?");
            
            $stmt->bind_param("dddddddddddsis", 
                $qty, 
                $hrg_beli, 
                $price, 
                $calcs['ppn'],
                $calcs['netto'],
                $calcs['admin_s'],
                $calcs['ongkir'],
                $calcs['promo'],
                $calcs['biaya_psn'],
                $calcs['avg_cost'],
                $calcs['net_price'],
                $kd_store, 
                $kode_kasir_int, 
                $plu
            );
            

            $stmt->execute();
            $stmt->close();
            echo json_encode(['success' => true]);
            break;
        case 'delete':
            $plus = $data['plus'] ?? [];
            if (empty($plus)) {
                throw new Exception("Tidak ada item yang dipilih untuk dihapus.");
            }
            $placeholders = implode(',', array_fill(0, count($plus), '?'));
            $types = str_repeat('s', count($plus));
            $sql = "DELETE FROM s_receipt_temp WHERE kd_store = ? AND kode_kasir = ? AND plu IN ($placeholders)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si" . $types, $kd_store, $kode_kasir_int, ...$plus);
            $stmt->execute();
            $affected_rows = $stmt->affected_rows;
            $stmt->close();
            echo json_encode(['success' => true, 'message' => "$affected_rows item berhasil dihapus."]);
            break;
        case 'delete_all':
            $stmt = $conn->prepare("DELETE FROM s_receipt_temp WHERE kd_store = ? AND kode_kasir = ?");
            $stmt->bind_param("si", $kd_store, $kode_kasir_int);
            $stmt->execute();
            $affected_rows = $stmt->affected_rows;
            $stmt->close();
            echo json_encode(['success' => true, 'message' => "Semua ($affected_rows) item di keranjang berhasil dihapus."]);
            break;
        case 'save':
            $no_lpb = $data['no_lpb'] ?? '';
            if (empty($no_lpb)) {
                throw new Exception("Nomor LPB wajib diisi.");
            }
            $conn->begin_transaction();
            try {
                $stmt_get_temp = $conn->prepare("SELECT * FROM s_receipt_temp WHERE kd_store = ? AND kode_kasir = ? AND QTY_REC > 0 FOR UPDATE");
                $stmt_get_temp->bind_param("si", $kd_store, $kode_kasir_int);
                $stmt_get_temp->execute();
                $temp_items_result = $stmt_get_temp->get_result();
                $temp_items = $temp_items_result->fetch_all(MYSQLI_ASSOC);
                $stmt_get_temp->close();
                if (empty($temp_items)) {
                    throw new Exception("Tidak ada item di Keranjang dengan Quantity lebih dari 0 / Keranjang kosong");
                }
                $prefix = $kd_store . "-RC-" . $kode_kasir_int . "-";
                $sql_get_last_num = "SELECT MAX(CAST(SUBSTRING_INDEX(no_faktur, '-', -1) AS UNSIGNED)) as last_num 
                                     FROM s_receipt 
                                     WHERE no_faktur LIKE ? 
                                     FOR UPDATE";
                $stmt_num = $conn->prepare($sql_get_last_num);
                $like_prefix = $prefix . '%';
                $stmt_num->bind_param("s", $like_prefix);
                $stmt_num->execute();
                $result_num = $stmt_num->get_result();
                $row_num = $result_num->fetch_assoc();
                $stmt_num->close();
                $last_num = $row_num['last_num'] ?? 0;
                $next_num = (int)$last_num + 1;
                $padded_num = str_pad($next_num, 5, '0', STR_PAD_LEFT);
                $no_faktur = $prefix . $padded_num;
                $logger->info("Memulai batch insert dari temp ke receipt. No Faktur: $no_faktur, No LPB: $no_lpb");
                $sql_receipt = "INSERT INTO s_receipt 
                                (kd_store, no_faktur, plu, barcode, descp, 
                                 avg_cost, hrg_beli, ppn, netto, admin_s, 
                                 ongkir, promo, biaya_psn, price, net_price, 
                                 QTY_REC, tgl_pesan, no_lpb, kode_kasir, kode_supp, jam) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?, ?, NOW())";
                $stmt_receipt = $conn->prepare($sql_receipt);
                $sql_stok_update = "UPDATE s_stok_ol SET Qty = Qty + ?, Tgl_Update = NOW() WHERE KD_STORE = ? AND plu = ?";
                $stmt_stok = $conn->prepare($sql_stok_update);
                $total_items_processed = 0;
                foreach ($temp_items as $item) {
                    $qty_rec = (float)$item['QTY_REC'];
                    if ($qty_rec <= 0) continue;
                    $kd_store_val   = $item['kd_store'];
                    $plu_val        = $item['plu'];
                    $barcode_val    = $item['barcode'];
                    $descp_val      = $item['descp'];
                    
                    $avg_cost_val   = (float)$item['avg_cost'];
                    $hrg_beli_val   = (float)$item['hrg_beli']; 
                    $ppn_val        = (float)$item['ppn'];
                    $netto_val      = (float)$item['netto'];
                    $admin_s_val    = (float)$item['admin_s'];
                    $ongkir_val     = (float)$item['ongkir'];
                    $promo_val      = (float)$item['promo'];
                    $biaya_psn_val  = (float)$item['biaya_psn'];
                    $price_val      = (float)$item['price'];
                    $net_price_val  = (float)$item['net_price'];
                    
                    
                    $kode_kasir_val = $item['kode_kasir'];
                    $kode_supp_val  = $item['kode_supp'];

                    
                    $stmt_receipt->bind_param(
                        "sssssdddddddddddsss",
                        $kd_store_val, $no_faktur, $plu_val, $barcode_val, $descp_val,
                        $avg_cost_val, $hrg_beli_val, $ppn_val, $netto_val, $admin_s_val,
                        $ongkir_val, $promo_val, $biaya_psn_val, $price_val, $net_price_val,
                        $qty_rec, 
                        $no_lpb, $kode_kasir_val, $kode_supp_val
                    );
                    if (!$stmt_receipt->execute()) {
                        throw new Exception("Gagal insert s_receipt PLU {$item['plu']}: " . $stmt_receipt->error);
                    }
                    $stmt_stok->bind_param("dss", $qty_rec, $item['kd_store'], $item['plu']);
                    if (!$stmt_stok->execute()) {
                        throw new Exception("Gagal update s_stok_ol PLU {$item['plu']}: " . $stmt_stok->error);
                    }
                    $total_items_processed++;
                }
                $stmt_receipt->close();
                $stmt_stok->close();
                $stmt_delete = $conn->prepare("DELETE FROM s_receipt_temp WHERE kd_store = ? AND kode_kasir = ?");
                $stmt_delete->bind_param("si", $kd_store, $kode_kasir_int);
                $stmt_delete->execute();
                $stmt_delete->close();
                $conn->commit();
                $logger->success("Transaksi berhasil. No Faktur: $no_faktur. Total $total_items_processed item.");
                echo json_encode([
                    'success' => true,
                    'message' => "Berhasil! $total_items_processed item telah dicatat dengan No. Faktur: $no_faktur"
                ]);
            } catch (Throwable $t) {
                $conn->rollback();
                throw $t; 
            }
            break;
        default:
            throw new Exception("Aksi tidak valid.");
    }
} catch (Throwable $t) {
    $logger->critical("🔥 ERROR: ". $t->getMessage(), ['trace' => $t->getTraceAsString()]);
    http_response_code(400); 
    echo json_encode([
        'success' => false,
        'message' => "Terjadi kesalahan: " . $t->getMessage()
    ]);
}
$conn->close();
?>