<?php
require_once __DIR__ . '/../../../aa_kon_sett.php';
require_once __DIR__ . '/../../auth/middleware_login.php';
header("Access-Control-Allow-Methods: GET");
header('Content-Type: application/json');

// Optional token verification: if Authorization provided, verify; otherwise allow public access
$headers = getallheaders();
$auth_header = $headers['Authorization'] ?? '';
$token = $auth_header ? str_replace('Bearer ', '', $auth_header) : null;
if ($token) {
    $verif = verify_token($token);
    if (!$verif) {
        http_response_code(401);
        echo json_encode(['status' => false, 'message' => 'Token tidak valid']);
        exit;
    }
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        http_response_code(405);
        echo json_encode(['status' => false, 'message' => 'Method not allowed']);
        exit;
    }

    // Read query params
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $per_page = isset($_GET['per_page']) ? max(1, intval($_GET['per_page'])) : 20;
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $kd_store = isset($_GET['kd_store']) ? trim($_GET['kd_store']) : '';
    $group_aset = isset($_GET['group_aset']) ? trim($_GET['group_aset']) : '';
    $status_aset = isset($_GET['status_aset']) ? trim($_GET['status_aset']) : '';
    $id = isset($_GET['id']) ? trim($_GET['id']) : '';
    // date filters (supports from/to for each date column)
    $tanggal_beli_from = isset($_GET['tanggal_beli_from']) ? trim($_GET['tanggal_beli_from']) : '';
    $tanggal_beli_to = isset($_GET['tanggal_beli_to']) ? trim($_GET['tanggal_beli_to']) : '';
    $tanggal_perbaikan_from = isset($_GET['tanggal_perbaikan_from']) ? trim($_GET['tanggal_perbaikan_from']) : '';
    $tanggal_perbaikan_to = isset($_GET['tanggal_perbaikan_to']) ? trim($_GET['tanggal_perbaikan_to']) : '';
    $tanggal_rusak_from = isset($_GET['tanggal_rusak_from']) ? trim($_GET['tanggal_rusak_from']) : '';
    $tanggal_rusak_to = isset($_GET['tanggal_rusak_to']) ? trim($_GET['tanggal_rusak_to']) : '';
    $tanggal_mutasi_from = isset($_GET['tanggal_mutasi_from']) ? trim($_GET['tanggal_mutasi_from']) : '';
    $tanggal_mutasi_to = isset($_GET['tanggal_mutasi_to']) ? trim($_GET['tanggal_mutasi_to']) : '';

    $offset = ($page - 1) * $per_page;

    // Build WHERE clauses
    $where = [];
    $types = '';
    $params = [];

    if ($search !== '') {
        $where[] = "(h.nama_barang LIKE ? OR h.merk LIKE ? OR h.nama_toko LIKE ? OR h.mutasi_untuk LIKE ? OR h.mutasi_dari LIKE ?)";
        $like = "%{$search}%";
        // add same like param 5 times
        for ($i = 0; $i < 5; $i++) {
            $params[] = $like;
            $types .= 's';
        }
    }

    if ($kd_store !== '') {
        $where[] = "h.kd_store = ?";
        $params[] = $kd_store;
        $types .= 's';
    }

    if ($group_aset !== '') {
        $where[] = "h.group_aset = ?";
        $params[] = $group_aset;
        $types .= 's';
    }

    if ($status_aset !== '') {
        $where[] = "h.status = ?";
        $params[] = $status_aset;
        $types .= 's';
    }

    // date filters: use DATE() to compare only the date portion
    if ($tanggal_beli_from !== '') {
        $where[] = "DATE(h.tanggal_beli) >= ?";
        $params[] = $tanggal_beli_from;
        $types .= 's';
    }
    if ($tanggal_beli_to !== '') {
        $where[] = "DATE(h.tanggal_beli) <= ?";
        $params[] = $tanggal_beli_to;
        $types .= 's';
    }
    if ($tanggal_perbaikan_from !== '') {
        $where[] = "DATE(h.tanggal_perbaikan) >= ?";
        $params[] = $tanggal_perbaikan_from;
        $types .= 's';
    }
    if ($tanggal_perbaikan_to !== '') {
        $where[] = "DATE(h.tanggal_perbaikan) <= ?";
        $params[] = $tanggal_perbaikan_to;
        $types .= 's';
    }
    if ($tanggal_rusak_from !== '') {
        $where[] = "DATE(h.tanggal_rusak) >= ?";
        $params[] = $tanggal_rusak_from;
        $types .= 's';
    }
    if ($tanggal_rusak_to !== '') {
        $where[] = "DATE(h.tanggal_rusak) <= ?";
        $params[] = $tanggal_rusak_to;
        $types .= 's';
    }
    if ($tanggal_mutasi_from !== '') {
        $where[] = "DATE(h.tanggal_mutasi) >= ?";
        $params[] = $tanggal_mutasi_from;
        $types .= 's';
    }
    if ($tanggal_mutasi_to !== '') {
        $where[] = "DATE(h.tanggal_mutasi) <= ?";
        $params[] = $tanggal_mutasi_to;
        $types .= 's';
    }

    if ($id !== '') {
        $where[] = "h.idhistory_aset = ?";
        $params[] = $id;
        $types .= 'i';
    }

    $where_sql = '';
    if (!empty($where)) {
        $where_sql = 'WHERE ' . implode(' AND ', $where);
    }

    // Count total (join with kode_store to allow filtering by kd_store if needed)
    $count_sql = "SELECT COUNT(*) as total FROM history_aset h LEFT JOIN kode_store ks ON h.kd_store = ks.kd_store " . $where_sql;
    $count_stmt = $conn->prepare($count_sql);
    if ($count_stmt === false) throw new Exception('Database error: ' . $conn->error);

    if (!empty($params)) {
        // bind params for count
        $bind_names = [];
        $bind_names[] = $types;
        for ($i = 0; $i < count($params); $i++) {
            $bind_names[] = &$params[$i];
        }
        call_user_func_array([$count_stmt, 'bind_param'], $bind_names);
    }

    if (!$count_stmt->execute()) throw new Exception('Failed to execute count query: ' . $count_stmt->error);
    $count_res = $count_stmt->get_result()->fetch_assoc();
    $total = intval($count_res['total'] ?? 0);
    $count_stmt->close();

    // Fetch rows with limit (include store alias)
    $sql = "SELECT h.idhistory_aset, h.nama_barang, h.no_seri, h.keterangan, h.merk, h.harga_beli, h.nama_toko, h.tanggal_beli, h.tanggal_ganti, h.tanggal_perbaikan, h.tanggal_mutasi, h.tanggal_rusak, h.group_aset, h.mutasi_untuk, h.mutasi_dari, h.kd_store, ks.nm_alias AS nm_alias, h.status, h.image_url FROM history_aset h LEFT JOIN kode_store ks ON h.kd_store = ks.kd_store " . $where_sql . " ORDER BY CASE WHEN h.group_aset IS NULL OR h.group_aset = '' THEN 1 ELSE 0 END, 
    h.group_aset ASC, 
    h.tanggal_beli DESC LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) throw new Exception('Database error: ' . $conn->error);

    // bind params + limit + offset
    $all_types = $types . 'ii';
    $all_params = $params;
    $all_params[] = $per_page;
    $all_params[] = $offset;

    $bind_names = [];
    $bind_names[] = $all_types;
    for ($i = 0; $i < count($all_params); $i++) {
        $bind_names[] = &$all_params[$i];
    }
    call_user_func_array([$stmt, 'bind_param'], $bind_names);

    if (!$stmt->execute()) throw new Exception('Failed to fetch data: ' . $stmt->error);
    $res = $stmt->get_result();
    $items = [];
    while ($row = $res->fetch_assoc()) {
        $items[] = $row;
    }
    $stmt->close();

    echo json_encode([
        'status' => true,
        'message' => 'Data fetched',
        'data' => [
            'items' => $items,
            'total' => $total,
            'page' => $page,
            'per_page' => $per_page,
            'total_pages' => $per_page ? ceil($total / $per_page) : 0
        ]
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['status' => false, 'message' => $e->getMessage()]);
}
