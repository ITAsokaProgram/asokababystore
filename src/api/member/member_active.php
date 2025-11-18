<?php

include "../../../aa_kon_sett.php";
header("Content-Type:application/json");
ini_set("display_errors", 1);
ini_set("display_startup_errors", 1);
error_reporting(E_ALL);


function sqlQuery($conn, $sql, ...$params)
{
  $stmt = $conn->prepare($sql);
  $type = str_repeat('s', count($params));
  $stmt->bind_param($type, ...$params);
  $stmt->execute();
  $result = $stmt->get_result();
  return $result->fetch_all(MYSQLI_ASSOC);
}

$data = json_decode(file_get_contents("php://input"), true);

$kd_store = $data['cabang'];
if (!is_array($kd_store)) {
  $kd_store = explode(',', $kd_store);
}
$placeholders = implode(',', array_fill(0, count($kd_store), '?'));
$sqlTopTenMember = "
-- sql top 10 member aktif --
SELECT 
  t.kd_cust,
  c.nama_cust,
  SUM(t.hrg_promo * t.qty) AS total_belanja,
  t.kd_store,
  s.Nm_Alias
FROM trans_b t
JOIN customers c ON t.kd_cust = c.kd_cust
LEFT JOIN kode_store s ON t.kd_store = s.kd_store
WHERE 
  t.kd_cust IS NOT NULL
  AND t.kd_cust NOT IN ('', '898989', '89898989', '999999999')
  AND c.kd_cust NOT IN ('', '89898989', '999999999')
  AND t.tgl_trans >= CURDATE() - INTERVAL 3 MONTH
  AND t.kd_store IN ($placeholders)
GROUP BY t.kd_cust, c.nama_cust
ORDER BY total_belanja DESC
LIMIT 10";

$sqlTopTenBarang = "
-- sql top 10 barang terlaris --
SELECT 
  barcode,
  descp,
  SUM(qty) AS total_terjual
FROM trans_b
WHERE 
  kd_cust IS NOT NULL
  AND kd_cust NOT IN ('', '898989', '89898989', '999999999')
  AND tgl_trans >= CURDATE() - INTERVAL 3 MONTH
  AND kd_store IN ($placeholders)
GROUP BY barcode, descp
ORDER BY total_terjual DESC
LIMIT 10";


$sqlTrendTransaksi = "SELECT 
  DATE_FORMAT(last_trans_date, '%Y-%m') AS bulan,
  COUNT(*) AS total_member_aktif
FROM (
  SELECT kd_cust, MAX(tgl_trans) AS last_trans_date
  FROM trans_b
  WHERE 
    kd_cust IS NOT NULL
    AND kd_cust NOT IN ('', '898989', '89898989', '999999999')
    AND kd_store IN ($placeholders)
  GROUP BY kd_cust
) AS last_trans
WHERE last_trans_date >= CURDATE() - INTERVAL 3 MONTH
GROUP BY bulan
ORDER BY bulan;
";

$sqlActiveOrNot = "SELECT
  COUNT(*) AS active_members
FROM (
  SELECT kd_cust, MAX(tgl_trans) AS last_trans_date
  FROM trans_b
  WHERE 
    kd_cust IS NOT NULL 
    AND kd_cust NOT IN ('', '898989', '89898989', '999999999')
    AND kd_store IN ($placeholders)
  GROUP BY kd_cust
) AS last_trans
WHERE last_trans_date >= CURDATE() - INTERVAL 3 MONTH";

$queries = [
  'top_10_member' => sqlQuery($conn, $sqlTopTenMember, ...$kd_store),
  'top_10_barang' => sqlQuery($conn, $sqlTopTenBarang, ...$kd_store),
  'trend_active' => sqlQuery($conn, $sqlTrendTransaksi, ...$kd_store),
  "active_member" => sqlQuery($conn, $sqlActiveOrNot, ...$kd_store)
];

if ($queries) {
  http_response_code(200);
  echo json_encode(["data" => $queries]);
} else {
  http_response_code(400);
  echo json_encode(['status' => "error", "message" => "Terjadi Kesalahan Load Data"]);
}
$conn->close();
