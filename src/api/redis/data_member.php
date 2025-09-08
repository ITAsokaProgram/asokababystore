<?php
require_once __DIR__ . "/../../../redis.php";
require_once __DIR__ . "/../../../aa_kon_sett.php"; // koneksi MySQLi

$page   = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit  = isset($_GET['limit']) ? (int)$_GET['limit'] : 15;
$search = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';

$metaKey  = "member:poin:meta";
$chunkKey = "member:poin:page:";

$meta       = json_decode($redis->get($metaKey), true);
$total      = $meta['total'] ?? 0;
$chunkSize  = $meta['chunk_size'] ?? 1000;

$response = [
    "success"     => true,
    "page"        => $page,
    "per_page"    => $limit,
    "total"       => 0,
    "total_pages" => 0,
    "data"        => [],
    "meta"        => $meta
];

if ($search !== '') {
    $offset = ($page - 1) * $limit;

    // 🔎 1. Coba pakai FULLTEXT
    $keyword = $search . "*"; // prefix search
    $stmt = $conn->prepare("
        SELECT nama_cust, kd_cust
        FROM customers
        WHERE MATCH(nama_cust, kd_cust) AGAINST (? IN BOOLEAN MODE)
        LIMIT ?, ?
    ");
    $stmt->bind_param("sii", $keyword, $offset, $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    $rows = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // hitung total hasil FULLTEXT
    $totalResult = 0;
    $res = $conn->query("SELECT FOUND_ROWS() as total");
    if ($res) {
        $totalRow = $res->fetch_assoc();
        $totalResult = (int)$totalRow['total'];
    }

    // 🔎 2. Kalau hasil kosong → fallback LIKE
    if ($totalResult === 0) {
        $searchLike = "%" . $search . "%";

        // hitung total data LIKE
        $stmt = $conn->prepare("
            SELECT COUNT(*) 
            FROM customers 
            WHERE nama_cust LIKE ? 
               OR kd_cust LIKE ?
        ");
        $stmt->bind_param("ss", $searchLike, $searchLike);
        $stmt->execute();
        $stmt->bind_result($totalResult);
        $stmt->fetch();
        $stmt->close();

        // ambil data LIKE
        $stmt = $conn->prepare("
            SELECT nama_cust, kd_cust
            FROM customers
            WHERE nama_cust LIKE ? 
               OR kd_cust LIKE ?
            ORDER BY nama_cust ASC
            LIMIT ?, ?
        ");
        $stmt->bind_param("ssii", $searchLike, $searchLike, $offset, $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        $rows = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    }

    $response['total']       = $totalResult;
    $response['total_pages'] = $totalResult > 0 ? ceil($totalResult / $limit) : 0;
    $response['data']        = $rows;

} else {
    // 📑 Pagination normal pakai Redis
    $offset = ($page - 1) * $limit;
    $chunkIndex = floor($offset / $chunkSize) + 1;
    $chunkStart = $offset % $chunkSize;

    $rows = json_decode($redis->get($chunkKey . $chunkIndex), true) ?? [];
    $data = array_slice($rows, $chunkStart, $limit);

    $response['total']       = $total;
    $response['total_pages'] = $total > 0 ? ceil($total / $limit) : 0;
    $response['data']        = $data;
}

header('Content-Type: application/json');
echo json_encode($response, JSON_UNESCAPED_UNICODE);
