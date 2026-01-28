<?php

require_once __DIR__ . '/../../../../aa_kon_sett.php';
require_once __DIR__ . '/../../../auth/middleware_login.php';

header("Content-Type:application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed"]);
    exit;
}

$verify = authenticate_request();



try {
    $filter = json_decode(file_get_contents('php://input'), true);
    $start = $filter['start'] ?? null;
    $end = $filter['end'] ?? null;
    $limit = max(1, min(200, (int)($filter['limit'] ?? 10)));
    $offset = max(0, (int)($filter['offset'] ?? 0));
    $kd_store = $filter['kd_store'] ?? [];
    if (!is_array($kd_store)) {
        $kd_store = $kd_store ? [$kd_store] : [];
    }
    $placeholders = $kd_store ? implode(',', array_fill(0, count($kd_store), '?')) : '';
    if(!$start || !$end) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Start date and end date are required"]);
        exit;
    }

    // Query untuk menghitung total data
    $countSql = "SELECT COUNT(*) as total FROM hadiah_t 
                 LEFT JOIN user_asoka ua ON ua.id_user = hadiah_t.id_user 
                 LEFT JOIN hadiah h ON h.id_hadiah = hadiah_t.id_hadiah
                 WHERE hadiah_t.dibuat_tanggal BETWEEN ? AND ?";
    $countParams = [$start, $end];
    $countTypes = "ss";
    if (!empty($kd_store)) {
        $countSql .= " AND hadiah_t.kd_store IN ($placeholders)";
        $countParams = array_merge($countParams, $kd_store);
        $countTypes .= str_repeat("s", count($kd_store));
    }
    
    // Get total count
    $countStmt = $conn->prepare($countSql);
    $countStmt->bind_param($countTypes, ...$countParams);
    $countStmt->execute();
    $totalResult = $countStmt->get_result();
    $totalCount = $totalResult->fetch_assoc()['total'];
    $countStmt->close();

    // Query untuk mendapatkan data dengan pagination
    $sql = "SELECT hadiah_t.id,hadiah_t.nama_hadiah,
     hadiah_t.poin_tukar, hadiah_t.status, 
     hadiah_t.expired_at, hadiah_t.ditukar_tanggal, 
     hadiah_t.dibuat_tanggal, hadiah_t.kd_store, 
     ua.no_hp AS number_phone, ua.nama_lengkap, h.qty
     FROM hadiah_t 
LEFT JOIN user_asoka ua ON ua.id_user = hadiah_t.id_user
LEFT JOIN hadiah h ON h.id_hadiah = hadiah_t.id_hadiah
WHERE hadiah_t.dibuat_tanggal BETWEEN ? AND ?";
    $params = [$start, $end];
    $types = "ss";
    if (!empty($kd_store)) {
        $sql .= " AND hadiah_t.kd_store IN ($placeholders)";
        $params = array_merge($params, $kd_store);
        $types .= str_repeat("s", count($kd_store));
    }
    $sql .= " ORDER BY hadiah_t.dibuat_tanggal DESC LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    $types .= "ii";

    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    // Pastikan jumlah params dan types sama
    if (strlen($types) !== count($params)) {
        throw new Exception("Parameter count does not match types: " . strlen($types) . " vs " . count($params));
    }
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
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
            'total' => $totalCount,
            'count' => count($data),
            'filters' => [
                'start_date' => $start,
                'end_date' => $end,
                'kd_store' => $kd_store
            ]
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Server error: " . $e->getMessage()]);
    exit;
}
