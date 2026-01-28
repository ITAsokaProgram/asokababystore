<?php

require_once __DIR__ . "/../../../aa_kon_sett.php";
require_once __DIR__ . "/../../auth/middleware_login.php";
header("Content-Type:application/json");
header("Access-Control-Allow-Methods: GET");
$verif = authenticate_request();
$kd_store = $verif->kd_store;
$sql = "SELECT 
    inv.nama_kasir AS kasir,
    inv.kode_kasir AS kode,
    inv.`type` AS kategori,
    MAX(inv.tgl_trans) AS tgl,
    inv.keterangan as ket,
    s.`Nm_Alias` AS cabang
FROM invtrans inv
LEFT JOIN kode_store s ON s.Kd_Store = inv.kode_toko";

if ($kd_store != "Pusat") {
    $storeArray = explode(',', $kd_store);
    $placeholders = implode("','", array_map('trim', $storeArray));
    $sql .= " WHERE inv.kode_toko IN ('" . $placeholders . "')";
}

$sql .= " GROUP BY kode ORDER BY tgl DESC";


$stmt = $conn->prepare($sql);
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Server Error']);
}
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    http_response_code(200);
    $data = json_encode(['data' => $result->fetch_all(MYSQLI_ASSOC)]);
    echo $data;
} else {
    http_response_code(204);
    echo json_encode(['status' => 'success', 'message' => 'No data']);
}
$stmt->close();
$conn->close();
