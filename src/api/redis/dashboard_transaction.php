<?php
require_once __DIR__ . "/../../../config.php";
require_once __DIR__ . "/../../../redis.php";


function forSelectQuery($queries, $conn)
{
  $response = [];

  foreach ($queries as $queryName => $query) {
    echo "Mencoba menjalankan query: '$queryName'...\n";

    $result = $conn->query($query); 

    if ($result === false) {
      echo "===================================================\n";
      echo "ERROR: Query '$queryName' GAGAL!\n";
      echo "Pesan Error MySQL: " . $conn->error . "\n";
      echo "===================================================\n";
      die(); 
    }

    echo "Query '$queryName' SUKSES.\n";
    $data = $result->fetch_all(MYSQLI_ASSOC);
    $response[$queryName] = $data;
  }
  return $response;
}

$redisKey = "transaction_dashboard";
$now = new DateTime('now', new DateTimeZone('Asia/Jakarta'));
$next9am = new DateTime('tomorrow 09:00', new DateTimeZone('Asia/Jakarta'));
$ttl = $next9am->getTimestamp() - $now->getTimestamp();

// sql total pelanggan member dan non member
$sqlTotalTransaksi = "SELECT COUNT(DISTINCT no_bon) AS total_transaksi,
COUNT(DISTINCT CASE WHEN kd_cust IS NOT NULL AND kd_cust NOT IN ('', '898989',  '999999999') THEN no_bon END) AS member,
COUNT(DISTINCT CASE WHEN kd_cust IS NULL OR kd_cust IN ('', '898989',  '999999999') THEN no_bon END) AS non_member
FROM trans_b WHERE tgl_trans = CURDATE() - INTERVAL 1 DAY";

$sqlTertinggiTrans = "SELECT 
 ks.Nm_Alias AS cabang,
  lowest.total_transaksi,
  COUNT(DISTINCT CASE 
    WHEN t.kd_cust IS NOT NULL 
      AND t.kd_cust NOT IN ('', '898989',  '999999999') 
    THEN t.no_bon END) AS member,
  COUNT(DISTINCT CASE 
    WHEN t.kd_cust IS NULL 
      OR t.kd_cust IN ('', '898989',  '999999999') 
    THEN t.no_bon END) AS non_member
FROM (
  SELECT kd_store, COUNT(DISTINCT no_bon) AS total_transaksi
  FROM trans_b
  WHERE tgl_trans = CURDATE() - INTERVAL 1 DAY
  GROUP BY kd_store
  ORDER BY total_transaksi DESC
  LIMIT 1
) AS lowest
LEFT JOIN kode_store ks ON ks.kd_store = lowest.kd_store
LEFT JOIN trans_b t 
  ON t.kd_store = lowest.kd_store
  AND t.tgl_trans = CURDATE() - INTERVAL 1 DAY";

$sqlTerendahTrans = "SELECT 
  ks.nm_alias AS cabang,
  lowest.total_transaksi,
  
  COUNT(DISTINCT CASE 
    WHEN t.kd_cust IS NOT NULL 
      AND t.kd_cust NOT IN ('', '898989',  '999999999') 
    THEN t.no_bon END) AS member,

  COUNT(DISTINCT CASE 
    WHEN t.kd_cust IS NULL 
      OR t.kd_cust IN ('', '898989',  '999999999') 
    THEN t.no_bon END) AS non_member

FROM (
  SELECT kd_store, COUNT(DISTINCT no_bon) AS total_transaksi
  FROM trans_b
  WHERE tgl_trans = CURDATE() - INTERVAL 1 DAY
  GROUP BY kd_store
  ORDER BY total_transaksi ASC
  LIMIT 1
) AS lowest
LEFT JOIN kode_store ks ON ks.kd_store = lowest.kd_store
LEFT JOIN trans_b t 
  ON t.kd_store = lowest.kd_store
  AND t.tgl_trans = CURDATE() - INTERVAL 1 DAY";

$sqlTopSalesByMember = "SELECT 
  t.kd_cust,
  c.nama_cust AS nama_customer,
  t.descp AS barang,
  t.plu,
  SUM(t.qty) AS total_qty
FROM trans_b t
LEFT JOIN customers c ON t.kd_cust = c.kd_cust
WHERE 
  t.kd_cust IS NOT NULL
  AND t.kd_cust NOT IN ('', '898989',  '999999999')
  AND t.tgl_trans =  CURDATE() - INTERVAL 1 DAY 
GROUP BY t.kd_cust, t.plu
ORDER BY total_qty DESC limit 1";

$sqlTopSalesByProduct = "SELECT descp AS barang,barcode AS kode_pabrik, plu, SUM(qty) AS total_qty
FROM trans_b WHERE tgl_trans = CURDATE() - INTERVAL 1 DAY AND kd_cust NOT IN ('', '898989',  '999999999')
GROUP BY plu ORDER BY total_qty DESC limit 1";

$sqlJumlahMemberPerCabang = "SELECT 
  ks.Nm_Alias AS cabang,
  COUNT(mc.kd_cust) AS jumlah_member
FROM (
  SELECT 
    kd_store,
    kd_cust
  FROM trans_b
  WHERE kd_cust IS NOT NULL
    AND TRIM(kd_cust) NOT IN ('', '898989', '999999999')
  GROUP BY kd_store, kd_cust
) AS mc
LEFT JOIN kode_store ks ON ks.kd_store = mc.kd_store
GROUP BY mc.kd_store, ks.Nm_Alias
ORDER BY ks.Nm_Alias";

$queries = [
  'total_trans' => $sqlTotalTransaksi,
  'trans_tertinggi' => $sqlTertinggiTrans,
  'trans_terendah' => $sqlTerendahTrans,
  'top_sales_by_member' => $sqlTopSalesByMember,
  'top_sales_by_product' => $sqlTopSalesByProduct,
  'jumlah_member_per_cabang' => $sqlJumlahMemberPerCabang,
];



$response = forSelectQuery($queries, $conn);
$redis->setex($redisKey, $ttl, json_encode(['data' => $response]));
echo date('Y-m-d H:i:s') . " - Redis updated: $redisKey\n";
$conn->close();
