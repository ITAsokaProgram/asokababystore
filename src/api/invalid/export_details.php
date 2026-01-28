<?php
require_once __DIR__ . "/../../../aa_kon_sett.php";
require_once __DIR__ . "/../../auth/middleware_login.php";
header("Content-Type:application/json");
header("Access-Control-Allow-Methods: GET");
$verif = authenticate_request();
$cabang = isset($_GET['cabang']) ? $_GET['cabang'] : '';
$sql = "SELECT 
            inv.DESCP as nama_product,
            inv.no_bon,
            inv.jam_trs as jam,
            inv.kode_kasir,
            inv.nama_kasir,
            s.Nm_Alias as cabang,
            inv.tgl_trans as tanggal,
            inv.keterangan,
            inv.qty,
            inv.harga,
            inv.type
        FROM invtrans inv
        LEFT JOIN kode_store s ON s.Kd_Store = inv.kode_toko 
        WHERE inv.type LIKE '%VOID%' 
        AND inv.tgl_trans BETWEEN CURDATE() - INTERVAL 1 DAY AND CURDATE() 
        AND NOT inv.nama_kasir LIKE 'PROMO%'";
if (!empty($cabang) && $cabang !== 'all') {
    $cabangClean = $conn->real_escape_string($cabang);
    $sql .= " AND inv.kode_toko = '$cabangClean'";
}
$sql .= " ORDER BY inv.tgl_trans DESC, inv.jam_trs DESC";
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_all(MYSQLI_ASSOC);
if ($data) {
    echo json_encode(['status' => 'success', 'data' => $data]);
} else {
    echo json_encode(['status' => 'success', 'data' => []]);
}
$stmt->close();
$conn->close();