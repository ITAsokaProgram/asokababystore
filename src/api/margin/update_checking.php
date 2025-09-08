<?php

require_once __DIR__ . "/../../../aa_kon_sett.php";
require_once __DIR__ . "/../../auth/middleware_login.php";
header("Content-Type:application/json");
header("Access-Control-Allow-Methods: POST");
date_default_timezone_set('Asia/Jakarta');
$headers = getallheaders();
$authHeader = $headers['Authorization'];
$token = null;
if (preg_match('/^Bearer\s(\S+)$/', $authHeader, $matches)) {
    $token = $matches[1]; // ini yang aman dan baku
}
if (!$token) {
    http_response_code(401);
    echo json_encode(['status' => "Unauthenticated", 'message' => 'Request ditolak user tidak terdaftar']);
    exit;
}
$verif = verify_token($token);
// Ambil data JSON dari frontend
$input = json_decode(file_get_contents("php://input"), true);

// Ambil masing-masing nilai
$plu     = $input['plu'];
$bon     = $input['bon'];
$barang  = $input['barang'];
$qty     = $input['qty'];
$gros    = $input['gros'];
$net     = $input['net'];
$avg     = $input['avg'];
$ppn     = $input['ppn'];
$margin  = $input['margin'];
$tgl     = $input['tgl'];
$cabang  = $input['cabang'];
$ket = $input['keterangan'];
$nama = $input['nama'];
$kd_store = $input['kd'];
$tanggal = date('Y-m-d H:i:s');
$sql = "INSERT INTO margin
(plu, no_bon, descp, qty, gross, net, avg_cost, ppn, margin_min, tanggal, kd_store,cabang, ket_cek, nama_cek, status_cek, tanggal_cek)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?, 1, ?)";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Server Error']);
    exit;
}

// Bind parameter
$stmt->bind_param(
    "sssidddddssssss", // sesuaikan dengan tipe data (mis. float = d, string = s)
    $plu, $bon, $barang, $qty, $gros, $net, $avg, $ppn, $margin, $tgl, $kd_store, $cabang, $ket, $nama, $tanggal
);

if ($stmt->execute()) {
    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'message' => 'Berhasil update data'
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'status' => 'warning',
        'message' => 'Gagal update silahkan hubungi Team IT PUSAT'
    ]);
    exit;
}

$stmt->close();
$conn->close();
