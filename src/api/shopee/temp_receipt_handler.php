<?php
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


if (!isset($conn) || !$conn instanceof mysqli) {
    $logger->critical("Objek koneksi database (\$conn) tidak ada.");
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Koneksi database tidak terinisialisasi.']);
    exit();
}


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

function get_item_calculation_data($logger, $conn, $plu, $kd_store, $hrg_beli, $price, $qty_terima) {
    
    $logger->info(" Kalkulasi dimulai | PLU: {$plu} | KD_Store: {$kd_store} | Hrg Beli: {$hrg_beli} | Price: {$price} | Qty Terima: {$qty_terima}");

    $hrg_beli = (float)$hrg_beli;
    $price = (float)$price;
    $qty_terima = (float)$qty_terima;

    
    $stmt_kat = $conn->prepare("SELECT admin, x_ongkir, x_ongkir_max, x_promo, x_promo_max, biaya_pesanan FROM s_kategori WHERE KD_STORE = ? AND plu = ?");
    $stmt_kat->bind_param("ss", $kd_store, $plu);
    $stmt_kat->execute();
    $kat_result = $stmt_kat->get_result();
    $kategori_data = $kat_result->fetch_assoc();
    $stmt_kat->close();

    
    $admin_pct = (float)($kategori_data['admin'] ?? 0);
    $ongkir_pct = (float)($kategori_data['x_ongkir'] ?? 0);
    $ongkir_max = (float)($kategori_data['x_ongkir_max'] ?? 0);
    $promo_pct = (float)($kategori_data['x_promo'] ?? 0);
    $promo_max = (float)($kategori_data['x_promo_max'] ?? 0);
    $biaya_pesanan_cost = (float)($kategori_data['biaya_pesanan'] ?? 0);
    $logger->info(" Data Kategori | Admin: {$admin_pct}% | Ongkir: {$ongkir_pct}% (Max: {$ongkir_max}) | Promo: {$promo_pct}% (Max: {$promo_max}) | Biaya Psn: {$biaya_pesanan_cost}");

    
    $stmt_stok = $conn->prepare("SELECT Qty, avg_cost FROM s_stok_ol WHERE KD_STORE = ? AND plu = ?");
    $stmt_stok->bind_param("ss", $kd_store, $plu);
    $stmt_stok->execute();
    $stok_result = $stmt_stok->get_result();
    $stok_data = $stok_result->fetch_assoc();
    $stmt_stok->close();

    $stok_qty = (float)($stok_data['Qty'] ?? 0);
    $stok_avg_cost = (float)($stok_data['avg_cost'] ?? 0);
    $logger->info(" Data Stok Online | Stok Qty: {$stok_qty} | Stok Avg Cost: {$stok_avg_cost}");

    
    $ppn = $hrg_beli * 0.11;
    $netto = $hrg_beli + $ppn;
    $logger->info(" Perhitungan PPN & Netto | PPN: {$ppn} ({$hrg_beli} * 0.11) | Netto: {$netto} ({$hrg_beli} + {$ppn})");

    $admin_cost = ($netto * $admin_pct) / 100;
    $logger->info(" Perhitungan Admin Cost | Hasil: {$admin_cost} | Rumus: ({$netto} * {$admin_pct}) / 100");

    $ongkir_cost_raw = ($netto * $ongkir_pct) / 100;
    $ongkir_cost = ($ongkir_max > 0 && $ongkir_cost_raw > $ongkir_max) ? $ongkir_max : $ongkir_cost_raw;
    $logger->info(" Perhitungan Ongkir Cost | {$ongkir_cost_raw} | Max: {$ongkir_max} | Final: {$ongkir_cost} | Rumus: ({$netto} * {$ongkir_pct}) / 100");

    $promo_cost_raw = ($netto * $promo_pct) / 100;
    $promo_cost = ($promo_max > 0 && $promo_cost_raw > $promo_max) ? $promo_max : $promo_cost_raw;
    $logger->info(" Perhitungan Promo Cost | {$promo_cost_raw} | Max: {$promo_max} | Final: {$promo_cost} | Rumus: ({$netto} * {$promo_pct}) / 100");

    
    $hb_plus_lainnya = $netto + $admin_cost + $ongkir_cost + $promo_cost + $biaya_pesanan_cost;
    $logger->info(" Perhitungan HB+Biaya Lainnya | Hasil: {$hb_plus_lainnya} | Rumus: {$netto} + {$admin_cost} + {$ongkir_cost} + {$promo_cost} + {$biaya_pesanan_cost}");

    $admin_cost_margin = ($price * $admin_pct) / 100;
    $ongkir_cost_margin_raw = ($price * $ongkir_pct) / 100;
    $ongkir_cost_margin = ($ongkir_max > 0 && $ongkir_cost_margin_raw > $ongkir_max) ? $ongkir_max : $ongkir_cost_margin_raw;
    $promo_cost_margin_raw = ($price * $promo_pct) / 100;
    $promo_cost_margin = ($promo_max > 0 && $promo_cost_margin_raw > $promo_max) ? $promo_max : $promo_cost_margin_raw;


    $margin = $price - ($netto + $admin_cost_margin + $ongkir_cost_margin + $promo_cost_margin + $biaya_pesanan_cost);
    $logger->info(" Perhitungan Margin | Hasil: {$margin} | Rumus: {$price} - {$admin_cost} + {$ongkir_cost} + {$promo_cost} + {$netto} + {$biaya_pesanan_cost}");

    
    $total_qty = $stok_qty + $qty_terima;
    $weighted_avg_cost = 0;
    $avg_cost_rumus = 'N/A';
    if ($stok_qty == 0 && $qty_terima > 0) {
        
        $weighted_avg_cost = $hrg_beli;
        $avg_cost_rumus = "Stok awal 0, avg_cost = hrg_beli ({$hrg_beli})";
        $logger->info(" Perhitungan Avg Cost (Stok 0) | Hasil: {$weighted_avg_cost} | Keterangan: {$avg_cost_rumus}");
    } else if ($total_qty > 0) {
        $weighted_avg_cost = (($stok_qty * $stok_avg_cost) + ($qty_terima * $hrg_beli)) / $total_qty;
        $avg_cost_rumus = "(({$stok_qty} * {$stok_avg_cost}) + ({$qty_terima} * {$hrg_beli})) / ({$total_qty})";
        $logger->info(" Perhitungan Avg Cost (Weighted) | Hasil: {$weighted_avg_cost} | Rumus: {$avg_cost_rumus}");
    } else {
        
        $weighted_avg_cost = ($qty_terima > 0) ? $hrg_beli : 0; 
        $avg_cost_rumus = "Total Qty = 0, fallback avg_cost = " . $weighted_avg_cost;
        $logger->warning(" Perhitungan Avg Cost (Total Qty 0) | Hasil: {$weighted_avg_cost} | Keterangan: {$avg_cost_rumus}");
    }

    $result_array = [
        'ppn' => $ppn,
        'netto' => $netto,
        'admin_cost' => $admin_cost,
        'ongkir_cost' => $ongkir_cost,
        'promo_cost' => $promo_cost,
        'biaya_pesanan_cost' => $biaya_pesanan_cost,
        'calc_hb_plus_lainnya' => $hb_plus_lainnya,
        'calc_margin' => $margin,
        'calc_weighted_avg_cost' => $weighted_avg_cost,
        'kategori_data' => [
            'admin_pct' => $admin_pct,
            'ongkir_pct' => $ongkir_pct,
            'ongkir_max' => $ongkir_max,
            'promo_pct' => $promo_pct,
            'promo_max' => $promo_max,
            'biaya_pesanan' => $biaya_pesanan_cost,
        ]
    ];

    return $result_array;
}
$action = $_GET['action'] ?? '';
$data = json_decode(file_get_contents('php://input'), true);
$kode_kasir_int = (int)$user_kode;
$kd_store = '9998';
try {
    switch ($action) {
        case 'get':
            
            $stmt = $conn->prepare("
                SELECT 
                    t.*, 
                    s.ITEM_N, 
                    s.Qty as stok_qty, 
                    s.avg_cost as stok_avg_cost,
                    k.admin as kategori_admin_pct,
                    k.x_ongkir as kategori_ongkir_pct,
                    k.x_ongkir_max as kategori_ongkir_max,
                    k.x_promo as kategori_promo_pct,
                    k.x_promo_max as kategori_promo_max,
                    k.biaya_pesanan as kategori_biaya_pesanan
                FROM s_receipt_temp t
                LEFT JOIN s_stok_ol s ON t.plu = s.plu AND t.kd_store = s.KD_STORE
                LEFT JOIN s_kategori k ON t.plu = k.plu AND t.kd_store = k.KD_STORE
                WHERE t.kd_store = ?
                ORDER BY t.descp ASC
            ");
            $stmt->bind_param("s", $kd_store); 
            $stmt->execute();
            $result = $stmt->get_result();
            $items = [];
            
            while ($item = $result->fetch_assoc()) {
                
                $calcs = get_item_calculation_data(
                    $logger, 
                    $conn,
                    $item['plu'],
                    $kd_store,
                    $item['hrg_beli'],
                    $item['price'],
                    $item['QTY_REC']
                );
                
                
                $item['ppn'] = $calcs['ppn'];
                $item['netto'] = $calcs['netto'];
                $item['admin_s'] = $calcs['admin_cost']; 
                $item['ongkir'] = $calcs['ongkir_cost']; 
                $item['promo'] = $calcs['promo_cost']; 
                $item['biaya_psn'] = $calcs['biaya_pesanan_cost']; 
                
                
                $item['calc_hb_plus_lainnya'] = $calcs['calc_hb_plus_lainnya'];
                $item['calc_margin'] = $calcs['calc_margin'];
                $item['calc_weighted_avg_cost'] = $calcs['calc_weighted_avg_cost'];

                
                $item['kategori_admin_pct'] = $calcs['kategori_data']['admin_pct'];
                $item['kategori_ongkir_pct'] = $calcs['kategori_data']['ongkir_pct'];
                $item['kategori_promo_pct'] = $calcs['kategori_data']['promo_pct'];
                $item['kategori_biaya_pesanan'] = $calcs['kategori_data']['biaya_pesanan'];
                
                
                
                $item['avg_cost'] = $calcs['calc_weighted_avg_cost'];
                $item['net_price'] = $calcs['calc_hb_plus_lainnya'];
                
                $items[] = $item;
            }
            $stmt->close();
            
            echo json_encode(['success' => true, 'items' => $items]);
            break;

        case 'add_by_plu':
        case 'add':
            if ($action === 'add') {
                $item_data = $data['item'];
                $plu = $item_data['plu'] ?? '';
                $vendor = $item_data['VENDOR'] ?? '';
                $descp_fallback = $item_data['DESCP'] ?? 'N/A';
            } else { 
                $plu = $data['plu'] ?? '';
                $vendor = $data['vendor'] ?? '';
                $descp_fallback = 'N/A';
                if (empty($plu) || empty($vendor) || $vendor === 'ALL') {
                    throw new Exception("PLU dan Vendor spesifik wajib diisi.");
                }
            }

            if (empty($plu) || empty($vendor)) {
                throw new Exception("Data item tidak lengkap (PLU atau VENDOR kosong).");
            }
            
            
            $stmt_check = $conn->prepare("SELECT plu FROM s_receipt_temp WHERE kd_store = ? AND kode_kasir = ? AND plu = ?");
            $stmt_check->bind_param("sis", $kd_store, $kode_kasir_int, $plu);
            $stmt_check->execute();
            if ($stmt_check->get_result()->num_rows > 0) {
                throw new Exception("Item (PLU: {$plu}) sudah ada di keranjang.");
            }
            $stmt_check->close();
            
            
            $stmt_find = $conn->prepare("SELECT ITEM_N, DESCP, hrg_beli, price, VENDOR FROM s_stok_ol WHERE KD_STORE = ? AND plu = ? AND VENDOR = ?");
            $stmt_find->bind_param("sss", $kd_store, $plu, $vendor);
            $stmt_find->execute();
            $result_find = $stmt_find->get_result();
            if ($result_find->num_rows === 0) {
                throw new Exception("Produk dengan PLU '$plu' dan Vendor '$vendor' tidak ditemukan di stok.");
            }
            $item_db = $result_find->fetch_assoc();
            $stmt_find->close();

            $plu_val = $item_db['plu'] ?? $plu;
            $item_n_val = $item_db['ITEM_N'] ?? $plu;
            $descp_val = $item_db['DESCP'] ?? $descp_fallback;
            $hrg_beli_val = (float)($item_db['hrg_beli'] ?? 0);
            $price_val = (float)($item_db['price'] ?? 0);
            $vendor_val = $item_db['VENDOR'] ?? $vendor;
            $qty_rec_val = 1;

            
            $calcs = get_item_calculation_data($logger, $conn, $plu_val, $kd_store, $hrg_beli_val, $price_val, $qty_rec_val);

            
            
            $stmt_insert = $conn->prepare(
                "INSERT INTO s_receipt_temp 
                (kd_store, no_faktur, plu, barcode, descp, 
                avg_cost, hrg_beli, ppn, netto, admin_s, 
                ongkir, promo, biaya_psn, price, net_price, 
                QTY_REC, tgl_pesan, no_lpb, kode_kasir, kode_supp, jam) 
                VALUES (?, '', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), '', ?, ?, NOW())"
            );
            
            $stmt_insert->bind_param("ssssddddddddddiis", 
                $kd_store,                       
                $plu_val,                        
                $item_n_val,                     
                $descp_val,                      
                $calcs['calc_weighted_avg_cost'], 
                $hrg_beli_val,                   
                $calcs['ppn'],                   
                $calcs['netto'],                 
                $calcs['admin_cost'],            
                $calcs['ongkir_cost'],           
                $calcs['promo_cost'],            
                $calcs['biaya_pesanan_cost'],    
                $price_val,                      
                $calcs['calc_hb_plus_lainnya'],  
                $qty_rec_val,                    
                $kode_kasir_int,                 
                $vendor_val                      
            );
            
            $stmt_insert->execute();
            $stmt_insert->close();
            
            echo json_encode(['success' => true, 'message' => "Item '{$descp_val}' berhasil ditambahkan."]);
            break;

        case 'update':
            $plu = $data['plu'] ?? '';
            $qty_rec = (float)($data['qty_rec'] ?? 0);
            $hrg_beli = (float)($data['hrg_beli'] ?? 0);
            $price = (float)($data['price'] ?? 0);

            if (empty($plu)) {
                throw new Exception("PLU tidak ditemukan untuk update.");
            }

            
            $calcs = get_item_calculation_data($logger, $conn, $plu, $kd_store, $hrg_beli, $price, $qty_rec);
            
            
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
                WHERE kd_store = ? AND plu = ?");
            
            $stmt->bind_param("dddddddddddss", 
                $qty_rec,
                $hrg_beli,
                $price,
                $calcs['ppn'],
                $calcs['netto'],
                $calcs['admin_cost'],
                $calcs['ongkir_cost'],
                $calcs['promo_cost'],
                $calcs['biaya_pesanan_cost'],
                $calcs['calc_weighted_avg_cost'], 
                $calcs['calc_hb_plus_lainnya'],   
                $kd_store, 
                $plu
            );
            
            $stmt->execute();
            $stmt->close();

            
            $item_response = $calcs;
            $item_response['plu'] = $plu;
            $item_response['QTY_REC'] = $qty_rec;
            $item_response['hrg_beli'] = $hrg_beli;
            $item_response['price'] = $price;
            
            $item_response['kategori_admin_pct'] = $calcs['kategori_data']['admin_pct'];
            $item_response['kategori_ongkir_pct'] = $calcs['kategori_data']['ongkir_pct'];
            $item_response['kategori_promo_pct'] = $calcs['kategori_data']['promo_pct'];
            $item_response['kategori_biaya_pesanan'] = $calcs['kategori_data']['biaya_pesanan'];

            echo json_encode(['success' => true, 'item' => $item_response]);
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
                    throw new Exception("Tidak ada item di Keranjang dengan Quantity lebih dari 0.");
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
                $last_num = (int)$stmt_num->get_result()->fetch_assoc()['last_num'];
                $stmt_num->close();
                $next_num = $last_num + 1;
                $padded_num = str_pad($next_num, 5, '0', STR_PAD_LEFT);
                $no_faktur = $prefix . $padded_num;
                
                // $logger->info("Memulai batch insert. No Faktur: $no_faktur, No LPB: $no_lpb");

                
                $sql_receipt = "INSERT INTO s_receipt 
                    (kd_store, no_faktur, plu, barcode, descp, 
                    avg_cost, hrg_beli, ppn, netto, admin_s, 
                    ongkir, promo, biaya_psn, price, net_price, 
                    QTY_REC, tgl_pesan, no_lpb, kode_kasir, kode_supp, jam) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?, ?, NOW())";
                $stmt_receipt = $conn->prepare($sql_receipt);
                
                $sql_stok_select = "SELECT Qty, avg_cost FROM s_stok_ol WHERE KD_STORE = ? AND plu = ? FOR UPDATE";
                $stmt_stok_select = $conn->prepare($sql_stok_select);
                
                $sql_stok_update = "UPDATE s_stok_ol SET Qty = ?, avg_cost = ?, Tgl_Update = NOW() WHERE KD_STORE = ? AND plu = ?";
                $stmt_stok_update = $conn->prepare($sql_stok_update);
                
                $total_items_processed = 0;

                
                foreach ($temp_items as $item) {
                    $qty_rec = (float)$item['QTY_REC'];
                    if ($qty_rec <= 0) continue;
                    
                    $plu = $item['plu'];
                    $hrg_beli_rec = (float)$item['hrg_beli'];

                    
                    $stmt_receipt->bind_param(
                        "sssssdddddddddddsss",
                        $item['kd_store'], $no_faktur, $item['plu'], $item['barcode'], $item['descp'],
                        $item['avg_cost'], $item['hrg_beli'], $item['ppn'], $item['netto'], $item['admin_s'],
                        $item['ongkir'], $item['promo'], $item['biaya_psn'], $item['price'], $item['net_price'],
                        $qty_rec, 
                        $no_lpb, $item['kode_kasir'], $item['kode_supp']
                    );
                    if (!$stmt_receipt->execute()) {
                        throw new Exception("Gagal insert s_receipt PLU {$plu}: " . $stmt_receipt->error);
                    }
                    
                    
                    $stmt_stok_select->bind_param("ss", $kd_store, $plu);
                    $stmt_stok_select->execute();
                    $stok_data = $stmt_stok_select->get_result()->fetch_assoc();
                    
                    $stok_qty_awal = (float)($stok_data['Qty'] ?? 0);
                    $stok_avg_cost_awal = (float)($stok_data['avg_cost'] ?? 0);
                    
                    $new_stok_qty = $stok_qty_awal + $qty_rec;
                    
                    
                    $new_stok_avg_cost = 0;
                    if ($stok_qty_awal == 0) {
                        
                        $new_stok_avg_cost = $hrg_beli_rec;
                    } else if ($new_stok_qty > 0) {
                        $total_cost_awal = $stok_qty_awal * $stok_avg_cost_awal;
                        $total_cost_baru = $qty_rec * $hrg_beli_rec;
                        $new_stok_avg_cost = ($total_cost_awal + $total_cost_baru) / $new_stok_qty;
                    } else {
                        $new_stok_avg_cost = $stok_avg_cost_awal; 
                    }

                    $stmt_stok_update->bind_param("ddss", $new_stok_qty, $new_stok_avg_cost, $kd_store, $plu);
                    if (!$stmt_stok_update->execute()) {
                        throw new Exception("Gagal update s_stok_ol PLU {$plu}: " . $stmt_stok_update->error);
                    }
                    
                    $total_items_processed++;
                }

                $stmt_receipt->close();
                $stmt_stok_select->close();
                $stmt_stok_update->close();

                
                $stmt_delete = $conn->prepare("DELETE FROM s_receipt_temp WHERE kd_store = ? AND kode_kasir = ?");
                $stmt_delete->bind_param("si", $kd_store, $kode_kasir_int);
                $stmt_delete->execute();
                $stmt_delete->close();
                
                
                $conn->commit();
                
                // $logger->success("Transaksi berhasil. No Faktur: $no_faktur. Total $total_items_processed item.");
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