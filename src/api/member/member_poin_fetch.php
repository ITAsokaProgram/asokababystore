<?php
require_once __DIR__ . "/../../../config.php";
require_once __DIR__ . "/../../../redis.php";
header("Content-Type: application/json");
$limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 10;
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$filterStatus = isset($_GET['status']) ? $_GET['status'] : 'allStatus';
$filterCabang = isset($_GET['cabang']) ? $_GET['cabang'] : '';
if ($limit <= 0 || $page <= 0) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Parameter tidak valid"]);
    exit;
}
$redisKey = "member_poin";
$cached = $redis->get($redisKey);
if ($cached === false) {
    http_response_code(404);
    echo json_encode(["success" => false, "message" => "Data belum tersedia di Redis"]);
    exit;
}
$decoded = json_decode($cached, true);
if (!isset($decoded['data'])) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Format data Redis tidak valid"]);
    exit;
}
$allData = $decoded['data'];
$globalStats = [
    'total' => count($allData),
    'active' => 0,
    'non_active' => 0
];
foreach ($allData as $row) {
    if (isset($row['status_aktif']) && $row['status_aktif'] === 'Aktif') {
        $globalStats['active']++;
    } else {
        $globalStats['non_active']++;
    }
}
$data = $allData;
if ($filterStatus !== 'allStatus' || !empty($filterCabang)) {
    $data = array_filter($data, function ($item) use ($filterStatus, $filterCabang) {
        $passStatus = true;
        if ($filterStatus === 'aktif') {
            $passStatus = ($item['status_aktif'] === 'Aktif');
        } elseif ($filterStatus === 'nonaktif') {
            $passStatus = ($item['status_aktif'] !== 'Aktif');
        }
        $passCabang = true;
        if (!empty($filterCabang)) {
            $cabangItem = isset($item['nama_cabang']) ? $item['nama_cabang'] : '';
            $passCabang = ($cabangItem === $filterCabang);
        }
        return $passStatus && $passCabang;
    });
}
$data = array_values($data);
$totalFiltered = count($data);
$totalPages = ceil($totalFiltered / $limit);
$offset = ($page - 1) * $limit;
$paginatedData = array_slice($data, $offset, $limit);
echo json_encode([
    "success" => true,
    "message" => "Data berhasil diambil",
    "page" => $page,
    "limit" => $limit,
    "total" => $totalFiltered,
    "total_pages" => $totalPages,
    "stats" => $globalStats,
    "data" => $paginatedData
]);
?>