<?php
include '../../../aa_kon_sett.php';
header("Content-Type: application/json");
$kemarin = date('Y-m-d', strtotime('yesterday'));
$weakData = date('Y-m-d', strtotime('1 week ago'));
$monthData = date('Y-m-d', strtotime('1 month ago'));
$yearData = date('Y-m-d', strtotime('1 year ago'));
$filter = $_GET['filter'] ?? 'per_jam';
switch ($filter) {
    case 'per_jam':
        $sql = "
            SELECT 
                CONCAT(LPAD(HOUR(jam_trs), 2, '0'), ':00 WIB') AS label,
                CONCAT('Rp ', FORMAT(SUM(hrg_promo * qty), 0)) AS total_pendapatan
            FROM trans_b
            WHERE tgl_trans = '$kemarin'
            GROUP BY HOUR(jam_trs)
        ";
        break;

    case '7_hari':
        $sql = "
                SELECT 
                DATE(tgl_trans) AS label,
                CONCAT('Rp ', FORMAT(SUM(hrg_promo * qty), 0)) AS total_pendapatan
            FROM trans_b
            WHERE tgl_trans BETWEEN '$weakData' AND '$kemarin'
            GROUP BY tgl_trans
        ";
        break;

    case '30_hari':
        $sql = "
            SELECT 
                DATE(tgl_trans) AS label,
                CONCAT('Rp ', FORMAT(SUM(hrg_promo * qty), 0)) AS total_pendapatan
            FROM trans_b
            WHERE tgl_trans BETWEEN '$monthData' AND '$kemarin'
            GROUP BY DATE(tgl_trans)
        ";
        break;

    case '12_bulan':
        $sql = "
            SELECT 
  DATE_FORMAT(tgl_trans, '%Y-%m') AS label,
  CONCAT('Rp ', FORMAT(SUM(hrg_promo * qty), 0)) AS total_pendapatan
FROM trans_b
WHERE tgl_trans BETWEEN '$yearData' AND '$kemarin'
GROUP BY DATE_FORMAT(tgl_trans, '%Y-%m')
ORDER BY label;
        ";
        break;

    default:
        echo json_encode(['error' => 'Invalid filter']);
        exit;
}

$stmt = $conn->query($sql);
$data = $stmt->fetch_all(MYSQLI_ASSOC);

echo json_encode(['data' => $data]);