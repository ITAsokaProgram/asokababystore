<?php
header("Content-Type: application/json");
require_once __DIR__ . "/../../../../aa_kon_sett.php"; // koneksi $conn

try {
    // query sebaran member aktif 3 bulan terakhir
    $sql = "SELECT c.kota, 
       COUNT(DISTINCT c.kd_cust) AS total_member,
       ROUND(
           COUNT(DISTINCT c.kd_cust) * 100.0 / 
           (SELECT COUNT(DISTINCT kd_cust) 
            FROM trans_b 
            WHERE tgl_trans >= CURDATE() - INTERVAL 3 MONTH), 
           2
       ) AS persen
FROM customers c
JOIN trans_b t ON t.kd_cust = c.kd_cust
WHERE t.tgl_trans >= CURDATE() - INTERVAL 3 MONTH
  AND c.kota IS NOT NULL
  AND c.kota <> ''
GROUP BY c.kota
ORDER BY total_member DESC
LIMIT 10"; 

    $res = $conn->query($sql);

    $data = [];
    while ($row = $res->fetch_assoc()) {
        $data[] = [
            "kota"   => $row['kota'] ?? "Tidak Diketahui",
            "total"  => (int)$row['total_member'],
            "persen" => (float)$row['persen']
        ];
    }

    echo json_encode([
        "success" => true,
        "data"    => $data
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Terjadi kesalahan: " . $e->getMessage()
    ]);
}
