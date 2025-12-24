<?php
ini_set('memory_limit', '512M');
ini_set('display_errors', 0);
error_reporting(E_ALL);
require_once __DIR__ . "/../../../config.php";
require_once __DIR__ . "/../../../redis.php";
header("Content-Type: application/json; charset=utf-8");
try {
    $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 10;
    $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
    $filterStatus = isset($_GET['status']) ? $_GET['status'] : 'allStatus';
    $filterCabang = isset($_GET['cabang']) ? $_GET['cabang'] : '';
    if ($limit <= 0 || $page <= 0) {
        throw new Exception("Parameter limit atau page tidak valid", 400);
    }
    if (!isset($redis) || !$redis) {
        throw new Exception("Koneksi Redis gagal diinisialisasi", 500);
    }
    $redisKey = "member_poin";
    $cached = $redis->get($redisKey);
    if ($cached === false) {
        throw new Exception("Data member belum tersedia di Redis (Key kosong)", 404);
    }
    $decoded = json_decode($cached, true);
    unset($cached);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Format data Redis rusak (Invalid JSON): " . json_last_error_msg(), 500);
    }
    if (!isset($decoded['data'])) {
        throw new Exception("Struktur data Redis tidak valid (tidak ada key 'data')", 500);
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
                $passStatus = (isset($item['status_aktif']) && $item['status_aktif'] === 'Aktif');
            } elseif ($filterStatus === 'nonaktif') {
                $passStatus = (!isset($item['status_aktif']) || $item['status_aktif'] !== 'Aktif');
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
    $totalPages = ($limit > 0) ? ceil($totalFiltered / $limit) : 0;
    $offset = ($page - 1) * $limit;
    $paginatedData = array_slice($data, $offset, $limit);
    unset($data);
    unset($allData);
    unset($decoded);
    $response = [
        "success" => true,
        "message" => "Data berhasil diambil",
        "page" => $page,
        "limit" => $limit,
        "total" => $totalFiltered,
        "total_pages" => $totalPages,
        "stats" => $globalStats,
        "data" => $paginatedData
    ];
    echo json_encode($response);
} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
?>