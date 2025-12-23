<?php

require_once __DIR__ . '/../../../../config.php';
require_once __DIR__ . '/../../../auth/middleware_login.php';
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json");
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    http_response_code(405);
    echo json_encode(['status' => false, 'message' => 'Request ditolak method tidak terdaftar']);
    $logger->warning('Request method not allowed', ['method' => $_SERVER['REQUEST_METHOD']]);
    exit;
}
$header = getAllHeaders();

if (!isset($header['Authorization']) || $header['Authorization'] == null) {
    http_response_code(401);
    echo json_encode(['status' => false, 'message' => 'Request ditolak user tidak terdaftar']);
    $logger->warning('Authorization header missing');
    exit;
}
$authHeader = $header['Authorization'];
$token = null;

if (preg_match('/^Bearer\s(\S+)$/', $authHeader, $matches)) {
    $token = $matches[1];
}
if (!$token) {
    http_response_code(401);
    echo json_encode(['status' => false, 'message' => 'Request ditolak user tidak terdaftar']);
    $logger->warning('Bearer token missing or invalid');
    exit;
}

$verif = verify_token($token);

try {
    $inputJson = json_decode(file_get_contents('php://input'), true);
    $limit = $inputJson['limit'] ?? 10;
    $page = $inputJson['page'] ?? 1;

    $offset = ($page - 1) * $limit;
    $page = $page > 0 ? $page : 1;
    $limit = $limit > 0 ? min($limit, 100) : 10;

    // Get total count for pagination
    $countSql = "SELECT count(*) as total FROM customers c 
                 LEFT JOIN kode_store AS ks ON ks.kd_store = c.kd_store
                 WHERE c.tgl_daftar = CURDATE()";

    $countStmt = $conn->prepare($countSql);
    if(!$countStmt) {
        http_response_code(500);
        echo json_encode(['status' => false, 'message' => 'Internal server error']);
        $logger->error('Failed to prepare count statement');
        exit;
    }
    $countStmt->execute();
    $total = $countStmt->get_result()->fetch_assoc()['total'];
    $totalPages = $total > 0 ? ceil($total / $limit) : 0;

    // Get data with pagination
    $sql = "SELECT c.nama_cust AS nama_lengkap, 
                   c.kd_cust AS phone, 
                   c.email, 
                   c.tgl_daftar, 
                   ks.nm_alias as cabang, 
                   CASE 
                       WHEN c.tgl_daftar = CURDATE() THEN 'new member'
                       ELSE 'existing member'
                   END AS status
            FROM customers c 
            LEFT JOIN kode_store AS ks ON ks.kd_store = c.kd_store
            WHERE c.tgl_daftar = CURDATE()
            GROUP BY c.kd_cust 
            ORDER BY c.tgl_daftar DESC, c.nama_cust ASC
            LIMIT ? OFFSET ?";

    $stmt = $conn->prepare($sql);
    if(!$stmt) {
        http_response_code(500);
        echo json_encode(['status' => false, 'message' => 'Internal server error']);
        $logger->error('Failed to prepare data statement');
        exit;
    }
    $stmt->bind_param('ii', $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    // Enhanced response with detailed pagination info
    http_response_code(200);
    echo json_encode([
        'status' => true,
        'message' => $total > 0 ? 'Data ditemukan' : 'Tidak ada data member baru hari ini',
        'data' => $result,
        'pagination' => [
            'total' => (int)$total,
            'page' => (int)$page,
            'limit' => (int)$limit,
            'total_pages' => (int)$totalPages,
            'has_more' => $page < $totalPages,
            'current_count' => count($result),
            'offset' => (int)$offset,
            'is_first_page' => $page === 1,
            'is_last_page' => $page >= $totalPages
        ],
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    $stmt->close();
    $countStmt->close();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => false, 'message' => $e->getMessage()]);
    exit;
} finally {
    $conn->close();
    exit;
}
