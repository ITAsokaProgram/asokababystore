<?php
require_once "../../../aa_kon_sett.php";
require_once "../../auth/middleware_login.php";
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
try {
    $sql = "
    SELECT
        c.nama_cust,
        top_cust.Total_Trans AS T_Trans,
        (SELECT 
             GROUP_CONCAT(DISTINCT ks.Nm_Alias ORDER BY ks.Nm_Alias SEPARATOR ', ')
         FROM point_kasir pk
         LEFT JOIN kode_store ks ON pk.kd_store = ks.kd_store
         WHERE pk.kd_cust = top_cust.kd_cust
           AND pk.tanggal BETWEEN CURDATE() - INTERVAL 1 DAY AND CURDATE()
        ) AS cabang,
        (SELECT nama_kasir
         FROM point_kasir pk
         WHERE pk.kd_cust = top_cust.kd_cust
           AND pk.tanggal BETWEEN CURDATE() - INTERVAL 1 DAY AND CURDATE()
         LIMIT 1
        ) AS kasir
    FROM (
        SELECT
            kd_cust,
            SUM(T_Trans) AS Total_Trans
        FROM (
            (SELECT 
                 kd_cust, 
                 COUNT(*) AS T_Trans
             FROM point_kasir
             WHERE tanggal BETWEEN CURDATE() - INTERVAL 1 DAY AND CURDATE()
             GROUP BY kd_cust)
            UNION ALL
            (SELECT 
                 kd_cust, 
                 COUNT(*) AS T_Trans
             FROM point_manual
             WHERE tgl_trans BETWEEN CURDATE() - INTERVAL 1 DAY AND CURDATE()
             GROUP BY kd_cust)
        ) AS all_trans
        WHERE kd_cust IS NOT NULL AND kd_cust != ''
        GROUP BY kd_cust
        ORDER BY Total_Trans DESC
        LIMIT 3
    ) AS top_cust
    JOIN customers c ON top_cust.kd_cust = c.kd_cust
    ORDER BY top_cust.Total_Trans DESC;
    ";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_all(MYSQLI_ASSOC);
    $count = $result->num_rows;
    if ($count > 0) {
        http_response_code(200);
        echo json_encode(['status' => true, 'message' => 'Data berhasil diambil', 'data' => $data]);
    } else {
        http_response_code(200);
        echo json_encode(['status' => false, 'message' => 'Data tidak ditemukan', 'data' => []]);
    }
} catch (\Throwable $th) {
    http_response_code(500);
    echo json_encode(['status' => false, 'message' => 'Server Error: ' . $th->getMessage()]);
}
$stmt->close();
$conn->close();
?>