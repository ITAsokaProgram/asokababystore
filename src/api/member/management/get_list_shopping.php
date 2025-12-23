<?php

header("Content-Type:application/json");
require_once __DIR__ . "/../../../../aa_kon_sett.php";
require_once __DIR__ . "/../../../auth/middleware_login.php";

$token = $_COOKIE['admin_token'];
if (!$token) {
    http_response_code(401);
    echo json_encode(['status' => false, "message" => "Unauthorize user cannot access"]);
    exit;
}

try {
    $kd_cust = $_GET['kd_cust'] ?? "";
    $no_bon = $_GET['no_bon'] ?? "";
    $sql = "SELECT 
    t.kd_store,
    c.kd_cust AS member, 
    c.nama_cust AS pelanggan, 
    t.no_bon AS kode_transaksi, 
    DATE_FORMAT(t.tgl_trans, '%d-%m-%Y') AS tanggal,
    t.jam_trs,
    t.descp AS item,
    t.qty,
    t.harga,
    t.diskon,
    tp.diskon as total_diskon,
    t.hrg_promo,
    t.nama_kasir AS kasir,
    tp.belanja,
    tp.bayar,
    tp.cash,
    tp.nm_kartu,
    tp.kembalian,
    tp.voucher1,
    tp.no_voucher1,
    tp.credit1,
    tp.no_kredit1,
    ks.Alm_toko AS alamat_store,
    ks.Nm_Store AS nama_store
FROM customers c 
LEFT JOIN trans_b t ON c.kd_cust = t.kd_cust
LEFT JOIN kode_store ks ON t.kd_store = ks.kd_store
LEFT JOIN pembayaran_b tp ON t.kd_cust = tp.kd_cust AND t.no_bon = tp.no_faktur
WHERE c.kd_cust = ? 
  AND t.no_bon = ?
ORDER BY t.jam_trs, t.no_bon, t.descp";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $kd_cust, $no_bon);
    if (!$stmt->execute()) {
        http_response_code(500);
        echo json_encode(['status' => false, "message" => $stmt->error_log()]);
        exit;
    }
    $result = $stmt->get_result();
    $transaction = $result->fetch_all(MYSQLI_ASSOC);
    http_response_code(200);
    echo json_encode([
        "status" => true,
        "message" => "Success get list",
        "transaction" => $transaction ?? []
    ]);
    $stmt->close();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Error Processing Request",
        "error"   => $e->getMessage()
    ]);
    exit;
} finally {
    $conn->close();
    exit;
}
