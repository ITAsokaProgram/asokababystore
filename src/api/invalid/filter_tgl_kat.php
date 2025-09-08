<?php

require_once __DIR__ . "/../../../aa_kon_sett.php";
require_once __DIR__ . "/../../auth/middleware_login.php";
header("Content-Type:application/json");
header("Access-Control-Allow-Methods: GET");

$headers = getallheaders();
if(!isset($headers['Authorization'])){
    http_response_code(401);
    echo json_encode(['status' => "Unauthenticated", 'message' => 'Request ditolak, token tidak ditemukan']);
    exit;
}
$authHeader = $headers['Authorization'];
$token = null;
if (preg_match('/^Bearer\s(\S+)$/', $authHeader, $matches)) {
    $token = $matches[1]; // ini yang aman dan baku
}

$verif = verify_token($token);
$start = $_GET['start'] ?? "";
$end = $_GET['end'] ?? "";
$filter = $_GET['kategori'] ?? "";
$periode = $_GET['periode'] ?? "";
$kd_store = $_GET['cabang'] ?? null;

switch ($periode) {
    case 'harian':
        $format = '%d-%m-%Y';
        break;
    case 'mingguan':
        $format = '%d-%m';
        break;
    case 'bulanan':
        $format = '%m-%Y';
        break;
    case 'tahunan':
        $format = '%Y';
        break;
    default:
        $format = '%d-%m-%Y';
}

if ($kd_store === "all") {
    // SQL tanpa filter cabang
    $sql = "SELECT 
        inv.nama_kasir AS kasir,
        inv.kode_kasir AS kode,
        COUNT(inv.type) AS jml_gagal,
        inv.type AS kategori,
        inv.tgl_trans AS tgl,
        s.Nm_Alias AS cabang,
        DATE_FORMAT(inv.tgl_trans, ?) AS periode,
        ? AS start_periode,
        ? AS end_periode
    FROM invtrans inv
    LEFT JOIN kode_store s ON s.Kd_Store = inv.kode_toko 
    WHERE inv.tgl_trans BETWEEN ? AND ?
      AND inv.type LIKE ?
    GROUP BY inv.kode_kasir
    ORDER BY jml_gagal DESC";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Prepare failed']);
        exit;
    }

    $stmt->bind_param("ssssss", $format, $start, $end, $start, $end, $filter);
} else {
    // Array cabang
    $storeArray = explode(',', $kd_store); 
    $placeholders = implode(',', array_fill(0, count($storeArray), '?'));

    $sql = "SELECT 
        inv.nama_kasir AS kasir,
        inv.kode_kasir AS kode,
        COUNT(inv.type) AS jml_gagal,
        inv.type AS kategori,
        inv.tgl_trans AS tgl,
        s.Nm_Alias AS cabang,
        DATE_FORMAT(inv.tgl_trans, ?) AS periode,
        ? AS start_periode,
        ? AS end_periode
    FROM invtrans inv
    LEFT JOIN kode_store s ON s.Kd_Store = inv.kode_toko 
    WHERE inv.tgl_trans BETWEEN ? AND ?
      AND inv.kode_toko IN ($placeholders)
      AND inv.type LIKE ?
    GROUP BY inv.kode_kasir
    ORDER BY jml_gagal DESC";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Prepare failed']);
        exit;
    }

    $params = array_merge([$format, $start, $end, $start, $end], $storeArray, [$filter]);
    $types = str_repeat('s', count($params));

    $stmt->bind_param($types, ...$params);
}

// Eksekusi dan ambil hasil
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    http_response_code(200);
    $data = $result->fetch_all(MYSQLI_ASSOC);
    echo json_encode(['data' => $data]);
} else {
    http_response_code(204);
    echo json_encode(['status' => 'success', 'message' => 'No data']);
}

$stmt->close();
$conn->close();
