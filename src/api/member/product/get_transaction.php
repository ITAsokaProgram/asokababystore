<?php

require_once __DIR__ . "/../../../../aa_kon_sett.php";
require_once __DIR__ . "/../../../auth/middleware_login.php";

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

// Get data from member

$kd_cust = $_GET['member'] ?? null;
$kd_store = $_GET['cabang'] ?? null;
$no_bon = $_GET['kode'] ?? null;

if ($kd_cust) {
    $sql = "SELECT 
    'Transaksi' AS sumber,
    t.kd_cust,
    DATE_FORMAT(t.tgl_trans, '%d-%m-%Y') AS tanggal,
    t.jam_trs AS jam,
    t.no_bon AS no_trans,
    t.kode_kasir AS USER,
    t.nama_kasir AS kasir,
    ks.Nm_Alias AS cabang,
    (
      SELECT SUM(qty * harga) 
      FROM trans_b t2 
      WHERE t2.no_bon = t.no_bon
    ) AS nominal,
    IFNULL(pk.point_1, 0) + IFNULL(pm.jum_point, 0) - IFNULL(pt.jum_point,0) AS jumlah_point,
    CASE 
      WHEN pk.kd_cust IS NOT NULL THEN 'Detail'
      WHEN pm.kd_cust IS NOT NULL THEN 'Manual'
      ELSE 'Tanpa Poin'
    END AS keterangan_struk
FROM trans_b t
LEFT JOIN (
select kd_cust,sum(point_1) as point_1 from 
point_kasir
group by kd_cust
) pk ON pk.kd_cust = t.kd_cust
LEFT JOIN ( select kd_cust, sum(jum_point) as jum_point from
point_manual
group by kd_cust
) pm ON pm.kd_cust = t.kd_cust
LEFT JOIN ( select kd_cust, sum(jum_point) as jum_point from
point_trans
group by kd_cust
) pt ON pt.kd_cust = t.kd_cust
LEFT JOIN kode_store ks ON t.kd_store = ks.Kd_Store
WHERE t.kd_cust = ?
  AND t.kd_store = ?
  AND t.tgl_trans = CURDATE() - INTERVAL 1 DAY
GROUP BY t.no_bon
ORDER BY t.tgl_trans DESC";

    // Statement for Member
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Server Gagal Memproses']);
        exit;
    }
    $stmt->bind_param("ss", $kd_cust, $kd_store);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        http_response_code(200);
        $data = $result->fetch_all(MYSQLI_ASSOC);
        echo json_encode([
            'status' => 'success',
            'message' => 'Data transaksi Member',
            'detail_transaction' => $data
        ]);
    } else {
        http_response_code(204);
        echo json_encode(['status' => 'success', 'message' => 'Data tidak ditemukan']);
    }
    $stmt->close();
} else {
    // Statement for Non Member
    $sqlNonMember = "SELECT 
    t.no_bon AS kode_transaksi, 
    DATE_FORMAT(t.tgl_trans, '%d-%m-%Y') AS tanggal,
    t.jam_trs,
    t.descp AS nama_item,
    t.qty AS jumlah_item,
    t.harga AS harga_satuan,
    t.harga AS harga_promo,
    t.nama_kasir AS kasir,
    SUM(t.harga*t.qty) AS nominal,
    ks.Nm_Alias AS cabang
FROM trans_b t 
LEFT JOIN kode_store ks ON t.kd_store = ks.kd_store
WHERE t.no_bon = ? GROUP BY plu
ORDER BY t.jam_trs, t.no_bon, t.descp";

    $stmt = $conn->prepare($sqlNonMember);
    $stmt->bind_param("s", $no_bon);
    if (!$stmt) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Server Gagal Memproses']);
        exit;
    }
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        http_response_code(200);
        $dataNonMember = $result->fetch_all(MYSQLI_ASSOC);
        echo json_encode([
            'status' => 'success',
            'message' => 'Data transaksi Non Member',
            'detail_transaction' => $dataNonMember
        ]);
    } else {
        http_response_code(204);
        echo json_encode(['status' => 'success', 'message' => 'Data tidak ditemukan']);
    }
    $stmt->close();
}
$conn->close();






