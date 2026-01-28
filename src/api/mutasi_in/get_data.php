<?php
header('Content-Type: application/json');
include '../../../aa_kon_sett.php';
require_once __DIR__ . "/../../auth/middleware_login.php";

try {
    $allow_print = false;
    

    if ($token) {
        $user = authenticate_request();
        if ($user) {
            $user_id = $user->kode ?? $user->id ?? 0;
            $checkSql = "SELECT 1 FROM user_internal_access 
                         WHERE id_user = ? AND menu_code = 'izin_cetak' AND can_view = 1 
                         LIMIT 1";
            $stmtPerm = $conn->prepare($checkSql);
            if ($stmtPerm) {
                $stmtPerm->bind_param("i", $user_id);
                $stmtPerm->execute();
                $stmtPerm->store_result();
                if ($stmtPerm->num_rows > 0) {
                    $allow_print = true;
                }
                $stmtPerm->close();
            }
        }
    }

    $tgl_mulai = $_GET['tgl_mulai'] ?? date('Y-m-d');
    $tgl_selesai = $_GET['tgl_selesai'] ?? date('Y-m-d');
    $kd_store = $_GET['kd_store'] ?? 'all';
    $kd_store_tujuan = $_GET['kd_store_tujuan'] ?? 'all'; // Baru
    $status_cetak = $_GET['status_cetak'] ?? 'all';
    $status_terima = $_GET['status_terima'] ?? 'all';
    $search_query = $_GET['search_query'] ?? '';
    $is_export = isset($_GET['export']) && $_GET['export'] === 'true';

    $page = (int) ($_GET['page'] ?? 1);
    if ($page < 1)
        $page = 1;
    $limit = 100;
    $offset = ($page - 1) * $limit;

    $where = "DATE(mi.tgl_mutasi) BETWEEN ? AND ?";
    $params = [$tgl_mulai, $tgl_selesai];
    $types = "ss";

    if ($kd_store !== 'all') {
        $where .= " AND mi.kode_dari = ?";
        $params[] = $kd_store;
        $types .= "s";
    }

    // Filter Cabang Tujuan (Baru)
    if ($kd_store_tujuan !== 'all') {
        $where .= " AND mi.kode_tujuan = ?";
        $params[] = $kd_store_tujuan;
        $types .= "s";
    }

    if (!empty($search_query)) {
        $where .= " AND (mi.no_faktur LIKE ? OR mi.no_mmd LIKE ?)";
        $wildcard = "%" . $search_query . "%";
        $params[] = $wildcard;
        $params[] = $wildcard;
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
        FROM mutasi_in mi
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
            kds.Nm_NPWP as dari_nama_npwp, 
            kds.Alm_NPWP as dari_alm_npwp,
            kdt.nm_alias as tujuan_nama,
            kdt.Nm_NPWP as tujuan_nama_npwp, 
            kdt.Alm_NPWP as tujuan_alm_npwp,
            SUM(mi.qty * mi.netto) as total_netto,
            SUM(mi.qty * mi.ppn) as total_ppn,
            SUM(mi.qty * (mi.netto + mi.ppn)) as total_grand
        FROM mutasi_in mi
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
        $queryCount = "SELECT COUNT(DISTINCT mi.no_faktur) as total FROM mutasi_in mi WHERE $where";
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
        'allow_print' => $allow_print,
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