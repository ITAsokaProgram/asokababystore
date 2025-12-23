<?php
// src/api/user/get_user_in.php

require_once __DIR__ . "/../../../aa_kon_sett.php";
require_once __DIR__ . "/../../auth/middleware_login.php";

header("Content-Type:application/json");
header("Access-Control-Allow-Methods: GET");

// 1. Validasi Token
$headers = getallheaders();
$authHeader = $headers['Authorization'] ?? '';
$token = null;
if (preg_match('/^Bearer\s(\S+)$/', $authHeader, $matches)) {
    $token = $matches[1];
}
if (!$token) {
    http_response_code(401);
    echo json_encode(['status' => "Unauthenticated", 'message' => 'Request ditolak user tidak terdaftar']);
    exit;
}
$verif = verify_token($token); // Asumsi fungsi ini ada dan valid

// 2. Ambil Parameter dari Request
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 10;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$offset = ($page - 1) * $limit;

// 3. Bangun Query Dasar (WHERE)
$whereClause = "WHERE (user_account.aktif = 1 OR user_account.aktif = 'True' OR user_account.aktif = '1')";
$params = [];
$types = "";

if (!empty($search)) {
    $searchTerm = "%" . $search . "%";
    // Cari berdasarkan Nama, Kode User, atau Hak Akses
    $whereClause .= " AND (user_account.nama LIKE ? OR user_account.kode LIKE ? OR user_account.hak LIKE ?)";
    array_push($params, $searchTerm, $searchTerm, $searchTerm);
    $types .= "sss";
}

// 4. Query Total Data (Untuk hitung jumlah halaman)
// Kita hitung DISTINCT kode karena query utama menggunakan GROUP BY
$countSql = "SELECT COUNT(DISTINCT user_account.kode) as total 
             FROM user_account 
             LEFT JOIN user_internal_access ON user_account.kode = user_internal_access.id_user 
             $whereClause";

$stmtCount = $conn->prepare($countSql);
if (!empty($params)) {
    $stmtCount->bind_param($types, ...$params);
}
$stmtCount->execute();
$resultCount = $stmtCount->get_result();
$totalRows = $resultCount->fetch_assoc()['total'];
$stmtCount->close();

// 5. Query Data Utama (Dengan LIMIT & OFFSET)
$sql = "SELECT 
            user_account.nama, 
            user_account.hak, 
            user_account.kode, 
            GROUP_CONCAT(user_internal_access.menu_code) AS menu_code, 
            user_account.kd_store AS kode_cabang
        FROM user_account 
        LEFT JOIN user_internal_access ON user_account.kode = user_internal_access.id_user 
        $whereClause
        GROUP BY user_account.kode
        ORDER BY user_account.kode DESC 
        LIMIT ? OFFSET ?";

// Tambahkan Limit dan Offset ke parameter binding
array_push($params, $limit, $offset);
$types .= "ii";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Server Error: " . $conn->error]);
    exit;
}

$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();

// 6. Return Response JSON dengan Metadata Pagination
$totalPages = ceil($totalRows / $limit);

http_response_code(200);
echo json_encode([
    'status' => 'success',
    'data' => $data,
    'pagination' => [
        'current_page' => $page,
        'total_pages' => $totalPages,
        'total_records' => $totalRows,
        'limit' => $limit
    ]
]);
?>