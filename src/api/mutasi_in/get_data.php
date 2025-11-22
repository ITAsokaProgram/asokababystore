<?php
header('Content-Type: application/json');
include '../../../aa_kon_sett.php';
try {
    $tgl_mulai = $_GET['tgl_mulai'] ?? date('Y-m-d');
    $tgl_selesai = $_GET['tgl_selesai'] ?? date('Y-m-d');
    $kd_store = $_GET['kd_store'] ?? 'all';
    $status_cetak = $_GET['status_cetak'] ?? 'all';
    $status_terima = $_GET['status_terima'] ?? 'all';
    $is_export = isset($_GET['export']) && $_GET['export'] === 'true';
    $page = (int) ($_GET['page'] ?? 1);
    if ($page < 1) $page = 1;
    $limit = 10;
    $offset = ($page - 1) * $limit;
    $where = "DATE(mi.tgl_mutasi) BETWEEN ? AND ?";
    $params = [$tgl_mulai, $tgl_selesai];
    $types = "ss";
    if ($kd_store !== 'all') {
        $where .= " AND (mi.kode_tujuan = ? OR mi.kode_dari = ?)";
        $params[] = $kd_store;
        $params[] = $kd_store;
        $types .= "ss";
    }
    if ($status_cetak !== 'all') {
        $where .= " AND mi.cetak = ?";
        $params[] = $status_cetak;
        $types .= "s";
    }
    if ($status_terima !== 'all') {
        $where .= " AND mi.receipt = ?";
        $params[] = $status_terima;
        $types .= "s";
    }
    $querySummary = "
        SELECT 
            SUM(mi.qty) as total_qty,
            SUM(mi.qty * mi.netto) as total_netto,
            SUM(mi.qty * mi.ppn) as total_ppn,
            SUM(mi.qty * (mi.netto + mi.ppn)) as total_grand
        FROM mutasi_in_copy mi
        WHERE $where
    ";
    $stmtSummary = $conn->prepare($querySummary);
    $stmtSummary->bind_param($types, ...$params);
    $stmtSummary->execute();
    $resSummary = $stmtSummary->get_result()->fetch_assoc();
    $summary = [
        'total_qty' => $resSummary['total_qty'] ?? 0,
        'total_netto' => $resSummary['total_netto'] ?? 0,
        'total_ppn' => $resSummary['total_ppn'] ?? 0,
        'total_grand' => $resSummary['total_grand'] ?? 0,
    ];
    $limitClause = "";
    $paramsData = $params;
    $typesData = $types;
    if (!$is_export) {
        $limitClause = "LIMIT ? OFFSET ?";
        $paramsData[] = $limit;
        $paramsData[] = $offset;
        $typesData .= "ii";
    }
    $query = "
        SELECT 
            mi.tgl_mutasi,
            DATE_FORMAT(mi.tgl_mutasi, '%Y-%m-%d') as tgl_raw,
            mi.no_faktur,
            mi.kode_supp,
            mi.kode_dari,
            mi.kode_tujuan,
            mi.acc_mutasi,
            mi.receipt,
            mi.cetak,
            kds.nm_alias as dari_nama,
            kdt.nm_alias as tujuan_nama,
            SUM(mi.qty * mi.netto) as total_netto,
            SUM(mi.qty * mi.ppn) as total_ppn,
            SUM(mi.qty * (mi.netto + mi.ppn)) as total_grand
        FROM mutasi_in_copy mi
        LEFT JOIN kode_store kds ON mi.kode_dari = kds.kd_store
        LEFT JOIN kode_store kdt ON mi.kode_tujuan = kdt.kd_store
        WHERE $where
        GROUP BY mi.no_faktur, mi.kode_supp, mi.kode_dari
        ORDER BY mi.tgl_mutasi DESC
        $limitClause
    ";
    $stmt = $conn->prepare($query);
    $stmt->bind_param($typesData, ...$paramsData);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    $pagination = null;
    if (!$is_export) {
        $queryCount = "SELECT COUNT(DISTINCT mi.no_faktur) as total FROM mutasi_in_copy mi WHERE $where";
        $stmtCount = $conn->prepare($queryCount);
        $stmtCount->bind_param($types, ...$params);
        $stmtCount->execute();
        $countResult = $stmtCount->get_result()->fetch_assoc();
        $totalRows = $countResult['total'];
        $pagination = [
            'current_page' => $page,
            'total_pages' => ceil($totalRows / $limit),
            'total_rows' => $totalRows
        ];
    }
    $stores = [];
    $storeRes = $conn->query("SELECT kd_store, nm_alias FROM kode_store ORDER BY kd_store");
    while ($s = $storeRes->fetch_assoc()) {
        $stores[] = $s;
    }
    echo json_encode([
        'summary' => $summary,
        'tabel_data' => $data,
        'stores' => $stores,
        'pagination' => $pagination
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>