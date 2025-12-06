<?php
require_once __DIR__ . "/../../../aa_kon_sett.php";
require_once __DIR__ . "/../../auth/middleware_login.php";
header("Content-Type:application/json");
header("Access-Control-Allow-Methods: POST");
date_default_timezone_set('Asia/Jakarta');
$headers = getallheaders();
$authHeader = $headers['Authorization'] ?? '';
$token = null;
if (preg_match('/^Bearer\s(\S+)$/', $authHeader, $matches)) {
    $token = $matches[1];
}
if (!$token) {
    http_response_code(401);
    echo json_encode(['status' => "Unauthenticated", 'message' => 'Request ditolak user tidak terdaftar']);
    exit;
}
$verif = verify_token($token);
$input = json_decode(file_get_contents("php://input"), true);
$items = isset($input['items']) ? $input['items'] : [];
if (empty($items) && isset($input['plu'])) {
    $items[] = $input;
}
$ket_global = $input['keterangan'] ?? ($input['ket'] ?? '-');
$nama_user = $input['nama'] ?? 'User';
$tanggal = date('Y-m-d H:i:s');
if (empty($items)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Tidak ada data yang dikirim']);
    exit;
}
$sql = "INSERT INTO margin
(plu, no_bon, descp, qty, gross, net, avg_cost, ppn, margin_min, tanggal, kd_store, cabang, ket_cek, nama_cek, status_cek, tanggal_cek)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, ?)
ON DUPLICATE KEY UPDATE 
    ket_cek = VALUES(ket_cek),
    nama_cek = VALUES(nama_cek),
    status_cek = 1,
    tanggal_cek = VALUES(tanggal_cek)";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Server Error: ' . $conn->error]);
    exit;
}
$successCount = 0;
foreach ($items as $item) {
    $plu = $item['plu'];
    $bon = $item['bon'] ?? $item['no_bon'];
    $barang = $item['barang'] ?? $item['descp'];
    $qty = $item['qty'];
    $gros = $item['gros'] ?? $item['gross'] ?? 0;
    $net = $item['net'] ?? 0;
    $avg = $item['avg'] ?? $item['avg_cost'] ?? 0;
    $ppn = $item['ppn'] ?? 0;
    $margin = $item['margin'] ?? 0;
    $tgl = $item['tgl'] ?? $item['tanggal'];
    $kd_store = $item['kd'] ?? $item['kd_store'];
    $cabang = $item['cabang'];
    $ket = $item['keterangan'] ?? $ket_global;
    $stmt->bind_param(
        "sssidddddssssss",
        $plu,
        $bon,
        $barang,
        $qty,
        $gros,
        $net,
        $avg,
        $ppn,
        $margin,
        $tgl,
        $kd_store,
        $cabang,
        $ket,
        $nama_user,
        $tanggal
    );
    if ($stmt->execute()) {
        $successCount++;
    }
}
$stmt->close();
$conn->close();
if ($successCount > 0) {
    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'message' => "Berhasil update $successCount data"
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'status' => 'warning',
        'message' => 'Gagal update data atau data tidak berubah'
    ]);
}
?>