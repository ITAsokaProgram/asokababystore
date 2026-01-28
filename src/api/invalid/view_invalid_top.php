<?php

require_once __DIR__ . "/../../../aa_kon_sett.php";
require_once __DIR__ . "/../../auth/middleware_login.php";
header("Content-Type:application/json");
header("Access-Control-Allow-Methods: GET");

$verif = authenticate_request();

$sql = "SELECT 
    inv.nama_kasir AS kasir,
    inv.kode_kasir AS kode,
    inv.type AS kategori,
    COUNT(inv.type) AS jml_gagal,
    s.Nm_Alias AS cabang
FROM invtrans inv
LEFT JOIN kode_store s ON s.Kd_Store = inv.kode_toko 
WHERE inv.type LIKE '%VOID%' AND inv.tgl_trans BETWEEN CURDATE() - INTERVAL 1 DAY AND CURDATE() AND NOT inv.nama_kasir LIKE 'PROMO%'
GROUP BY inv.kode_kasir ORDER BY jml_gagal DESC limit 5";

$sqlRetur = "SELECT 
    inv.nama_kasir AS kasir,
    inv.kode_kasir AS kode,
    inv.type AS kategori,
    COUNT(inv.type) AS jml_gagal,
    s.Nm_Alias AS cabang
FROM invtrans inv
LEFT JOIN kode_store s ON s.Kd_Store = inv.kode_toko 
WHERE inv.type LIKE '%RETUR%' AND inv.tgl_trans BETWEEN CURDATE() - INTERVAL 1 DAY AND CURDATE()
GROUP BY inv.kode_kasir ORDER BY jml_gagal DESC limit 5";

$result = $conn->query($sql);
$data = $result->fetch_all(MYSQLI_ASSOC);
$count = $result->num_rows;
$result->close();
$resultRetur = $conn->query($sqlRetur);
$dataRetur = $resultRetur->fetch_all(MYSQLI_ASSOC);
$countRetur = $resultRetur->num_rows;
$resultRetur->close();



if ($count > 0 || $countRetur > 0) {
    echo json_encode([
        'status' => 'success',
        'message' => 'Data berhasil diambil',
        'data' => $data,
        'dataRetur' => $dataRetur
    ]);
} else {
    // Tetap kirim array kosong agar JS tidak error saat melakukan .length atau .slice
    echo json_encode([
        'status' => 'success',
        'message' => 'Tidak ada data invalid transaksi',
        'data' => [],
        'dataRetur' => []
    ]);
}
$conn->close();
exit;