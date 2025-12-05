<?php

require_once __DIR__ . "/../../../aa_kon_sett.php";
require_once __DIR__ . "/../../auth/middleware_login.php";
header("Content-Type:application/json");
header("Access-Control-Allow-Methods: POST");

$headers = getallheaders();
if (!isset($headers['Authorization'])) {
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

// Modifikasi: Support Single update dan Bulk Update
// Jika ada key 'items', berarti bulk update. Jika tidak, bungkus single data jadi array.
$items = isset($inputJSON['items']) ? $inputJSON['items'] : [$inputJSON];

$ket_cek = $inputJSON['ket'];
// Nama user bisa diambil dari payload luar atau item pertama (tergantung implementasi JS)
$nama = $inputJSON['nama'] ?? 'User';

if (empty($items)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Tidak ada data yang dikirim']);
    exit;
}

$successCount = 0;
$stmt = $conn->prepare("UPDATE invtrans SET ket_cek = ?, nama_cek = ? WHERE kode_toko = ? AND plu = ? AND kode_kasir = ? AND tgl_trans = ? AND jam_trs = ?");

foreach ($items as $item) {
    // Normalisasi variable dari item array
    $plu = $item['plu'];
    // Handle kemungkinan perbedaan nama key (kd_store vs cabang)
    $kode_toko = $item['kd_store'] ?? $item['cabang'] ?? '';
    $kode_kasir = $item['kasir'];
    $tgl_trans = $item['tgl'];
    $jam_trans = $item['jam'];

    // Bind parameter dalam loop
    $stmt->bind_param("sssssss", $ket_cek, $nama, $kode_toko, $plu, $kode_kasir, $tgl_trans, $jam_trans);

    if ($stmt->execute()) {
        $successCount++;
    }
}

$stmt->close();
$conn->close();

if ($successCount > 0) {
    http_response_code(200);
    echo json_encode(['status' => 'success', 'message' => "Berhasil mengupdate $successCount data"]);
} else {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Gagal mengupdate data']);
}
?>