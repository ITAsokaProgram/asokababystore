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


$headers = getallheaders();
$authHeader = $headers['Authorization'] ?? null;
if (!$authHeader || !preg_match('/^Bearer\s(\S+)$/', $authHeader, $matches)) {
    http_response_code(401);
    echo json_encode(['status' => "Unauthenticated", 'message' => 'Request ditolak user tidak terdaftar']);
    exit;
}
$token = $matches[1];
$verif = verify_token($token);


$pageSize = isset($_GET['pageSize']) ? (int)$_GET['pageSize'] : 10;
$page     = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$pageSize = max(1, min(100, $pageSize));
$page     = max(1, $page);
$offset   = ($page - 1) * $pageSize;


$branch = isset($_GET['branch']) ? trim($_GET['branch']) : '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$where = "WHERE 1=1";
$params = [];
$types  = "";


if ($branch !== '') {
    $where .= " AND FIND_IN_SET(?, h.kd_store)";
    $params[] = $branch;
    $types   .= "s";
}


if ($search !== '') {
    $where .= " AND (h.nama_hadiah LIKE ? OR h.plu LIKE ? OR h.nama_karyawan LIKE ?)";
    $searchLike = "%".$search."%";
    $params[] = $searchLike;
    $params[] = $searchLike;
    $params[] = $searchLike;
    $types   .= "sss";
}


$countSql = "SELECT COUNT(*) as total FROM hadiah h $where";
$countStmt = $conn->prepare($countSql);
if (!empty($params)) {
    $countStmt->bind_param($types, ...$params);
}
$countStmt->execute();
$countResult = $countStmt->get_result();
$total = $countResult->fetch_assoc()['total'] ?? 0;
$countStmt->close();


$sql = "SELECT 
    h.id_hadiah,
    h.kode_karyawan,
    h.nama_karyawan,
    h.plu,
    h.nama_hadiah,
    h.poin,
    h.qty,
    h.kd_store,
    h.tanggal_dibuat,
    h.tanggal_diubah,
    CASE 
        WHEN (
            SELECT COUNT(*) FROM kode_store
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
$where
ORDER BY h.tanggal_dibuat DESC
LIMIT ?, ?";

$stmt = $conn->prepare($sql);
$bindTypes = $types . "ii";
$paramsWithLimit = array_merge($params, [$offset, $pageSize]);
$stmt->bind_param($bindTypes, ...$paramsWithLimit);

$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_all(MYSQLI_ASSOC);

$stmt->close();
$conn->close();


http_response_code(200);
echo json_encode([
    'success'     => true,
    'data'        => $data,
    'total'       => $total,
    'page'        => $page,
    'pageSize'    => $pageSize,
    'totalPages'  => $total > 0 ? ceil($total / $pageSize) : 0
], JSON_UNESCAPED_UNICODE);
