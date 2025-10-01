<?php
require_once __DIR__ . ("/../../../aa_kon_sett.php");
require_once __DIR__ . ("/../../auth/middleware_login.php");
header("Content-Type:application/json");
header("Access-Control-Allow-Methods: GET");
$headers = getallheaders();
$authHeader = $headers['Authorization'];
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
                SUM(IF(sudah_terpecahkan = 0, 1, 0)) as pending_issues 
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
$ratingCounts = [
    'all' => 0, '5' => 0, '4' => 0, '3' => 0, '2' => 0, '1' => 0
];
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
$offset = ($page - 1) * $limit;
$whereClause = "";
$params = [];
$paramTypes = "";
if ($rating !== 'all' && is_numeric($rating)) {
    $whereClause = "WHERE rating = ?";
    $params[] = (int)$rating;
    $paramTypes .= "i";
}
$totalSql = "SELECT COUNT(id) AS total FROM review " . $whereClause;
$totalStmt = $conn->prepare($totalSql);
if ($params) {
    $totalStmt->bind_param($paramTypes, ...$params);
}
$totalStmt->execute();
$totalResult = $totalStmt->get_result()->fetch_assoc();
$totalRecords = $totalResult['total'];
$totalPages = $totalRecords > 0 ? ceil($totalRecords / $limit) : 1;
$sql = "SELECT
    ua.id_user, ua.nama_lengkap AS nama, r.rating, r.komentar, r.kategori,
    r.no_bon AS bon, k.nm_alias AS cabang, r.dibuat_tgl AS tanggal,
    ua.no_hp AS no_hp, t.nama_kasir, rf.path_file AS enpoint_foto,
    r.sudah_terpecahkan as sudah_terpecahkan
FROM (
    SELECT * FROM review " . $whereClause . " ORDER BY dibuat_tgl DESC LIMIT ? OFFSET ?
) AS r
LEFT JOIN user_asoka ua ON ua.id_user = r.id_user
LEFT JOIN kode_store k ON k.kd_store = SUBSTRING(r.no_bon, 1, 4)
LEFT JOIN trans_b t ON t.kode_kasir = SUBSTRING(r.no_bon, 6, 6)
LEFT JOIN review_foto rf ON rf.review_id = r.id
GROUP BY r.no_bon
ORDER BY r.dibuat_tgl DESC";
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
    $row = $result->fetch_all(MYSQLI_ASSOC);
    $data = array_map(function ($item) {
        return [
            'id_user' => $item['id_user'], 'nama' => $item['nama'], 'hp' => $item['no_hp'],
            'rating' => $item['rating'], 'komentar' => $item['komentar'], 'kategori' => $item['kategori'],
            'no_bon' => $item['bon'], 'cabang' => $item['cabang'], 'nama_kasir' => $item['nama_kasir'],
            'enpoint_foto' => $item['enpoint_foto'], 'tanggal' => $item['tanggal'],
            'sudah_terpecahkan' => (bool)$item['sudah_terpecahkan']
        ];
    }, $row);
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