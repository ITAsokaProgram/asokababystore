<?php


require_once __DIR__ . '/../../../aa_kon_sett.php';
require_once __DIR__ . '/../../auth/middleware_login.php';
require_once __DIR__ . '/../../utils/Logger.php';

header("Content-Type:application/json");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Authorization");

// Init logger
$logger = new AppLogger('get_hadiah_pubs.log');

if($_SERVER['REQUEST_METHOD'] != 'GET') {
    http_response_code(405);
    echo json_encode(['status' => false, 'message' => 'Request ditolak method tidak terdaftar']);
    $logger->warning('Request method not allowed', ['method' => $_SERVER['REQUEST_METHOD']]);
    exit;
}
$header = getAllHeaders();

if(!isset($header['Authorization']) || $header['Authorization'] == null) {
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


// JWT validation only
$user_id = null;
try {
    $verif = verify_token($token);
    $user_id = $verif->id ?? null;
} catch (Exception $e) {
    $logger->error('Token verification failed: ' . $e->getMessage());
    http_response_code(401);
    echo json_encode(['status' => false, 'message' => 'Token tidak valid']);
    exit;
}

$sql = "SELECT 
    h.id_hadiah AS id,
    h.nama_hadiah,
    h.image_url AS link_gambar,
    h.qty AS stok,
    h.poin AS points,
    h.plu,

    -- Ambil 1 kd_store saja (yang paling kecil)
    CASE 
        WHEN (
            SELECT COUNT(*) 
            FROM kode_store
        ) = (
            SELECT COUNT(*) 
            FROM kode_store ks2
            WHERE FIND_IN_SET(ks2.kd_store, h.kd_store)
        ) THEN 'Semua Cabang'
        ELSE (
            SELECT MIN(ks3.kd_store)
            FROM kode_store ks3
            WHERE FIND_IN_SET(ks3.kd_store, h.kd_store)
        )
    END AS store,

    -- Tetap tampilkan nm_store gabungan
    CASE 
        WHEN (
            SELECT COUNT(*) 
            FROM kode_store
        ) = (
            SELECT COUNT(*) 
            FROM kode_store ks2
            WHERE FIND_IN_SET(ks2.kd_store, h.kd_store)
        ) THEN 'Semua Cabang'
        ELSE (
            SELECT GROUP_CONCAT(ks3.nm_store ORDER BY ks3.nm_store SEPARATOR ', ')
            FROM kode_store ks3
            WHERE FIND_IN_SET(ks3.kd_store, h.kd_store)
        )
    END AS nm_store

FROM hadiah h";
;
$stmt = $conn->prepare($sql);
$stmt->execute();
if (!$stmt) {
    http_response_code(500);
    echo json_encode(["status" => false, "message" => "Server bermasalah"]);
    $logger->error('SQL prepare/execute failed: ' . $sql);
}
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    http_response_code(200);
    $data = json_encode(["status" => true,"message"=> "Data ditemukan", "data" => $result->fetch_all(MYSQLI_ASSOC)]);
    echo $data;
    $logger->info('Hadiah data ditemukan, count: ' . $result->num_rows . ', user_id: ' . $user_id);
} else {
    http_response_code(200);
    echo json_encode(["status"=> true,"message"=> "Data kosong", 'data' => []]);
    $logger->info('Hadiah data kosong untuk user_id: ' . $user_id);
}
$stmt->close();
$conn->close();
