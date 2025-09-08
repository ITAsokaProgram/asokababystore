<?php

require_once "../../../aa_kon_sett.php";
require_once "../../auth/middleware_login.php";
header("Content-Type:application/json");
header("Access-Control-Allow-Methods: GET");

$headers = getallheaders();
if(!isset($headers['Authorization'])){
    http_response_code(401);
    echo json_encode(['status' => "Unauthenticated", 'message' => 'Request ditolak, token tidak ditemukan']);
    exit;
}
$authHeader = $headers['Authorization'];
$token = null;
if (preg_match('/^Bearer\s(\S+)$/', $authHeader, $matches)) {
    $token = $matches[1]; // ini yang aman dan baku
}

$verif = verify_token($token);

try {
    $sql = " SELECT 
    c.nama_cust,
    tr.store_alias_pk AS cabang, 
    tr.kasir,
    IFNULL(tr.jumlah_transaksi, 0) + IFNULL(pm.j_pm, 0) AS T_Trans
FROM customers c
LEFT JOIN (
    SELECT 
        pk.kd_cust,
        GROUP_CONCAT(DISTINCT pk.kd_store ORDER BY pk.kd_store SEPARATOR ',') AS store_kode,
        COUNT(*) AS jumlah_transaksi,
        GROUP_CONCAT(DISTINCT ks.Nm_Alias ORDER BY ks.Nm_Alias SEPARATOR ', ') AS store_alias_pk,
        nama_kasir AS kasir
    FROM point_kasir pk
    LEFT JOIN kode_store ks ON pk.kd_store = ks.kd_store
    WHERE pk.tanggal BETWEEN CURDATE() - INTERVAL 1 DAY AND CURDATE()
    GROUP BY pk.kd_cust
) AS tr ON c.kd_cust = tr.kd_cust

-- Transaksi Manual
LEFT JOIN (
    SELECT kd_cust, COUNT(*) AS j_pm
    FROM point_manual
    WHERE tgl_trans BETWEEN CURDATE() - INTERVAL 1 DAY AND CURDATE()
    GROUP BY kd_cust
) AS pm ON c.kd_cust = pm.kd_cust

WHERE IFNULL(tr.jumlah_transaksi, 0) > 0 OR IFNULL(pm.j_pm, 0) > 0
ORDER BY T_Trans DESC LIMIT 3";

$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_all(MYSQLI_ASSOC);
$count = $result->num_rows;

if($count > 0){
    http_response_code(200);
    echo json_encode(['status' => true, 'message' => 'Data berhasil diambil', 'data' => $data]);
} else {
    http_response_code(204);
    echo json_encode(['status' => false, 'message' => 'Data tidak ditemukan' , 'data' => []]);
}
} catch (\Throwable $th) {
    http_response_code(500);
    echo json_encode(['status' => false, 'message' => 'Server Error']);
}
$stmt->close();
$conn->close();