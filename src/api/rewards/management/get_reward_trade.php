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


// Buat mysqli melempar exception supaya try/catch efektif
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $limit = max(1, min(200, (int) ($_GET['limit'] ?? 10)));
    $offset = max(0, (int) ($_GET['offset'] ?? 0));
    $status_filter = $_GET['status'] ?? '';

    // Query untuk menghitung total tanpa limit
    $countSql = "SELECT COUNT(*) as total FROM hadiah_t 
                 LEFT JOIN user_asoka ua ON ua.id_user = hadiah_t.id_user 
                 LEFT JOIN hadiah h ON h.id_hadiah = hadiah_t.id_hadiah";

    $countParams = [];
    $countTypes = "";

    if (!empty($status_filter)) {
        $countSql .= " WHERE hadiah_t.status = ?";
        $countParams[] = $status_filter;
        $countTypes .= "s";
    }

    // Get total count
    $countStmt = $conn->prepare($countSql);
    if (!empty($countParams)) {
        $countStmt->bind_param($countTypes, ...$countParams);
    }
    $countStmt->execute();
    $totalResult = $countStmt->get_result();
    $totalCount = $totalResult->fetch_assoc()['total'];
    $countStmt->close();

    // Query untuk data dengan pagination
    $sql = "SELECT hadiah_t.id,hadiah_t.nama_hadiah,
     hadiah_t.poin_tukar, hadiah_t.status, 
     hadiah_t.expired_at, hadiah_t.ditukar_tanggal, 
     hadiah_t.dibuat_tanggal, hadiah_t.cabang, hadiah_t.kd_store, 
     ua.no_hp AS number_phone, ua.nama_lengkap, h.qty
     FROM hadiah_t 
LEFT JOIN user_asoka ua ON ua.id_user = hadiah_t.id_user
LEFT JOIN hadiah h ON h.id_hadiah = hadiah_t.id_hadiah";

    $params = [];
    $types = "";

    if (!empty($status_filter)) {
        $sql .= " WHERE hadiah_t.status = ?";
        $params[] = $status_filter;
        $types .= "s";
    }

    $sql .= " ORDER BY hadiah_t.dibuat_tanggal DESC LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    $types .= "ii";

    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    if (!$stmt->execute()) {
        http_response_code(500);
        echo json_encode(["error" => "Failed to retrieve data"]);
        exit;
    }
    $result = $stmt->get_result();
    $data = $result->fetch_all(MYSQLI_ASSOC);
    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'message' => $data ? 'Rewards retrieved successfully' : 'No rewards found',
        'data' => $data,
        'meta' => [
            'limit' => $limit,
            'offset' => $offset,
            'total' => $result->num_rows
        ]
    ]);
} catch (mysqli_sql_exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Internal Server Error: ' . $e->getMessage()]);
    exit;
} finally {
    if (isset($stmt) && $stmt instanceof mysqli_stmt) {
        $stmt->close();
    }
    $conn->close();
}
