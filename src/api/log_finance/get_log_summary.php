<?php
session_start();
include '../../../aa_kon_sett.php';
header('Content-Type: application/json');

$selected_date = $_GET['tanggal'] ?? date('Y-m-d');
$search_ref = $_GET['search'] ?? ''; // Tangkap parameter search
$page = (int) ($_GET['page'] ?? 1);
$limit = 50;

if ($page < 1)
  $page = 1;
$offset = ($page - 1) * $limit;

$response = [
  'summary' => ['total' => 0, 'insert' => 0, 'update' => 0, 'delete' => 0],
  'pagination' => ['current_page' => $page, 'total_pages' => 1, 'total_rows' => 0, 'limit' => $limit, 'offset' => $offset],
  'tabel_data' => []
];

try {
  // Base Condition: Tanggal selalu wajib
  $where_sql = "WHERE DATE(created_at) = ?";
  $params = [$selected_date];
  $types = "s";

  // Jika ada search, tambahkan kondisi
  if (!empty($search_ref)) {
    $where_sql .= " AND ref_id LIKE ?";
    $params[] = "%" . $search_ref . "%";
    $types .= "s";
  }

  // 1. Get Aggregated Stats
  // Sesuaikan query stats dengan kondisi search
  $sql_stats = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN action = 'INSERT' THEN 1 ELSE 0 END) as total_insert,
                    SUM(CASE WHEN action = 'UPDATE' THEN 1 ELSE 0 END) as total_update,
                    SUM(CASE WHEN action = 'DELETE' THEN 1 ELSE 0 END) as total_delete
                  FROM finance_activity_logs 
                  $where_sql";

  if ($stmt = $conn->prepare($sql_stats)) {
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    $stats = $result->fetch_assoc();

    $response['summary']['total'] = $stats['total'] ?? 0;
    $response['summary']['insert'] = $stats['total_insert'] ?? 0;
    $response['summary']['update'] = $stats['total_update'] ?? 0;
    $response['summary']['delete'] = $stats['total_delete'] ?? 0;

    $total_rows = $stats['total'] ?? 0;
    $response['pagination']['total_rows'] = $total_rows;
    $response['pagination']['total_pages'] = ceil($total_rows / $limit);
    $stmt->close();
  } else {
    throw new Exception("Error preparing stats query: " . $conn->error);
  }

  // 2. Get Detail Rows
  // Tambahkan Limit dan Offset ke parameter binding
  $sql_rows = "SELECT 
                    l.id, l.table_name, l.ref_id, l.action, l.user_id, 
                    u.inisial as user_inisial, l.old_data, l.new_data, 
                    l.ip_address, l.user_agent, l.created_at 
                 FROM finance_activity_logs l
                 LEFT JOIN user_account u ON l.user_id = u.kode
                 $where_sql 
                 ORDER BY l.created_at DESC
                 LIMIT ? OFFSET ?";

  // Tambahkan parameter untuk limit dan offset
  $params_rows = $params;
  $params_rows[] = $limit;
  $params_rows[] = $offset;
  $types_rows = $types . "ii";

  if ($stmt = $conn->prepare($sql_rows)) {
    $stmt->bind_param($types_rows, ...$params_rows);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
      if (empty($row['user_inisial'])) {
        $row['user_inisial'] = 'Unknown (' . $row['user_id'] . ')';
      }
      $response['tabel_data'][] = $row;
    }
    $stmt->close();
  } else {
    throw new Exception("Error preparing rows query: " . $conn->error);
  }

  echo json_encode($response);

} catch (Exception $e) {
  http_response_code(500);
  echo json_encode(['error' => $e->getMessage()]);
}

$conn->close();
?>