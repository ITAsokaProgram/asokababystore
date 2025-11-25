<?php
require_once __DIR__ . '/../../../aa_kon_sett.php';
require_once __DIR__ . '/../../auth/middleware_login.php';

header("Access-Control-Allow-Methods: GET");
header('Content-Type: application/json');

// Get token from Authorization header
$headers = getallheaders();
$auth_header = $headers['Authorization'] ?? '';
$token = str_replace('Bearer ', '', $auth_header);

if (!$token) {
    http_response_code(401);
    echo json_encode(['status' => false, 'message' => 'Request ditolak, token tidak ada']);
    exit;
}

$verif = verify_token($token);
if (!$verif) {
    http_response_code(401);
    echo json_encode(['status' => false, 'message' => 'Token tidak valid']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['status' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    $sql = "SELECT Kd_Store, Nm_Alias FROM kode_store WHERE display = 'on' ORDER BY Nm_Alias ASC";
    $result = $conn->query($sql);

    $stores = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $stores[] = $row;
        }
    }

    echo json_encode(['status' => true, 'data' => $stores]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => false,
        'message' => 'Internal Server Error: ' . $e->getMessage()
    ]);
}
?>