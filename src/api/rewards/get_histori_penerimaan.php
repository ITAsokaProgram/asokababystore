<?php
require_once __DIR__ . "/../../../aa_kon_sett.php";
require_once __DIR__ . "/../../auth/middleware_login.php";

header("Content-Type:application/json");

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Gunakan metode GET.']);
    exit;
}
$decoded = authenticate_request();


$pageSize = isset($_GET['pageSize']) ? (int)$_GET['pageSize'] : 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $pageSize;

$search = $_GET['search'] ?? '';
$kd_store = $_GET['kd_store'] ?? '';
$tanggal_mulai = $_GET['tanggal_mulai'] ?? '';
$tanggal_selesai = $_GET['tanggal_selesai'] ?? '';

$params = [];
$types = "";


$baseQuery = "FROM hadiah_r hr LEFT JOIN kode_store ks ON hr.kd_store = ks.Kd_Store WHERE 1=1";


$sql = "SELECT hr.no_hdh, hr.plu, hr.nama_hadiah, hr.qty_rec, hr.old_poin, hr.new_poin, hr.kd_store, ks.Nm_Store AS nama_cabang, hr.nama_karyawan, hr.tanggal, hr.jam, hr.ket " . $baseQuery;
$countSql = "SELECT COUNT(*) as total " . $baseQuery;


if (!empty($search)) {
    $searchTerm = "%" . $search . "%";
    $filterCondition = " AND (hr.no_hdh LIKE ? OR hr.plu LIKE ? OR hr.nama_hadiah LIKE ? OR hr.nama_karyawan LIKE ?)";
    $sql .= $filterCondition;
    $countSql .= $filterCondition;
    array_push($params, $searchTerm, $searchTerm, $searchTerm, $searchTerm);
    $types .= "ssss";
}
if (!empty($kd_store)) {
    $sql .= " AND hr.kd_store = ?";
    $countSql .= " AND hr.kd_store = ?";
    $params[] = $kd_store;
    $types .= "s";
}
if (!empty($tanggal_mulai) && !empty($tanggal_selesai)) {
    $sql .= " AND hr.tanggal BETWEEN ? AND ?";
    $countSql .= " AND hr.tanggal BETWEEN ? AND ?";
    $params[] = $tanggal_mulai;
    $params[] = $tanggal_selesai;
    $types .= "ss";
}


$countParams = $params;
$countTypes = $types;

$stmtCount = $conn->prepare($countSql);
if ($stmtCount === false) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'SQL Error (Count): ' . $conn->error]);
    exit;
}
if (!empty($countTypes)) {
    $stmtCount->bind_param($countTypes, ...$countParams);
}
$stmtCount->execute();
$totalRecords = $stmtCount->get_result()->fetch_assoc()['total'];
$totalPages = ceil($totalRecords / $pageSize);
$stmtCount->close();


$sql .= " ORDER BY hr.tanggal DESC, hr.jam DESC LIMIT ?, ?";
$params[] = $offset;
$params[] = $pageSize;
$types .= "ii";

$stmt = $conn->prepare($sql);
if ($stmt === false) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'SQL Error (Data): ' . $conn->error]);
    exit;
}
if (!empty($types)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);


http_response_code(200);
echo json_encode([
    'success' => true,
    'data' => $data,
    'pagination' => [
        'page' => $page,
        'pageSize' => $pageSize,
        'totalRecords' => $totalRecords,
        'totalPages' => $totalPages
    ]
]);

$stmt->close();
$conn->close();