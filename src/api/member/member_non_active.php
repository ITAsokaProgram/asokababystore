<?php
include "../../../aa_kon_sett.php";
header("Content-Type:application/json");
ini_set("display_errors", 1);
ini_set("display_startup_errors", 1);
error_reporting(E_ALL);
function sqlQuery($conn, $sql, ...$params)
{
  $stmt = $conn->prepare($sql);
  if (count($params) > 0) {
    $type = str_repeat('s', count($params));
    $stmt->bind_param($type, ...$params);
  }
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

$sqlCountNonActive = "SELECT
  COUNT(CASE WHEN mla.last_trans_date IS NULL THEN 1 END) AS belum_pernah_transaksi,
  COUNT(CASE WHEN mla.last_trans_date < CURDATE() - INTERVAL 3 MONTH THEN 1 END) AS non_active_members
FROM customers c
LEFT JOIN (
  SELECT kd_cust, MAX(tgl_trans) AS last_trans_date
  FROM trans_b
  WHERE 
    kd_cust IS NOT NULL 
    AND kd_cust NOT IN ('', '898989', '89898989', '999999999')
  GROUP BY kd_cust
) AS mla ON c.kd_cust = mla.kd_cust
WHERE 
  c.kd_cust IS NOT NULL 
  AND c.kd_cust NOT IN ('', '898989', '89898989', '999999999')
";

$sqlRataNonActive = "SELECT 
  AVG(TIMESTAMPDIFF(MONTH, mla.last_trans_date, CURDATE())) AS avg_inactive_months
FROM customers c
LEFT JOIN (
  SELECT kd_cust, MAX(tgl_trans) AS last_trans_date
  FROM trans_b
  WHERE 
    kd_cust IS NOT NULL 
    AND kd_cust NOT IN ('', '898989', '89898989', '999999999')
  GROUP BY kd_cust
) AS mla ON c.kd_cust = mla.kd_cust
WHERE 
  c.kd_cust IS NOT NULL 
  AND c.kd_cust NOT IN ('', '898989', '89898989', '999999999')
  AND mla.last_trans_date < CURDATE() - INTERVAL 3 MONTH";

$sqlTrend = "SELECT
  DATE_FORMAT(mla.last_trans_date, '%Y-%m') AS bulan,
  COUNT(*) AS jumlah_nonaktif
FROM (
  SELECT kd_cust, MAX(tgl_trans) AS last_trans_date
  FROM trans_b
  WHERE 
    kd_cust IS NOT NULL 
    AND kd_cust NOT IN ('', '898989', '89898989', '999999999')
  GROUP BY kd_cust
) AS mla
WHERE mla.last_trans_date < CURDATE() - INTERVAL 3 MONTH
AND mla.last_trans_date >= CURDATE() - INTERVAL 12 MONTH
GROUP BY bulan ORDER BY bulan";

$sqlSegmentasi = "SELECT 
  c.kd_cust,
  c.nama_cust,
  t2.last_trans AS tgl_trans_terakhir,
  s.Nm_Alias AS nama_cabang
FROM customers c
JOIN (
  SELECT kd_cust,kd_store, MAX(tgl_trans) AS last_trans
  FROM trans_b
  WHERE 
    kd_cust IS NOT NULL 
    AND kd_cust NOT IN ('', '898989', '89898989', '999999999')
  GROUP BY kd_cust
) t2 ON c.kd_cust = t2.kd_cust
LEFT JOIN kode_store s ON t2.kd_store = s.Kd_Store  
WHERE 
  DATE(t2.last_trans) < CURDATE() - INTERVAL 3 MONTH
  AND c.kd_cust NOT IN ('', '898989', '89898989', '999999999') ORDER BY t2.last_trans DESC";

$sqlNonActiveTerbanyak = "SELECT 
  c.kd_store,
  COUNT(*) AS jumlah_member_nonaktif
FROM customers c
LEFT JOIN (
  SELECT kd_cust, MAX(tgl_trans) AS last_trans_date
  FROM trans_b
  WHERE 
    kd_cust IS NOT NULL
    AND kd_cust NOT IN ('', '898989', '89898989', '999999999')
  GROUP BY kd_cust
) AS mla ON c.kd_cust = mla.kd_cust
WHERE 
  c.kd_cust IS NOT NULL
  AND c.kd_cust NOT IN ('', '898989', '89898989', '999999999')
  AND mla.last_trans_date < CURDATE() - INTERVAL 3 MONTH 
GROUP BY c.kd_store
ORDER BY jumlah_member_nonaktif DESC
LIMIT 1";

$queries = [
  'total_rata' => sqlQuery($conn, $sqlRataNonActive),
  'trend' => sqlQuery($conn, $sqlTrend),
  "segmen" => sqlQuery($conn, $sqlSegmentasi),
  "non_active_terbanyak" => sqlQuery($conn, $sqlNonActiveTerbanyak),
  "total_non_active" => sqlQuery($conn, $sqlCountNonActive),
];

if ($queries) {
  http_response_code(200);
  echo json_encode(["data" => $queries]);
} else {
  http_response_code(400);
  echo json_encode(['status' => "error", "message" => "Terjadi Kesalahan Load Data"]);
}

$conn->close();
