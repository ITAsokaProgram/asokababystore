<?php

require_once __DIR__ . '/../../../../aa_kon_sett.php';
require_once __DIR__ . '/../../../auth/middleware_login.php';

header("Content-Type:application/json");
header("Access-Control-Allow-Methods: GET, OPTIONS");
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$headers = getallheaders();
if (!$headers || !isset($headers['Authorization'])) {
    http_response_code(401);
    echo json_encode(['status' => false, 'error' => 'Unauthorized']);
    exit();
}
$authHeader = $headers['Authorization'] ?? '';
$token = null;
if(preg_match('/^Bearer\s(\S+)$/', $authHeader, $matches)) {
    $token = $matches[1];
}
$verify = verify_token($token);


try {
    // Sql Status Trans Hari Ini
    $sql = 'SELECT COUNT(*) FROM hadiah_t WHERE DATE(dibuat_tanggal) = CURDATE()';
    $result = $conn->query($sql);
     // Handle Error State Trans
    if ($result === false) {
        throw new Exception('Database query failed: ' . $conn->error);
    }
    $count = $result->fetch_row()[0];

   
    
    // Sql Status Poin Tukar Hari Ini
    $sqlPoin = 'SELECT COALESCE(SUM(poin_tukar), 0) FROM hadiah_t WHERE DATE(dibuat_tanggal) = CURDATE()';
    $resultPoin = $conn->query($sqlPoin);

    // Handle Error State Poin Tukar
    if ($resultPoin === false) {
        throw new Exception('Database query failed: ' . $conn->error);
    }
    $countPoin = $resultPoin->fetch_row()[0];

    
    
    // Sql Status Claimed Hari Ini
    $sqlClaimed = 'SELECT COUNT(*) FROM hadiah_t WHERE status = "claimed"  AND DATE(dibuat_tanggal) = CURDATE()';
    $resultClaimed = $conn->query($sqlClaimed);
     // Handle Error State Claimed
    if ($resultClaimed === false) {
        throw new Exception('Database query failed: ' . $conn->error);
    }
    $countClaimed = $resultClaimed->fetch_row()[0];

   
    http_response_code(200);
    echo json_encode([
        'status' => true,
        'message' => 'Status cards retrieved successfully',
        'data' => [
            'trans' => (int)$count,
            'poin_tukar' => (int)$countPoin,
            'claimed' => (int)$countClaimed,
        ]
    ]);
} catch(Throwable $e) {
    http_response_code(500);
    echo json_encode(['status' => false, 'error' => $e->getMessage()]);
} finally {
    if (isset($result)) $result->close();
    if (isset($resultPoin)) $resultPoin->close();
    if (isset($resultClaimed)) $resultClaimed->close();
    if (isset($resultAvg)) $resultAvg->close();
    $conn->close();
}