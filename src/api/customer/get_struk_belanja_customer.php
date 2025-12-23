<?php

include '../../../aa_kon_sett.php';
require_once __DIR__ . '/../../auth/middleware_login.php';
header('Content-Type: application/json');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$kd_tr = $_GET['kode'];
$kd_cust = $_GET['member'];
$token = $_COOKIE['admin_token'];
$verify = verify_token($token);
if(!$token) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Anda belum login silahkan login kembali']);
    exit;
}
if (empty($kd_tr) || empty($kd_cust)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Kode transaksi atau member tidak boleh kosong']);
    exit;
} 
$id_user = $verify->id ?? null;
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
FROM customers c 
LEFT JOIN trans_b t ON c.kd_cust = t.kd_cust
LEFT JOIN kode_store ks ON t.kd_store = ks.kd_store
LEFT JOIN pembayaran_b tp ON t.kd_cust = tp.kd_cust AND t.no_bon = tp.no_faktur
LEFT JOIN user_asoka ua on ua.no_hp = c.kd_cust
WHERE c.kd_cust = ? 
  AND t.no_bon = ?
ORDER BY t.jam_trs, t.no_bon, t.descp";
$stmt = $conn->prepare($sql);
if(!$stmt){
  http_response_code(500);
  echo json_encode(['status'=>'error','message'=>'Gagal Fetch']);
  exit;
}
$stmt->bind_param("ss", $kd_cust,  $kd_tr);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_all(MYSQLI_ASSOC);
http_response_code(200);
echo json_encode([
    'detail_transaction' => $data
]);
$stmt->close();
$conn->close();