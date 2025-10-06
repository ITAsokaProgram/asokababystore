<?php
require_once __DIR__ . "/../../../aa_kon_sett.php";
require_once __DIR__ . "/../../auth/middleware_login.php";
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: GET");


$headers = getallheaders();
if (!isset($headers['Authorization'])) {
    http_response_code(401);
    echo json_encode(['status' => "Unauthenticated", 'message' => 'Request ditolak, token tidak ditemukan']);
    exit;
}
$authHeader = $headers['Authorization'];
$token = null;
if (preg_match('/^Bearer\s(\S+)$/', $authHeader, $matches)) {
    $token = $matches[1];
}
$verif = verify_token($token); 


$sqlStats = "SELECT 
    ROUND(AVG(rating), 1) AS avg_rating,
    COUNT(*) AS total_reviews,
    SUM(CASE WHEN sudah_terpecahkan = 0 OR sudah_terpecahkan IS NULL THEN 1 ELSE 0 END) AS pending_count
FROM review";



$sqlFeatured = "
SELECT
    r.id,
    r.rating,
    ua.nama_lengkap AS nama_customer,
    COALESCE(rd.status, 'pending') AS review_status, 
    r.dibuat_tgl
FROM
    review r
LEFT JOIN
    user_asoka ua ON r.id_user = ua.id_user
LEFT JOIN
    review_detail rd ON r.id = rd.review_id
WHERE
    r.rating <= 3 
ORDER BY
    
    CASE
        WHEN rd.status IS NULL OR rd.status = 'pending' THEN 1 
        WHEN rd.status = 'in_progress' THEN 2 
        WHEN rd.status = 'resolved' THEN 3 
        ELSE 4 
    END ASC,
    
    r.dibuat_tgl DESC
LIMIT 1;
";


try {
    
    $stmtStats = $conn->prepare($sqlStats);
    $stmtStats->execute();
    $resultStats = $stmtStats->get_result();
    $stats = $resultStats->fetch_assoc();
    $stmtStats->close();

    
    $stmtFeatured = $conn->prepare($sqlFeatured);
    $stmtFeatured->execute();
    $resultFeatured = $stmtFeatured->get_result();
    $featuredReview = $resultFeatured->fetch_assoc(); 
    $stmtFeatured->close();

    
    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'message' => 'Data berhasil diambil',
        'data' => [
            'featured_review' => $featuredReview, 
            'avg_rating' => $stats['avg_rating'] ?? 0,
            'total_reviews' => (int)($stats['total_reviews'] ?? 0),
            'pending_count' => (int)($stats['pending_count'] ?? 0)
        ]
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Terjadi kesalahan: ' . $e->getMessage()
    ]);
}

$conn->close();
?>