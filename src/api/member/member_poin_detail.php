<?php
require_once __DIR__ . "/../../../aa_kon_sett.php";
require_once __DIR__ . "/../../auth/middleware_login.php";
header("Content-Type:application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");

if ($_SERVER['REQUEST_METHOD'] != 'GET') {
    http_response_code(405);
    echo json_encode(['status' => false, 'message' => 'Request ditolak method tidak terdaftar']);
    exit;
}

$headers = getallheaders();
if (!isset($headers['Authorization']) || empty($headers['Authorization'])) {
    http_response_code(401);
    echo json_encode(['status' => false, 'message' => 'Request ditolak user tidak terdaftar']);
    exit;
}
$authHeader = $headers['Authorization'];
$token = null;
if (preg_match('/^Bearer\s(\S+)$/', $authHeader, $matches)) {
    $token = $matches[1];
}
if (!$token) {
    http_response_code(401);
    echo json_encode(['status' => false, 'message' => 'Request ditolak user tidak terdaftar']);
    exit;
}
$verif = verify_token($token);


$kd_cust = $_GET['kd_cust'];
$sql = "SELECT 
    'Kasir' AS sumber,
    pk.kd_cust,
    DATE_FORMAT(pk.tanggal, '%d-%m-%Y') AS tanggal,
    pk.jam,
    pk.no_faktur AS no_trans,
    pk.kode_kasir AS USER,
    pk.nama_kasir AS kasir,
    ks.Nm_Alias AS cabang,
    pk.point_1 AS jumlah_point,
    pk.belanja AS nominal,
    'Detail' AS keterangan_struk
FROM point_kasir pk
LEFT JOIN kode_store ks ON pk.kd_store = ks.Kd_Store
WHERE pk.kd_cust = ?

UNION ALL

SELECT 
    'Back Office' AS sumber,
    pm.kd_cust,
    DATE_FORMAT(pm.tgl_trans, '%d-%m-%Y') AS tanggal,
    pm.jam,
    pm.no_trans,
    pm.kd_user AS USER,
    NULL AS kasir,
    ks.Nm_Alias AS cabang,
    pm.jum_point AS jumlah_point,
    pm.belanja AS nominal,
    'Manual' AS keterangan_struk
FROM point_manual pm
LEFT JOIN kode_store ks ON pm.kd_store = ks.kd_store
WHERE pm.kd_cust = ?


UNION ALL

SELECT 
    'Penukaran' AS sumber,
    pt.kd_cust,
    DATE_FORMAT(pt.tgl_trans, '%d-%m-%Y') AS tanggal,
    pt.jam,
    pt.no_trans,
    pt.kd_user AS USER,
    NULL AS kasir,
    ks.Nm_Alias AS cabang,
    -pt.jum_point AS jumlah_point,
    0 AS nominal,
    'Tukar Poin' AS keterangan_struk
FROM point_trans pt
LEFT JOIN kode_store ks ON pt.kd_store = ks.kd_store
WHERE pt.kd_cust = ?
ORDER BY tanggal DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param('sss',$kd_cust,$kd_cust,$kd_cust);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

echo json_encode(['status'=>'true','message'=>'Success Fetch', 'data'=>$row]);
$conn->close();