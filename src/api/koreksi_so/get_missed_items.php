<?php
session_start();
include '../../../aa_kon_sett.php';
header('Content-Type: application/json');
register_shutdown_function(function () {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        http_response_code(500);
        echo json_encode(['error' => 'Server Error: ' . $error['message']]);
    }
});
$is_export = isset($_GET['export']) && $_GET['export'] === 'true';
$response = [
    'stores' => [],
    'tabel_data' => [],
    'summary' => ['total_items' => 0],
    'pagination' => null,
    'error' => null,
    'params' => []
];
try {
    $default_tgl_mulai = date('Y-m-16', strtotime('last month'));
    $default_tgl_selesai = date('Y-m-15');
    $tgl_mulai = $_GET['tgl_mulai'] ?? $default_tgl_mulai;
    $tgl_selesai = $_GET['tgl_selesai'] ?? $default_tgl_selesai;
    $kd_store = $_GET['kd_store'] ?? 'all';
    $mode_laporan = $_GET['mode'] ?? 'jadwal';
    $response['params'] = [
        'tgl_mulai' => $tgl_mulai,
        'tgl_selesai' => $tgl_selesai,
        'mode' => $mode_laporan
    ];
    $page = max(1, (int) ($_GET['page'] ?? 1));
    $limit = 100;
    $offset = ($page - 1) * $limit;
    $sql_stores = "SELECT kd_store, nm_alias FROM kode_store WHERE display = 'on' ORDER BY nm_alias ASC";
    $res_stores = $conn->query($sql_stores);
    if ($res_stores) {
        while ($row = $res_stores->fetch_assoc()) {
            $response['stores'][] = $row;
        }
    }
    $sql_koreksi_filter = "DATE(tgl_koreksi) BETWEEN ? AND ?";
    $params_koreksi = [$tgl_mulai, $tgl_selesai];
    $types_koreksi = "ss";
    $where_store_master = "";
    $params_store = [];
    $types_store = "";
    if ($kd_store !== 'all') {
        $where_store_master = "AND m.KD_STORE = ?";
        $sql_koreksi_filter .= " AND kd_store = ?";
        $params_store[] = $kd_store;
        $types_store .= "s";
        $params_koreksi[] = $kd_store;
        $types_koreksi .= "s";
    }
    $final_bind_vars = [];
    $final_bind_types = "";
    if ($mode_laporan === 'non_jadwal') {
        $sql_core = "
            FROM master m
            WHERE 
                1=1 
                $where_store_master
                AND m.plu NOT IN (
                    SELECT plu 
                    FROM koreksi 
                    WHERE $sql_koreksi_filter
                )
        ";
        $final_bind_types .= $types_store;
        $final_bind_vars = array_merge($final_bind_vars, $params_store);
        $final_bind_types .= $types_koreksi;
        $final_bind_vars = array_merge($final_bind_vars, $params_koreksi);
        $cols_select = "
            '$tgl_selesai' as tgl_jadwal,
            m.plu, 
            m.DESCP as deskripsi, 
            IFNULL(m.SATUAN, 'PCS') as satuan, 
            m.VENDOR as kode_supp, 
            m.VENDOR as nama_supp, 
            m.ON_HAND1 as stock,        
            m.AVG_COST as avg_cost
        ";
        $order_by = "ORDER BY m.VENDOR ASC, m.plu ASC";
    } elseif ($mode_laporan === 'master_exclude_jadwal') {
        $sql_jadwal_exclude = "DATE(Tgl_schedule) BETWEEN ? AND ?";
        $params_jadwal_ex = [$tgl_mulai, $tgl_selesai];
        $types_jadwal_ex = "ss";
        if ($kd_store !== 'all') {
            $sql_jadwal_exclude .= " AND Kd_Store = ?";
            $params_jadwal_ex[] = $kd_store;
            $types_jadwal_ex .= "s";
        }
        $sql_core = "
            FROM master m
            WHERE 
                1=1 
                $where_store_master
                -- Syarat 1: Belum ada di koreksi
                AND m.plu NOT IN (
                    SELECT plu 
                    FROM koreksi 
                    WHERE $sql_koreksi_filter
                )
                -- Syarat 2: Vendornya TIDAK ada di jadwal (Gaada di jadwal)
                AND m.VENDOR NOT IN (
                    SELECT kode_supp 
                    FROM jadwal_so 
                    WHERE $sql_jadwal_exclude
                )
        ";
        $final_bind_types .= $types_store;
        $final_bind_vars = array_merge($final_bind_vars, $params_store);
        $final_bind_types .= $types_koreksi;
        $final_bind_vars = array_merge($final_bind_vars, $params_koreksi);
        $final_bind_types .= $types_jadwal_ex;
        $final_bind_vars = array_merge($final_bind_vars, $params_jadwal_ex);
        $cols_select = "
            '$tgl_selesai' as tgl_jadwal,
            m.plu, 
            m.DESCP as deskripsi, 
            IFNULL(m.SATUAN, 'PCS') as satuan, 
            m.VENDOR as kode_supp, 
            m.VENDOR as nama_supp, 
            m.ON_HAND1 as stock,        
            m.AVG_COST as avg_cost
        ";
        $order_by = "ORDER BY m.VENDOR ASC, m.plu ASC";
    } else {
        $sql_jadwal_filter = "DATE(Tgl_schedule) BETWEEN ? AND ?";
        $params_jadwal = [$tgl_mulai, $tgl_selesai];
        $types_jadwal = "ss";
        if ($kd_store !== 'all') {
            $sql_jadwal_filter .= " AND Kd_Store = ?";
            $params_jadwal[] = $kd_store;
            $types_jadwal .= "s";
        }
        $sql_core = "
            FROM master m
            JOIN (
                SELECT DISTINCT kode_supp, nama_supp, DATE(Tgl_schedule) as tgl_jadwal
                FROM jadwal_so 
                WHERE $sql_jadwal_filter
            ) j ON m.VENDOR = j.kode_supp
            WHERE 
                1=1 
                $where_store_master
                AND m.plu NOT IN (
                    SELECT plu 
                    FROM koreksi 
                    WHERE $sql_koreksi_filter
                )
        ";
        $final_bind_types .= $types_jadwal;
        $final_bind_vars = array_merge($final_bind_vars, $params_jadwal);
        $final_bind_types .= $types_store;
        $final_bind_vars = array_merge($final_bind_vars, $params_store);
        $final_bind_types .= $types_koreksi;
        $final_bind_vars = array_merge($final_bind_vars, $params_koreksi);
        $cols_select = "
            j.tgl_jadwal,
            m.plu, 
            m.DESCP as deskripsi, 
            IFNULL(m.SATUAN, 'PCS') as satuan, 
            m.VENDOR as kode_supp, 
            j.nama_supp,
            m.ON_HAND1 as stock,        
            m.AVG_COST as avg_cost
        ";
        $order_by = "ORDER BY j.tgl_jadwal ASC, m.VENDOR ASC, m.plu ASC";
    }
    if (!$is_export) {
        $sql_count = "SELECT COUNT(*) as total $sql_core";
        $stmt_count = $conn->prepare($sql_count);
        if (!$stmt_count)
            throw new Exception("Count Query Error: " . $conn->error);
        $stmt_count->bind_param($final_bind_types, ...$final_bind_vars);
        $stmt_count->execute();
        $res_count = $stmt_count->get_result()->fetch_assoc();
        $total_rows = $res_count['total'];
        $response['pagination'] = [
            'current_page' => $page,
            'total_rows' => (int) $total_rows,
            'total_pages' => ceil($total_rows / $limit),
            'limit' => $limit,
            'offset' => $offset
        ];
        $response['summary']['total_items'] = (int) $total_rows;
    }
    $sql_select = "SELECT $cols_select $sql_core $order_by";
    if (!$is_export) {
        $sql_select .= " LIMIT ? OFFSET ?";
        $final_bind_types .= "ii";
        $final_bind_vars[] = $limit;
        $final_bind_vars[] = $offset;
    }
    $stmt = $conn->prepare($sql_select);
    if (!$stmt)
        throw new Exception("Select Query Error: " . $conn->error);
    $stmt->bind_param($final_bind_types, ...$final_bind_vars);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        array_walk_recursive($row, function (&$item) {
            if (is_string($item))
                $item = utf8_encode($item);
        });
        $response['tabel_data'][] = $row;
    }
    $conn->close();
} catch (Exception $e) {
    http_response_code(500);
    $response['error'] = $e->getMessage();
}
echo json_encode($response);
?>