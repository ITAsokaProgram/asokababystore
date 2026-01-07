<?php
header("Content-Type: application/json");
require_once __DIR__ . "/../../../../aa_kon_sett.php";
require_once __DIR__ . '/../../../auth/middleware_login.php';
try {
    $sql = "SELECT kota, COUNT(*) AS jumlah_customer
            FROM customers
            WHERE kota != '' AND kota IS NOT NULL
            GROUP BY kota
            ORDER BY jumlah_customer DESC";
    $res = $conn->query($sql);
    $data = [];
    while ($row = $res->fetch_assoc()) {
        $data[] = [
            'kota' => strtoupper($row['kota'])
        ];
    }
    echo json_encode([
        "success" => true,
        "data" => $data
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Terjadi kesalahan: " . $e->getMessage()
    ]);
}
?>