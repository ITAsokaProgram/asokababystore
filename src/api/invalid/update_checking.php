<?php

require_once __DIR__ . "/../../../aa_kon_sett.php";
require_once __DIR__ . "/../../auth/middleware_login.php";
header("Content-Type:application/json");
header("Access-Control-Allow-Methods: POST");




$headers = getallheaders();
if(!isset($headers['Authorization'])){
    http_response_code(401);
    echo json_encode(['status' => "Unauthenticated", 'message' => 'Request ditolak, token tidak ditemukan']);
    exit;
}
$authHeader = $headers['Authorization'];
$token = null;
if (preg_match('/^Bearer\s(\S+)$/', $authHeader, $matches)) {
    $token = $matches[1]; 
}


$inputJSON = json_decode(file_get_contents('php://input'), true);
$verif = verify_token($token);
$ket_cek = $inputJSON['ket'];
$plu = $inputJSON['plu'];
$kode_toko = $inputJSON['kd_store'];
$kode_kasir = $inputJSON['kasir'];
$tgl_trans = $inputJSON['tgl'];
$jam_trans = $inputJSON['jam'];
$nama = $inputJSON['nama'];
$sql = "UPDATE invtrans SET ket_cek =? , nama_cek = ?  WHERE kode_toko = ? AND plu = ? AND kode_kasir = ? AND tgl_trans = ? AND jam_trs = ?";

$stmt = $conn->prepare($sql);
error_log("BIND VALUES: " . json_encode([
  $ket_cek, $nama, $kode_toko, $plu, $kode_kasir, $tgl_trans, $jam_trans
]));
$stmt->bind_param("sssssss", $ket_cek, $nama, $kode_toko, $plu, $kode_kasir, $tgl_trans, $jam_trans);
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Server Error']);
    exit;
}
$stmt->execute();
if ($stmt->affected_rows === 0) {
    http_response_code(200);
    echo json_encode([
        'status' => 'warning',
        'message' => 'Query sukses tapi tidak ada data yang diubah. Periksa kondisi WHERE.'
    ]);
    exit;
}
http_response_code(200);
echo json_encode(['status' => 'success', 'message' => 'Berhasil update keterangan']);

$stmt->close();
$conn->close();
