<?php
require_once __DIR__ . '/../../../aa_kon_sett.php';
ini_set('max_execution_time', 10);
// Ambil keyword dari request (GET/POST)
$keyword = $_GET['q'] ?? '';
$keyword = trim($keyword);

if ($keyword === '') {
    echo json_encode([
        "status" => false,
        "message" => "Keyword kosong",
        "data" => []
    ]);
    exit;
}

// Escape untuk keamanan
$keywordEscaped = $conn->real_escape_string($keyword);

if (strlen($keyword) < 3) {
    $sql = "
        SELECT id, nama_produk, deskripsi, kategori, image_url, tanggal_upload
        FROM product_online
        WHERE nama_produk LIKE '%$keywordEscaped%'
    ";
} else {
    $sql = "
        SELECT id, nama_produk, deskripsi, kategori, image_url, tanggal_upload
        FROM product_online
        WHERE MATCH(nama_produk) AGAINST('{$keywordEscaped}*' IN BOOLEAN MODE)
    ";
}

$result = $conn->query($sql);
$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode([
    "status" => true,
    "count" => count($data),
    "data" => $data
]);
$conn->close();
