<?php
require_once __DIR__ . ("/../../../aa_kon_sett.php");
require_once __DIR__ . ("/../../auth/middleware_login.php");
header("Content-Type:application/json");
header("Access-Control-Allow-Methods: GET");
$headers = getallheaders();
$authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : '';
$token = null;
if (preg_match('/^Bearer\s(\S+)$/', $authHeader, $matches)) {
    $token = $matches[1];
}
if (!$token) {
    http_response_code(401);
    echo json_encode(['status' => "Unauthenticated", 'message' => 'Request ditolak user tidak terdaftar']);
    exit;
}
$verif = verify_token($token);
$statsSql = "SELECT 
                AVG(rating) as avg_rating, 
                COUNT(id) as total_reviews, 
                (SELECT COUNT(r.id) FROM review r LEFT JOIN review_detail rd ON r.id = rd.review_id WHERE r.sudah_terpecahkan = 0 OR rd.status = 'pending') as pending_issues
             FROM review";
$statsStmt = $conn->prepare($statsSql);
$statsStmt->execute();
$statsResult = $statsStmt->get_result()->fetch_assoc();
$stats = [
    'total_reviews' => (int)$statsResult['total_reviews'],
    'avg_rating' => $statsResult['avg_rating'] ? number_format((float)$statsResult['avg_rating'], 1) : '0.0',
    'pending_issues' => (int)$statsResult['pending_issues']
];
$statsStmt->close();
$countsSql = "SELECT rating, COUNT(id) AS count FROM review GROUP BY rating";
$countsStmt = $conn->prepare($countsSql);
$countsStmt->execute();
$countsResult = $countsStmt->get_result();
$ratingCounts = ['all' => 0, '5' => 0, '4' => 0, '3' => 0, '2' => 0, '1' => 0];
while ($row = $countsResult->fetch_assoc()) {
    if (isset($ratingCounts[(string)$row['rating']])) {
        $ratingCounts[(string)$row['rating']] = (int)$row['count'];
        $ratingCounts['all'] += (int)$row['count'];
    }
}
$countsStmt->close();
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$rating = isset($_GET['rating']) ? $_GET['rating'] : 'all';
$status = isset($_GET['status']) ? $_GET['status'] : 'all'; 
$offset = ($page - 1) * $limit;
$whereConditions = [];
$params = [];
$paramTypes = "";
if ($rating !== 'all' && is_numeric($rating)) {
    $whereConditions[] = "r.rating = ?";
    $params[] = (int)$rating;
    $paramTypes .= "i";
}
if ($status !== 'all') {
    if ($status === 'pending') {
        $whereConditions[] = "(rd.status = 'pending' OR r.sudah_terpecahkan = 0)";
    } else if (in_array($status, ['in_progress', 'resolved', 'closed'])) {
        $whereConditions[] = "rd.status = ?";
        $params[] = $status;
        $paramTypes .= "s";
    }
}
$whereClause = "";
if (!empty($whereConditions)) {
    $whereClause = "WHERE " . implode(" AND ", $whereConditions);
}
$totalSql = "SELECT COUNT(DISTINCT r.id) AS total 
             FROM review r 
             LEFT JOIN review_detail rd ON rd.review_id = r.id 
             $whereClause";
$totalStmt = $conn->prepare($totalSql);
if (!empty($params)) {
    $totalStmt->bind_param($paramTypes, ...$params);
}
$totalStmt->execute();
$totalResult = $totalStmt->get_result()->fetch_assoc();
$totalRecords = $totalResult['total'];
$totalPages = $totalRecords > 0 ? ceil($totalRecords / $limit) : 1;
$sql = "SELECT
    r.id, ua.id_user, ua.nama_lengkap AS nama, r.rating, r.komentar, r.kategori,
    r.no_bon AS bon, k.nm_alias AS cabang, r.dibuat_tgl AS tanggal,
    ua.no_hp AS no_hp, r.nama_kasir, 
    MAX(rf.path_file) AS enpoint_foto,
    r.sudah_terpecahkan as sudah_terpecahkan, rd.review_id AS detail_review_id,
    rd.status as detail_status,
    (SELECT COUNT(*) 
     FROM review_conversation rc 
     WHERE rc.review_id = r.id 
       AND rc.sudah_dibaca = 0 
       AND rc.pengirim_type = 'customer'
    ) as unread_count 
FROM review r
LEFT JOIN user_asoka ua ON ua.id_user = r.id_user
LEFT JOIN kode_store k ON k.kd_store = SUBSTRING(r.no_bon, 1, 4)
LEFT JOIN review_foto rf ON rf.review_id = r.id
LEFT JOIN review_detail rd ON rd.review_id = r.id
$whereClause
GROUP BY r.id 
ORDER BY r.dibuat_tgl DESC
LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Server Error: " . $conn->error]);
    exit;
}
$mainParams = $params;
$mainParams[] = $limit;
$mainParams[] = $offset;
$mainParamTypes = $paramTypes . "ii";
$stmt->bind_param($mainParamTypes, ...$mainParams);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    http_response_code(200);
    $rows = $result->fetch_all(MYSQLI_ASSOC);
    $data = array_map(function ($item) {
        return [
            'id' => $item['id'],
            'id_user' => $item['id_user'],
            'nama' => $item['nama'],
            'hp' => $item['no_hp'],
            'rating' => $item['rating'],
            'komentar' => $item['komentar'],
            'kategori' => $item['kategori'],
            'no_bon' => $item['bon'],
            'cabang' => $item['cabang'],
            'nama_kasir' => $item['nama_kasir'],
            'enpoint_foto' => $item['enpoint_foto'],
            'tanggal' => $item['tanggal'],
            'sudah_terpecahkan' => (bool)$item['sudah_terpecahkan'],
            'detail_review_id' => $item['detail_review_id'] ? (int)$item['detail_review_id'] : null,
            'detail_status' => $item['detail_status'] ?? null,
            'unread_count' => (int)$item['unread_count']
        ];
    }, $rows);
    echo json_encode([
        'status' => 'success', 'data' => $data,
        'pagination' => [
            'total_records' => (int)$totalRecords, 'total_pages' => $totalPages, 'current_page' => $page
        ],
        'rating_counts' => $ratingCounts,
        'stats' => $stats
    ], JSON_UNESCAPED_SLASHES);
} else {
    http_response_code(200);
    echo json_encode([
        'status' => 'success', 'data' => [],
        'pagination' => [
            'total_records' => (int)$totalRecords, 'total_pages' => $totalPages, 'current_page' => $page
        ],
        'rating_counts' => $ratingCounts,
        'stats' => $stats
    ]);
}
$stmt->close();
$conn->close();