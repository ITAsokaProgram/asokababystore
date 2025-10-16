<?php
require_once __DIR__ . '/../../../aa_kon_sett.php';
require_once __DIR__ . '/../../auth/middleware_login.php';

header("Content-Type:application/json");
header("Access-Control-Allow-Methods: GET");

$token = $_COOKIE['admin_token'] ?? null;
if (!$token) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$verify = verify_token($token);

// Pagination
$page     = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$pageSize = max(1, min(100, (int)($_GET['pageSize'] ?? 10)));
$offset   = max(0, ($page - 1) * $pageSize);

// Filter
$cabang  = $_GET['cabang']  ?? '';
$keyword = $_GET['keyword'] ?? '';

// Build WHERE clause
$where  = [];
$params = [];
$types  = '';

if (!empty($cabang)) {
    $where[] = "po.kd_store = ?";
    $params[] = $cabang;
    $types   .= 's';
}

if (!empty($keyword)) {
    $where[] = "(po.nama_produk LIKE ? OR po.barcode LIKE ?)";
    $kw = "%" . $keyword . "%";
    $params[] = $kw;
    $params[] = $kw;
    $types   .= 'ss';
}

$whereSql = count($where) ? "WHERE " . implode(" AND ", $where) : "";

// Main Query
$sql = "SELECT po.id, po.nama_produk, po.deskripsi, po.kategori, po.image_url, po.tanggal_upload,
               sb.qty, po.barcode, sb.kd_store, sb.harga_jual, ks.nm_alias as cabang
        FROM product_online po
        LEFT JOIN s_barang sb ON sb.item_n = po.barcode AND po.kd_store = sb.kd_store
        JOIN kode_store ks ON po.kd_store = ks.kd_store
        $whereSql
        ORDER BY po.tanggal_upload DESC
        LIMIT ?, ?";

try {
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    // bind filter + pagination
    $paramsMain = $params;
    $typesMain  = $types . 'ii';
    $paramsMain[] = $offset;
    $paramsMain[] = $pageSize;

    $stmt->bind_param($typesMain, ...$paramsMain);
    $stmt->execute();
    $result = $stmt->get_result();

    // Count query
    $countSql = "SELECT COUNT(*) as total
                 FROM product_online po
                 JOIN kode_store ks ON po.kd_store = ks.kd_store
                 $whereSql";

    $countStmt = $conn->prepare($countSql);
    if (!$countStmt) {
        throw new Exception("Prepare count failed: " . $conn->error);
    }
    if (!empty($params)) {
        $countStmt->bind_param($types, ...$params); // cukup pakai param filter saja, tanpa offset/limit
    }
    $countStmt->execute();
    $countResult = $countStmt->get_result();
    $countData = $countResult->fetch_assoc()['total'] ?? 0;

    $rows = $result->fetch_all(MYSQLI_ASSOC);

    http_response_code(200);
    echo json_encode([
        'success'  => true,
        'data'     => $rows,
        'total'    => $countData,
        'page'     => $page,
        'pageSize' => $pageSize,
        'offset'   => $offset,
        'filter'   => [
            'cabang'  => $cabang,
            'keyword' => $keyword
        ]
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error'   => 'Internal Server Error',
        'detail'  => $e->getMessage()
    ]);
} finally {
    if (isset($stmt)) $stmt->close();
    if (isset($countStmt)) $countStmt->close();
    $conn->close();
}
