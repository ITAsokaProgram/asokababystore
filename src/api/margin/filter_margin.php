<?php

require_once __DIR__ . ("./../../../aa_kon_sett.php");
require_once __DIR__ . ("./../../auth/middleware_login.php");

header("Content-Type:application/json");
header("Access-Control-Allow-Methods: GET");

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
$start = $_GET['start'];
$end = $_GET['end'];
$kd_store = $_GET['cabang'] ?? null;

if ($kd_store == "all") {
    $sql = "SELECT 
    t.plu, 
    t.descp,
    t.tgl_trans AS tgl,
    s.Nm_Alias AS cabang,
    t.no_bon AS no_trans,
    t.kd_store AS kode,
    m.status_cek,
    m.nama_cek,   
    m.ket_cek,    
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
        tr.conv2, tr.conv1, tr.qtyppn, tr.tgl_trans, tr.kd_store, tr.no_bon
    FROM trans_b tr
    WHERE tr.tgl_trans BETWEEN ? AND ?  AND tr.avg_cost > tr.hrg_promo
) AS t 
LEFT JOIN kode_store s ON t.kd_store = s.kd_store
LEFT JOIN margin m ON t.plu = m.plu AND m.kd_store = t.kd_store AND m.no_bon = t.no_bon
GROUP BY t.plu ORDER BY  t.tgl_trans DESC";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "Server Error"]);
    }
    $stmt->bind_param("ss", $start, $end);
} else {

    $storeArray = explode(',', $kd_store);
    $placeholders = implode(',', array_fill(0, count($storeArray), '?'));
    $sql = "SELECT 
    t.plu, 
    t.descp,
    t.tgl_trans AS tgl,
    s.Nm_Alias AS cabang,
    t.no_bon AS no_trans,
    t.kd_store AS kode,
    m.status_cek,
    m.nama_cek,   
    m.ket_cek,    
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
        tr.conv2, tr.conv1, tr.qtyppn, tr.tgl_trans, tr.kd_store, tr.no_bon
    FROM trans_b tr
    WHERE tr.tgl_trans BETWEEN ? AND ? AND tr.kd_store IN ($placeholders)
    AND tr.avg_cost > tr.hrg_promo
) AS t 
LEFT JOIN kode_store s ON t.kd_store = s.kd_store
LEFT JOIN margin m ON t.plu = m.plu AND m.kd_store = t.kd_store AND m.no_bon = t.no_bon
GROUP BY t.plu ORDER BY t.tgl_trans DESC";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "Server Error"]);
    }
    $params = array_merge([$start, $end], $storeArray);
    $types = str_repeat('s', count($params));

    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    http_response_code(200);
    $data = json_encode(['data' => $result->fetch_all(MYSQLI_ASSOC)]);
    echo $data;
} else {
    http_response_code(204);
    echo json_encode(['status' => 'error', 'message' => 'Data tidak ada']);
}
$stmt->close();
$conn->close();
