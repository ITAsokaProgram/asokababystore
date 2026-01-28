<?php

require_once __DIR__ . "/../../../aa_kon_sett.php";
require_once __DIR__ . "/../../auth/middleware_login.php";

header("Content-Type:application/json");
header("Access-Control-Allow-Methods: GET");


if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Gunakan metode GET.']);
    exit;
}

$verif = authenticate_request();


$pageSize = isset($_GET['pageSize']) ? (int)$_GET['pageSize'] : 10;
$offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;


$pageSize = max(1, min(100, $pageSize));
$offset = max(0, $offset);


$sql = "SELECT 
    h.id_hadiah,
    h.kode_karyawan,
    h.nama_karyawan,
    h.plu,
    h.nama_hadiah,
    h.kd_store,
    h.poin,
    h.qty,
    h.tanggal_dibuat,
    h.tanggal_diubah,
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
            SELECT GROUP_CONCAT(ks3.nm_alias ORDER BY ks3.nm_alias SEPARATOR ', ')
            FROM kode_store ks3
            WHERE FIND_IN_SET(ks3.kd_store, h.kd_store)
        )
    END AS nm_alias
FROM hadiah h
LIMIT ?, ?
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $offset, $pageSize);
$stmt->execute();

$result = $stmt->get_result();


$countSql = "SELECT COUNT(*) as total FROM hadiah";
$countStmt = $conn->prepare($countSql);
$countStmt->execute();
$countResult = $countStmt->get_result();

if ($result->num_rows > 0 && $countResult->num_rows > 0) {
    $result = $result->fetch_all(MYSQLI_ASSOC);
    $countData = $countResult->fetch_assoc()['total'];
    http_response_code(200);
    echo json_encode(['success' => true, 'data' => $result, 'total' => $countData, 'offset' => $offset, 'pageSize' => $pageSize]);
} else {
    http_response_code(200);
    echo json_encode(['success' => true, 'message' => 'Data tidak ditemukan', 'data' => []]);
}
$stmt->close();

$countStmt->close();

$conn->close();
