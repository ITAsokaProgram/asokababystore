<?php
require_once __DIR__ . "/../../../../config.php";
require_once __DIR__ . "/../../../../redis.php";
$logger = null;
if (php_sapi_name() === 'cli') {
  require_once __DIR__ . "/../../../../src/utils/Logger.php";
  $logger = new AppLogger('cron_product_fav.log');
  $logger->info("Mulai cron job get_product_fav.php.");
}
if (php_sapi_name() !== 'cli') {
  header("Content-Type:application/json");
}
ini_set("display_errors", 1);
ini_set("display_startup_errors", 1);
error_reporting(E_ALL);
if (php_sapi_name() === 'cli' && isset($argv[1])) {
  parse_str($argv[1], $_GET);
}
if (php_sapi_name() !== 'cli') {
  require_once __DIR__ . ("./../../../auth/middleware_login.php");
  $headers = getallheaders();
  $token = $headers['Authorization'] ?? null;
  if (!$token) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized: Token not provided']);
    exit;
  }
  $token = str_replace('Bearer ', '', $token);
  $user = verify_token($token);
  if (!$user) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized: Invalid token']);
    exit;
  }
}
$search = $_GET['search'] ?? '';
$sortBy = $_GET['sort_by'] ?? 'qty';
$page = (int) ($_GET['page'] ?? 1);
$limit = (int) ($_GET['limit'] ?? 10);
$status = $_GET['status'] ?? 'all';
$filter_type = $_GET['filter_type'] ?? null;
$filter_preset = $_GET['filter'] ?? null;
$start_date = $_GET['start_date'] ?? null;
$end_date = $_GET['end_date'] ?? null;
$cacheKey = "report:product_fav_paginated:" .
  "status=$status" .
  "&search=$search" .
  "&sort=$sortBy" .
  "&page=$page" .
  "&limit=$limit" .
  "&filtertype=$filter_type" .
  "&preset=$filter_preset" .
  "&start=$start_date" .
  "&end=$end_date";
try {
  $cachedData = $redis->get($cacheKey);
  if ($cachedData) {
    if (php_sapi_name() !== 'cli') {
      http_response_code(200);
      echo $cachedData;
    } else {
      echo "Cache found for $cacheKey. Skipping DB query.\n";
    }
    $conn->close();
    exit;
  }
} catch (Exception $e) {
  if ($logger) {
    $logger->error("Redis cache get failed: " . $e->getMessage());
  }
}
if (php_sapi_name() === 'cli') {
  echo "Cache not found. Generating cache...\n";
}
$offset = ($page - 1) * $limit;
$params = [];
$types = "";
$date_sql = "";
$status_sql = "";
$searchSql = "";
if ($filter_type === 'custom' && $start_date && $end_date) {
  if ($start_date === $end_date) {
    $date_sql = " AND t.tgl_trans >= ? AND t.tgl_trans < ? ";
    $params[] = $start_date . " 00:00:00";
    $next_day = date('Y-m-d', strtotime($start_date . ' +1 day'));
    $params[] = $next_day . " 00:00:00";
    $types .= "ss";
  } else {
    $date_sql = " AND t.tgl_trans BETWEEN ? AND ? ";
    $params[] = $start_date . " 00:00:00";
    $params[] = $end_date . " 23:59:59";
    $types .= "ss";
  }
} elseif ($filter_type === 'preset' && $filter_preset) {
  $end = date('Y-m-d');
  $start = '';
  switch ($filter_preset) {
    case 'kemarin':
      $start = date('Y-m-d', strtotime('-1 day'));
      $end = $start;
      $date_sql = " AND t.tgl_trans >= ? AND t.tgl_trans < ? ";
      $params[] = $start . " 00:00:00";
      $next_day = date('Y-m-d', strtotime($start . ' +1 day'));
      $params[] = $next_day . " 00:00:00";
      $types .= "ss";
      break;
    default:
      $start = date('Y-m-d', strtotime('-3 months'));
      $date_sql = " AND t.tgl_trans BETWEEN ? AND ? ";
      $params[] = $start . " 00:00:00";
      $params[] = $end . " 23:59:59";
      $types .= "ss";
      break;
  }
} else {
  $yesterday = date('Y-m-d', strtotime('-1 day'));
  $date_sql = " AND t.tgl_trans >= ? AND t.tgl_trans < ? ";
  $params[] = $yesterday . " 00:00:00";
  $next_day = date('Y-m-d', strtotime($yesterday . ' +1 day'));
  $params[] = $next_day . " 00:00:00";
  $types .= "ss";
}
$cutoff_active = date('Y-m-d 00:00:00', strtotime("-3 months"));
if ($status === 'active') {
  $status_sql = " AND (c.Last_Trans >= ?) ";
  $params[] = $cutoff_active;
  $types .= "s";
} elseif ($status === 'inactive') {
  $status_sql = " AND (c.Last_Trans < ? OR c.Last_Trans IS NULL) ";
  $params[] = $cutoff_active;
  $types .= "s";
}
if (!empty($search)) {
  $searchSql = " AND (c.nama_cust LIKE ? OR t.descp LIKE ? OR t.kd_cust LIKE ?) ";
  $searchValue = "%" . $search . "%";
  $params[] = $searchValue;
  $params[] = $searchValue;
  $params[] = $searchValue;
  $types .= "sss";
}
$orderBySql = " ORDER BY total_qty DESC ";
if ($sortBy === 'harga') {
  $orderBySql = " ORDER BY total_hrg DESC ";
}
$sql = "SELECT 
    SQL_CALC_FOUND_ROWS 
    t.kd_cust,
    c.nama_cust AS nama_customer,
    t.plu,
    t.descp AS barang,
    SUM(t.qty) AS total_qty,
    SUM(t.qty * t.harga) AS total_hrg
