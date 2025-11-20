<?php
include '../../../aa_kon_sett.php';
header('Content-Type: application/json');
try {
    $tgl = $_GET['tgl'];
    $supp = $_GET['supp'];
    $kd_store = $_GET['store'] ?? 'all';
    $where = "DATE(tgl_koreksi) = ? AND kode_supp = ?";
    $params = ['ss', $tgl, $supp];
    if ($kd_store != 'all') {
        $where .= " AND kd_store = ?";
        $params[0] .= 's';
        $params[] = $kd_store;
    }
    $sql = "
        SELECT 
            no_faktur, 
            no_kor, 
            acc_kor, 
            nama_kar, 
            plu, 
            deskripsi as `desc`, 
            sel_qty, 
            avg_cost, 
            ppn_kor,
            ((avg_cost + ppn_kor) * sel_qty) as total_row
        FROM koreksi_so 
        WHERE $where
    ";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Query Error: " . $conn->error);
    }
    $stmt->bind_param(...$params);
    $stmt->execute();
    $res = $stmt->get_result();
    $data = [];
    while ($row = $res->fetch_assoc()) {
        $data[] = $row;
    }
    echo json_encode($data);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>