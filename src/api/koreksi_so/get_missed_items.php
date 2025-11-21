<?php
session_start();
include '../../../aa_kon_sett.php';

header('Content-Type: application/json');

// Handle Error Global
register_shutdown_function(function () {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        http_response_code(500);
        echo json_encode(['error' => 'Server Error: ' . $error['message']]);
    }
});

$is_export = isset($_GET['export']) && $_GET['export'] === 'true';

// Response Template
$response = [
    'stores' => [],
    'tabel_data' => [],
    'summary' => ['total_items' => 0],
    'pagination' => null,
    'error' => null,
    'params' => []
];

try {
    // 1. Parameter Default
    $default_tgl_mulai = date('Y-m-16', strtotime('last month'));
    $default_tgl_selesai = date('Y-m-15');

    $tgl_mulai = $_GET['tgl_mulai'] ?? $default_tgl_mulai;
    $tgl_selesai = $_GET['tgl_selesai'] ?? $default_tgl_selesai;
    $kd_store = $_GET['kd_store'] ?? 'all';
    
    $response['params'] = ['tgl_mulai' => $tgl_mulai, 'tgl_selesai' => $tgl_selesai];

    // Pagination Logic
    $page = max(1, (int)($_GET['page'] ?? 1));
    $limit = 20; // Item per page
    $offset = ($page - 1) * $limit;

    // 2. Ambil Data Cabang (Store)
    $sql_stores = "SELECT kd_store, nm_alias FROM kode_store ORDER BY kd_store ASC";
    $res_stores = $conn->query($sql_stores);
    while ($row = $res_stores->fetch_assoc()) {
        $response['stores'][] = $row;
    }

    // 3. Build Query Utama
    // Logika:
    // a. Cari Supplier yang terjadwal di rentang tanggal ini (jadwal_so)
    // b. Ambil barang di Master milik supplier tersebut
    // c. Filter barang yang TIDAK ADA di tabel Koreksi pada rentang tanggal/store yang sama

    $where_store_jadwal = "";
    $where_store_koreksi = "";
    $where_store_master = "";
    $bind_types = ""; 
    $bind_vars = [];

    // Tanggal Bind (2x untuk jadwal, 2x untuk koreksi)
    // Structure: Master -> Vendor IN (Jadwal) -> PLU NOT IN (Koreksi)

    // Setup Params untuk Prepared Statement
    // Filter Jadwal
    $sql_jadwal_filter = "DATE(Tgl_schedule) BETWEEN ? AND ?";
    $params_jadwal = [$tgl_mulai, $tgl_selesai];
    $types_jadwal = "ss";

    // Filter Koreksi
    $sql_koreksi_filter = "DATE(tgl_koreksi) BETWEEN ? AND ?";
    $params_koreksi = [$tgl_mulai, $tgl_selesai];
    $types_koreksi = "ss";

    if ($kd_store !== 'all') {
        $sql_jadwal_filter .= " AND Kd_Store = ?";
        $params_jadwal[] = $kd_store;
        $types_jadwal .= "s";

        $sql_koreksi_filter .= " AND kd_store = ?";
        $params_koreksi[] = $kd_store;
        $types_koreksi .= "s";
        
        $where_store_master = "AND m.KD_STORE = ?";
    }

    // Query Construct
    // Note: Kita ambil nama supplier dari jadwal_so (karena master cuma punya kode)
    // Kita gunakan Subquery DISTINCT untuk nama supplier agar lebih rapi
    
    $sql_core = "
        FROM master m
        JOIN (
            SELECT DISTINCT kode_supp, nama_supp 
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

    // Gabungkan semua parameter bind
    // Urutan: Jadwal Params -> Store Master (if any) -> Koreksi Params
    $final_bind_types = $types_jadwal;
    $final_bind_vars = $params_jadwal;

    if ($kd_store !== 'all') {
        $final_bind_types .= "s";
        $final_bind_vars[] = $kd_store;
    }

    $final_bind_types .= $types_koreksi;
    $final_bind_vars = array_merge($final_bind_vars, $params_koreksi);

    // Hitung Total Row (untuk pagination)
    if (!$is_export) {
        $sql_count = "SELECT COUNT(*) as total $sql_core";
        $stmt_count = $conn->prepare($sql_count);
        $stmt_count->bind_param($final_bind_types, ...$final_bind_vars);
        $stmt_count->execute();
        $res_count = $stmt_count->get_result()->fetch_assoc();
        $total_rows = $res_count['total'];
        
        $response['pagination'] = [
            'current_page' => $page,
            'total_rows' => (int)$total_rows,
            'total_pages' => ceil($total_rows / $limit),
            'limit' => $limit,
            'offset' => $offset
        ];
        $response['summary']['total_items'] = (int)$total_rows;
    }

    // Query Data Sebenarnya
    // Group by Supplier Code agar JS bisa rendering header
    $sql_select = "
        SELECT 
            m.plu, 
            m.DESCP as deskripsi, 
            m.SATUAN as satuan, 
            m.VENDOR as kode_supp, 
            j.nama_supp,
            m.ON_HAND1 as stock, 
            m.AVG_COST as avg_cost
        $sql_core
        ORDER BY m.VENDOR ASC, m.plu ASC
    ";

    if (!$is_export) {
        $sql_select .= " LIMIT ? OFFSET ?";
        $final_bind_types .= "ii";
        $final_bind_vars[] = $limit;
        $final_bind_vars[] = $offset;
    }

    $stmt = $conn->prepare($sql_select);
    if (!$stmt) throw new Exception("DB Error: " . $conn->error);
    
    $stmt->bind_param($final_bind_types, ...$final_bind_vars);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        // Encoding UTF8
        array_walk_recursive($row, function(&$item) {
            if(is_string($item)) $item = utf8_encode($item);
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