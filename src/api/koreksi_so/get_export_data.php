<?php
// src/api/koreksi_so/get_export_data.php
session_start();
include '../../../aa_kon_sett.php';
header('Content-Type: application/json');

try {
    $tgl_mulai = $_GET['tgl_mulai'] ?? date('Y-m-01');
    $tgl_selesai = $_GET['tgl_selesai'] ?? date('Y-m-d');
    $kd_store = $_GET['kd_store'] ?? 'all';

    // Query untuk mengambil detail lengkap (flat data)
    $where = "DATE(tgl_koreksi) BETWEEN ? AND ?";
    $params = ['ss', $tgl_mulai, $tgl_selesai];

    if ($kd_store != 'all') {
        $where .= " AND kd_store = ?";
        $params[0] .= 's';
        $params[] = $kd_store;
    }

    $sql = "
        SELECT 
            tgl_koreksi,
            kd_store,
            kode_supp,
            no_faktur,
            no_kor,
            acc_kor,
            nama_kar,
            plu,
            deskripsi,
            sel_qty,
            avg_cost,
            ppn_kor,
            ((avg_cost + ppn_kor) * sel_qty) as total_row
        FROM koreksi_so 
        WHERE $where
        ORDER BY tgl_koreksi ASC, kode_supp ASC, no_faktur ASC
    ";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Query Error: " . $conn->error);
    }

    $stmt->bind_param(...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    $data = [];
    while ($row = $result->fetch_assoc()) {
        // Pastikan tipe data angka benar untuk Excel
        $row['sel_qty'] = (float) $row['sel_qty'];
        $row['avg_cost'] = (float) $row['avg_cost'];
        $row['ppn_kor'] = (float) $row['ppn_kor'];
        $row['total_row'] = (float) $row['total_row'];
        $data[] = $row;
    }

    echo json_encode(['success' => true, 'data' => $data]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>