<?php
require_once __DIR__ . ("/../../../aa_kon_sett.php");
require_once __DIR__ . ("/../../auth/middleware_login.php");
header('Content-Type: application/json');
$verify = authenticate_request();

$kd_tr = $_GET['kode'] ?? '';
if (empty($kd_tr)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Kode transaksi tidak boleh kosong']);
    exit;
}
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
    ks.nama_logo AS logo,
    ks.Nm_Store AS nama_store
FROM trans_b t
LEFT JOIN customers c ON c.kd_cust = t.kd_cust 
LEFT JOIN kode_store ks ON t.kd_store = ks.kd_store
LEFT JOIN pembayaran_b tp ON t.kd_cust = tp.kd_cust AND t.no_bon = tp.no_faktur
WHERE t.no_bon = ? 
ORDER BY t.jam_trs, t.no_bon, t.descp";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database Error']);
    exit;
}
$stmt->bind_param("s", $kd_tr);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_all(MYSQLI_ASSOC);
http_response_code(200);
echo json_encode([
    'detail_transaction' => $data
]);
$stmt->close();
$conn->close();
?>