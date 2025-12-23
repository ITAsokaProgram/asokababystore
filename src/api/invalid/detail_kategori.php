<?php
require_once __DIR__ . "/../../../aa_kon_sett.php";
require_once __DIR__ . "/../../auth/middleware_login.php";
header("Content-Type:application/json");
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
$kode = $_GET['kode'] ?? "";
$filter = $_GET['kategori'] ?? "";
$start = $_GET['start'] ?? "";
$end = $_GET["end"] ?? "";
$cabang = $_GET['cabang'] ?? "";
$sql = "SELECT inv.plu AS barcode, 
    inv.descp AS nama_product, 
    inv.no_bon AS no_trans, 
    inv.jam_trs AS jam, 
    inv.kode_toko,
    inv.nama_kasir AS kasir,
    inv.kode_kasir AS kode,
    inv.tgl_trans AS tgl,
    inv.keterangan AS ket,
    inv.type AS kategori,
    inv.ket_cek,
    inv.nama_cek,
    s.Nm_Alias AS cabang
FROM invtrans inv
LEFT JOIN kode_store s ON s.Kd_Store = inv.kode_toko 
WHERE inv.kode_kasir = ? AND inv.type LIKE ? AND inv.tgl_trans BETWEEN ? AND ? ";
$params = [$kode, $filter, $start, $end];
$types = "ssss";
if (!empty($cabang) && $cabang !== 'all') {
    $storeArray = explode(',', $cabang);
    $placeholders = implode(',', array_fill(0, count($storeArray), '?'));
    $sql .= " AND inv.kode_toko IN ($placeholders) ";
    $params = array_merge($params, $storeArray);
    $types .= str_repeat('s', count($storeArray));
}
$sql .= " ORDER BY tgl DESC, jam DESC;";
$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Server Error']);
    exit;
}
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    http_response_code(200);
    $data = json_encode(['data' => $result->fetch_all(MYSQLI_ASSOC)]);
    echo $data;
} else {
    http_response_code(204);
    echo json_encode(['status' => 'success', 'message' => 'No data']);
}
$stmt->close();
$conn->close();
?>