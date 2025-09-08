<?php

require_once __DIR__ . '/../../../aa_kon_sett.php';
require_once __DIR__ . '/../../auth/middleware_login.php';
require_once __DIR__ . '/../../utils/Logger.php';

$logger = new AppLogger('get_hadiah_tukar.log');
$logger->info('Request hadiah_tukar dari IP: ' . ($_SERVER['REMOTE_ADDR'] ?? '-') . ', headers: ' . json_encode(getAllHeaders()));

header("Content-Type:application/json");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Authorization");

if ($_SERVER['REQUEST_METHOD'] != 'GET') {
    http_response_code(405);
    echo json_encode(['status' => false, 'message' => 'Request ditolak method tidak terdaftar']);
    $logger->info('Request method tidak valid: ' . $_SERVER['REQUEST_METHOD']);
    exit;
}

$header = getAllHeaders();
if (!isset($header['Authorization']) || $header['Authorization'] == null) {
    $logger->warning('Request tanpa Authorization header');
    http_response_code(401);
    echo json_encode(['status' => false, 'message' => 'Request ditolak user tidak terdaftar']);
    exit;
}

$authHeader = $header['Authorization'];
$token = null;
if (preg_match('/^Bearer\s(\S+)$/', $authHeader, $matches)) {
    $token = $matches[1];
}
if (!$token) {
    $logger->warning('Request tanpa token Bearer');
    http_response_code(401);
    echo json_encode(['status' => false, 'message' => 'Request ditolak user tidak terdaftar']);
    exit;
}
$verif = verify_token($token);
$user_id = $verif->id;

// Get pagination parameters
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = isset($_GET['limit']) ? max(1, min(50, intval($_GET['limit']))) : 10; // Max 50 items per page
$offset = ($page - 1) * $limit;

// Get total count
$countSql = "SELECT COUNT(*) as total FROM hadiah_t WHERE id_user = ?";
$countStmt = $conn->prepare($countSql);
$countStmt->bind_param('i', $user_id);
$countStmt->execute();
$countResult = $countStmt->get_result();
$totalCount = $countResult->fetch_assoc()['total'];
$countStmt->close();

// Get paginated data
$sql = "SELECT nama_hadiah AS reward, poin_tukar AS points, qr_code_url AS code, expired_at, dibuat_tanggal AS date, ditukar_tanggal, cabang
        FROM hadiah_t
        WHERE id_user = ?
        ORDER BY dibuat_tanggal DESC
        LIMIT ? OFFSET ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param('iii', $user_id, $limit, $offset);
$stmt->execute();
$result = $stmt->get_result();
if($result->num_rows === 0 && $page === 1) {
    $logger->info('User_id ' . $user_id . ' tidak ada hadiah yang sedang ditukar');
    http_response_code(204);
    echo json_encode(['status' => false, 'message' => 'Tidak ada hadiah yang sedang ditukar']);
    exit;
}
http_response_code(200);
$hadiah = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$logger->success('User_id ' . $user_id . ' ambil data hadiah_tukar halaman ' . $page . ', total: ' . count($hadiah));

// Calculate pagination info
$totalPages = ceil($totalCount / $limit);
$hasMore = $page < $totalPages;

echo json_encode([
    'status' => true, 
    'data' => $hadiah,
    'pagination' => [
        'current_page' => $page,
        'per_page' => $limit,
        'total' => $totalCount,
        'total_pages' => $totalPages,
        'has_more' => $hasMore
    ]
]);
$conn->close();