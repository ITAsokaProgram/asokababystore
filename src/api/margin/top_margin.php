<?php
require_once __DIR__ . "/../../../aa_kon_sett.php";
require_once __DIR__ . "/../../auth/middleware_login.php";
header("Content-Type:application/json");
header("Access-Control-Allow-Methods: GET");
$headers = getallheaders();

if (!isset($headers['Authorization'])) {
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
$sql = "SELECT 
    t.plu, 
    t.tgl_trans AS tgl,
    s.Nm_Alias AS cabang,
    t.no_bon AS no_trans,
    t.kd_store AS kode,
    m.status_cek,
    SUM(
        CASE 
            WHEN MOD(t.plu, 10) = 0 THEN t.qty * t.conv2 
            WHEN MOD(t.plu, 10) = 1 THEN t.qty * (t.conv2 / t.conv1) 
            WHEN MOD(t.plu, 10) = 2 THEN t.qty 
        END
    ) AS qty, 
    IFNULL(SUM(t.qty * t.harga), 0) AS GROSS, 
    IFNULL(SUM((t.harga - t.hrg_promo) * t.qty), 0) AS diskon, 
    IFNULL(SUM(t.hrg_promo * t.qty), 0) AS net, 
    IFNULL(SUM(t.avg_cost * t.qty), 0) AS avg_cost,
    IFNULL(SUM((t.avg_cost * 0.11) * t.qtyppn), 0) AS PPN, 
    IFNULL(
        SUM(t.hrg_promo * t.qty) - 
        (SUM(t.avg_cost * t.qty) + IFNULL(SUM((t.avg_cost * 0.11) * t.qtyppn), 0)), 
        0
    ) AS Margin
FROM (
    SELECT 
        tr.plu, tr.descp, tr.qty, tr.harga, tr.ppn, tr.hrg_promo, tr.avg_cost, 
        tr.conv2, tr.conv1, tr.qtyppn, tr.tgl_trans, tr.kd_store , tr.no_bon
    FROM trans_b tr
    WHERE tr.tgl_trans = CURDATE() - INTERVAL 1 DAY 
      AND tr.avg_cost > tr.hrg_promo
) AS t 
LEFT JOIN kode_store s ON t.kd_store = s.kd_store
LEFT JOIN margin m ON t.plu = m.plu AND m.kd_store = t.kd_store AND m.no_bon = t.no_bon
GROUP BY t.kd_store ORDER BY Margin ASC";

$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_all(MYSQLI_ASSOC);
$count = $result->num_rows;

if ($count > 0) {
    error_log("DEBUG: Data ditemukan, mengirim 200");
    http_response_code(200);
    echo json_encode(['status' => true, 'message' => 'Data berhasil diambil', 'data' => $data]);
} else {
    error_log("DEBUG: Data tidak ditemukan, mengirim 200");
    http_response_code(200);
    echo json_encode(['status' => false, 'message' => 'Data tidak ditemukan', 'data' => []]);
}
$stmt->close();
$conn->close();
exit;
