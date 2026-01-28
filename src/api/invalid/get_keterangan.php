<?php
require_once __DIR__ . ("./../../../aa_kon_sett.php");
require_once __DIR__ . ("./../../auth/middleware_login.php");

header("Content-Type:application/json");
header("Access-Control-Allow-Methods: GET");

$verif = authenticate_request();
$plu = $_GET['plu'] ?? '';
$kode_kasir = $_GET['kasir'] ?? '';
$kd_store = $_GET['cabang'] ?? '';
$tgl = $_GET['tgl'] ?? '';
$jam = $_GET['jam'] ?? '';

// Validasi input dasar
if (!$plu || !$kode_kasir || !$kd_store || !$tgl || !$jam) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Parameter tidak lengkap"]);
    exit;
}

$sql = "SELECT ket_cek, nama_cek 
FROM invtrans 
WHERE plu = ?
  AND kode_kasir = ? 
  AND kd_store = ?
  AND tgl_trans = ? 
  AND jam_trs = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Server Error"]);
    exit;
}

$stmt->bind_param("sssss", $plu, $kode_kasir, $kd_store, $tgl, $jam);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows > 0){
    http_response_code(200);
    $data = json_encode(['data'=>$result->fetch_all(MYSQLI_ASSOC)]);
    echo $data;
} else {
    http_response_code(204);
    echo json_encode(['status'=> 'error','message'=> 'Data tidak ada']);
}

$stmt->close();
$conn->close();
