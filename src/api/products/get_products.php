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

try {
    // Main Query
    $sql = "SELECT po.id, po.nama_produk, po.deskripsi, po.kategori, po.image_url, po.tanggal_upload,
                   sb.qty, po.barcode, sb.kd_store, sb.harga_jual, ks.nm_alias as cabang
            FROM product_online po
            LEFT JOIN s_barang sb ON sb.item_n = po.barcode AND po.kd_store = sb.kd_store
            JOIN kode_store ks ON po.kd_store = ks.kd_store
            ORDER BY po.tanggal_upload DESC
            LIMIT ?, ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $offset, $pageSize);
    $stmt->execute();
    $result = $stmt->get_result();

    // Count total data (harus ikut join/filter biar konsisten)
    $countSql = "SELECT COUNT(*) as total
                 FROM product_online po
                 JOIN kode_store ks ON po.kd_store = ks.kd_store";
    $countStmt = $conn->prepare($countSql);
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
        'offset'   => $offset
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error'   => 'Internal Server Error',
        'detail'  => $e->getMessage() // bisa dihapus di production
    ]);
} finally {
    if (isset($stmt)) $stmt->close();
    if (isset($countStmt)) $countStmt->close();
    $conn->close();
}
