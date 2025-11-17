<?php

require_once __DIR__ . ("./../../../../config.php"); // Menggunakan config.php
require_once __DIR__ . ("./../../../auth/middleware_login.php");
header("Content-Type:application/json");
ini_set("display_errors", 1);
ini_set("display_startup_errors", 1);
error_reporting(E_ALL);

$headers = getallheaders();
$token = $headers['Authorization'];
$token = str_replace('Bearer ', '', $token);
$user = verify_token($token);
if (!$user) {
  http_response_code(401);
  echo json_encode(['status' => false, 'message' => 'Unauthorized']);
  exit;
}

// --- LOGIKA FILTER BARU ---
$params = [];
$types = "";
$date_sql = "";
$status_sql = "";

$filter_type = $_GET['filter_type'] ?? null;
$filter_preset = $_GET['filter'] ?? null;
$start_date = $_GET['start_date'] ?? null;
$end_date = $_GET['end_date'] ?? null;
$status = $_GET['status'] ?? 'all';

if ($filter_type === 'custom' && $start_date && $end_date) {
  if ($start_date === $end_date) {
    $date_sql = " AND DATE(t.tgl_trans) = ? ";
    $params[] = $start_date;
    $types .= "s";
  } else {
    $date_sql = " AND t.tgl_trans BETWEEN ? AND ? ";
    $params[] = $start_date;
    $params[] = $end_date;
    $types .= "ss";
  }
} elseif ($filter_type === 'preset' && $filter_preset) {
  $end = date('Y-m-d'); // Hari ini
  $start = '';
  switch ($filter_preset) {
    case 'kemarin':
      $start = date('Y-m-d', strtotime('-1 day'));
      $end = $start; // Hanya kemarin
      $date_sql = " AND DATE(t.tgl_trans) = ? ";
      $params[] = $start;
      $types .= "s";
      break;
    case '1minggu':
      $start = date('Y-m-d', strtotime('-7 days'));
      $date_sql = " AND t.tgl_trans BETWEEN ? AND ? ";
      $params[] = $start;
      $params[] = $end;
      $types .= "ss";
      break;
    case '1bulan':
      $start = date('Y-m-d', strtotime('-1 month'));
      $date_sql = " AND t.tgl_trans BETWEEN ? AND ? ";
      $params[] = $start;
      $params[] = $end;
      $types .= "ss";
      break;
    case '3bulan':
      $start = date('Y-m-d', strtotime('-3 months'));
      $date_sql = " AND t.tgl_trans BETWEEN ? AND ? ";
      $params[] = $start;
      $params[] = $end;
      $types .= "ss";
      break;
    case '6bulan':
      $start = date('Y-m-d', strtotime('-6 months'));
      $date_sql = " AND t.tgl_trans BETWEEN ? AND ? ";
      $params[] = $start;
      $params[] = $end;
      $types .= "ss";
      break;
    case '9bulan':
      $start = date('Y-m-d', strtotime('-9 months'));
      $date_sql = " AND t.tgl_trans BETWEEN ? AND ? ";
      $params[] = $start;
      $params[] = $end;
      $types .= "ss";
      break;
    case '12bulan':
      $start = date('Y-m-d', strtotime('-12 months'));
      $date_sql = " AND t.tgl_trans BETWEEN ? AND ? ";
      $params[] = $start;
      $params[] = $end;
      $types .= "ss";
      break;
    case 'semua':
      $date_sql = ""; // Tidak ada filter tanggal
      break;
    default: // Default ke 3 bulan
      $start = date('Y-m-d', strtotime('-3 months'));
      $date_sql = " AND t.tgl_trans BETWEEN ? AND ? ";
      $params[] = $start;
      $params[] = $end;
      $types .= "ss";
      break;
  }
} else {
  // Fallback jika tidak ada filter_type (default 'kemarin' dari param lama)
  $start_date_default = $start_date ?? date('Y-m-d', strtotime('-1 day'));
  $end_date_default = $end_date ?? $start_date_default;

  if ($start_date_default === $end_date_default) {
    $date_sql = " AND DATE(t.tgl_trans) = ? ";
    $params[] = $start_date_default;
    $types .= "s";
  } else {
    $date_sql = " AND t.tgl_trans BETWEEN ? AND ? ";
    $params[] = $start_date_default;
    $params[] = $end_date_default;
    $types .= "ss";
  }
}

// Logika Status
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
// --- AKHIR LOGIKA FILTER BARU ---

$sql = "SELECT 
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
    AND t.kd_cust NOT IN ('', '898989', '#898989', '#999999999')
    $date_sql
    $status_sql
GROUP BY t.kd_cust, t.plu, c.nama_cust, t.descp
ORDER BY total_qty DESC 
LIMIT 100"; // Limit tetap 100

$stmt = $conn->prepare($sql);

if (!$stmt) {
  http_response_code(500);
  echo json_encode(['status' => false, 'message' => 'Internal Server Error: ' . $conn->error]);
  exit;
}

if (!empty($params)) {
  $stmt->bind_param($types, ...$params);
}

$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows > 0) {
  http_response_code(200);
  $data = $result->fetch_all(MYSQLI_ASSOC);
  echo json_encode(['status' => true, 'data' => $data]);
} else {
  http_response_code(200); // Tetap 200 OK
  echo json_encode(['status' => false, 'message' => 'Data Kosong']);
}
$stmt->close();
$conn->close();
?>