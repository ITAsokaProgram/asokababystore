<?php
require_once __DIR__ . ("./../../../aa_kon_sett.php");
require_once __DIR__ . ("./../../auth/middleware_login.php");

header("Content-Type:application/json");
header("Access-Control-Allow-Methods: GET");

$verif = authenticate_request();


function forSelectQuery($queries, $conn)
{
  $response = [];

  foreach ($queries as $queryName => $config) {
    $sqlText = $config['sql'];
    $params = $config['params'] ?? [];

    $stmt = $conn->prepare($sqlText);
    if (!$stmt) {
      http_response_code(500);
      $response[$queryName] = ['error' => 'Prepare failed: ' . $conn->error];
      continue;
    }

    // Bind parameter jika ada
    if (!empty($params)) {
      $types = str_repeat('s', count($params));
      if (!$stmt->bind_param($types, ...$params)) {
        http_response_code(500);
        $response[$queryName] = ['error' => 'Bind failed: ' . $stmt->error];
        $stmt->close();
        continue;
      }
    }

    if (!$stmt->execute()) {
      http_response_code(500);
      $response[$queryName] = ['error' => 'Execute failed: ' . $stmt->error];
      $stmt->close();
      continue;
    }

    $result = $stmt->get_result();
    $response[$queryName] = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

    $stmt->close();
  }

  return $response;
}


$kd_store = $_GET['cabang'] ?? "";
$kd_store = preg_replace("/[^a-zA-Z0-9]/", "", $kd_store);
$sqlCabang = "SELECT 
  ks.Nm_Alias AS cabang,
  l.kd_store,
  l.total_transaksi,
  COUNT(DISTINCT CASE 
    WHEN t.kd_cust IS NOT NULL 
      AND t.kd_cust NOT IN ('', '898989', '89898989', '999999999') 
    THEN t.no_bon END) AS member,
  COUNT(DISTINCT CASE 
    WHEN t.kd_cust IS NULL 
      OR t.kd_cust IN ('', '898989', '89898989', '999999999') 
    THEN t.no_bon END) AS non_member
FROM (
  SELECT kd_store, COUNT(DISTINCT no_bon) AS total_transaksi
  FROM trans_b
  WHERE tgl_trans = CURDATE() - INTERVAL 1 DAY
  GROUP BY kd_store
) AS l
LEFT JOIN trans_b t 
  ON t.kd_store = l.kd_store
  AND t.tgl_trans = CURDATE() - INTERVAL 1 DAY
LEFT JOIN kode_store ks 
  ON ks.kd_store = l.kd_store
WHERE l.kd_store = ?
GROUP BY l.kd_store, ks.Nm_Alias, l.total_transaksi
ORDER BY l.total_transaksi ASC";

$sqlTableBelanja = "SELECT 
  ks.Nm_Alias AS cabang,
  t.kd_store,
  t.no_bon,
  t.kd_cust,
  c.nama_cust,
  t.tgl_trans,
  t.nama_kasir,
  SUM(t.qty * t.hrg_promo) AS total_belanja,
  CASE 
    WHEN t.kd_cust IS NOT NULL AND t.kd_cust NOT IN ('', '898989', '89898989', '999999999') THEN 'Member'
    ELSE 'Non Member'
  END AS status_member
FROM trans_b t
LEFT JOIN customers c ON c.kd_cust = t.kd_cust
LEFT JOIN kode_store ks ON ks.kd_store = t.kd_store
WHERE t.tgl_trans = CURDATE() - INTERVAL 1 DAY
  AND t.kd_store = ?
GROUP BY t.no_bon
ORDER BY t.no_bon";


$sqlTop10BarangMember = "SELECT 
  descp AS barang, 
  SUM(qty) AS total_qty
FROM trans_b
WHERE 
  kd_cust IS NOT NULL
  AND kd_cust NOT IN ('', '898989', '89898989', '999999999')
  AND tgl_trans >= CURDATE() - INTERVAL 1 DAY AND kd_store = ? AND LOWER(descp) NOT LIKE '%tas asoka%'
AND LOWER(descp) NOT LIKE '%kertas kado%'
GROUP BY descp
ORDER BY total_qty DESC
LIMIT 10";

$sqlTop10BarangNonMember = "SELECT 
  descp AS barang, 
  SUM(qty) AS total_qty
FROM trans_b
WHERE 
  (kd_cust IS NULL OR kd_cust IN ('', '898989', '89898989', '999999999'))
  AND tgl_trans >= CURDATE() - INTERVAL 1 DAY AND kd_store = ? AND LOWER(descp) NOT LIKE '%tas asoka%'
AND LOWER(descp) NOT LIKE '%kertas kado%'
GROUP BY descp
ORDER BY total_qty DESC
LIMIT 10";

$sqlAllCabang = "SELECT 
  COUNT(DISTINCT no_bon) AS total_transaksi,
  COUNT(DISTINCT CASE 
    WHEN kd_cust IS NOT NULL 
      AND kd_cust NOT IN ('', '898989', '89898989', '999999999') 
    THEN no_bon END) AS member,
  COUNT(DISTINCT CASE 
    WHEN kd_cust IS NULL 
      OR kd_cust IN ('', '898989', '89898989', '999999999') 
    THEN no_bon END) AS non_member
FROM trans_b WHERE tgl_trans = CURDATE() - INTERVAL 1 DAY";

$sqlTop10MAll = "SELECT 
  descp AS barang, 
  SUM(qty) AS total_qty
FROM trans_b
WHERE 
  kd_cust IS NOT NULL
  AND kd_cust NOT IN ('', '898989', '89898989', '999999999')
  AND tgl_trans >= CURDATE() - INTERVAL 1 DAY AND LOWER(descp) NOT LIKE '%tas asoka%'
AND LOWER(descp) NOT LIKE '%kertas kado%'
GROUP BY descp
ORDER BY total_qty DESC
LIMIT 10";

$sqlTop10NAll = "SELECT 
  descp AS barang, 
  SUM(qty) AS total_qty
FROM trans_b
WHERE 
  (kd_cust IS NULL OR kd_cust IN ('', '898989', '89898989', '999999999'))
  AND tgl_trans >= CURDATE() - INTERVAL 1 DAY AND LOWER(descp) NOT LIKE '%tas asoka%'
AND LOWER(descp) NOT LIKE '%kertas kado%'
GROUP BY descp
ORDER BY total_qty DESC
LIMIT 10";

if ($kd_store === "all") {
  $queries = [
    'total_trans' => ['sql' => $sqlAllCabang],
    'top_10_member' => ['sql' => $sqlTop10MAll],
    'top_10_non' => ['sql' => $sqlTop10NAll],
  ];
  $response = forSelectQuery($queries, $conn);
} else {
  $queries = [
    'total_trans' => ['sql' => $sqlCabang, 'params' => [$kd_store]],
    'belanja' => ['sql' => $sqlTableBelanja, 'params' => [$kd_store]],
    'top_10_member' => ['sql' => $sqlTop10BarangMember, 'params' => [$kd_store]],
    'top_10_non' => ['sql' => $sqlTop10BarangNonMember, 'params' => [$kd_store]],
  ];
  $response = forSelectQuery($queries, $conn);
}
echo json_encode(['message' => 'GET Success', 'data' => $response]);
$conn->close();