FROM trans_b t
LEFT JOIN customers c ON t.kd_cust = c.kd_cust
WHERE 
    t.kd_cust IS NOT NULL
    AND t.kd_cust NOT IN ('', '898989', '89898989', '999999999')
    $date_sql
    $status_sql
    $searchSql
GROUP BY t.kd_cust, t.plu, c.nama_cust, t.descp
$orderBySql
LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$types .= 'ii';
$stmt = $conn->prepare($sql);
if (!$stmt) {
  if ($logger)
    $logger->error("Statement error (data): " . $conn->error);
  if (php_sapi_name() !== 'cli') {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal Server Error: ' . $conn->error]);
  }
  exit;
}
if (!empty($params)) {
  $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$total_records_result = $conn->query("SELECT FOUND_ROWS() AS total");
$total_records = $total_records_result->fetch_assoc()['total'] ?? 0;
$total_pages = ceil($total_records / $limit);
if (empty($data)) {
  if (php_sapi_name() !== 'cli') {
    http_response_code(200);
  }
  $response = [
    "success" => false,
    "message" => "Data tidak ditemukan"
  ];
  $jsonData = json_encode($response);
  try {
    $redis->set($cacheKey, $jsonData);
  } catch (Exception $e) {
    if ($logger)
      $logger->error("Redis cache set (no data) failed: " . $e->getMessage());
  }
  if (php_sapi_name() !== 'cli') {
    echo $jsonData;
  } else {
    echo "Cache generated (no data) for $cacheKey.\n";
  }
  $conn->close();
  exit;
}
$response = [
  "success" => true,
  "data" => $data,
  "pagination" => [
    "current_page" => $page,
    "items_per_page" => $limit,
    "total_records" => (int) $total_records,
    "total_pages" => $total_pages,
    "offset" => $offset
  ]
];
$jsonData = json_encode($response);
try {
  $redis->set($cacheKey, $jsonData);
} catch (Exception $e) {
  if ($logger)
    $logger->error("Redis cache set (with data) failed: " . $e->getMessage());
}
if (php_sapi_name() !== 'cli') {
  http_response_code(200);
  echo $jsonData;
} else {
  echo "Cache generated (with data) for $cacheKey. No TTL set.\n";
}
if ($logger) {
  $logger->info("Selesai cron job get_product_fav.php. Cache generated for $cacheKey.");
}
$conn->close();
?>