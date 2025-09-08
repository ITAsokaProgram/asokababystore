<?php

include "../../../aa_kon_sett.php";
header("Content-Type:application/json");
ini_set("display_errors", 1);
ini_set("display_startup_errors", 1);
error_reporting(E_ALL);
$data = json_decode(file_get_contents("php://input"), true);
$kd_store = $data['cabang'];
if (!is_array($kd_store)) {
    $kd_store = explode(',', $kd_store);
}
$placeholders = implode(',', array_fill(0, count($kd_store), '?'));
$periode = $data['periode'];
if($periode === "1"){
  $tm = "CURDATE() - INTERVAL 1 MONTH";
} else if ($periode === "3") {
  $tm =   "CURDATE() - INTERVAL 3 MONTH";
} else if($periode === "12"){
  $tm = "CURDATE() - INTERVAL 12 MONTH";
} else {
  $tm =   "CURDATE() - INTERVAL 3 MONTH";

}
function sqlQuery($conn, $sql, ...$params)
{
    $stmt = $conn->prepare($sql);
    $type = str_repeat('s', count($params));
    $stmt->bind_param($type, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

$sqlTrendTransaksi = "SELECT 
  DATE_FORMAT(last_trans_date, '%Y-%m') AS bulan,
  COUNT(*) AS total_member_aktif
FROM (
  SELECT kd_cust, MAX(tgl_trans) AS last_trans_date
  FROM trans_b
  WHERE 
    kd_cust IS NOT NULL
    AND kd_cust NOT IN ('', '898989', '#898989', '#999999999')
    AND kd_store IN ($placeholders)
  GROUP BY kd_cust
) AS last_trans
WHERE last_trans_date >= $tm
GROUP BY bulan
ORDER BY bulan
";


$result = ['active_trend' => sqlQuery($conn, $sqlTrendTransaksi, ...$kd_store)];

if($result){
    http_response_code(200);
    echo json_encode($result);
} else {
    http_response_code(400);
    echo json_encode(['status'=>"error", "message"=>"Terjadi Kesalahan Saat Filtering"]);
}
