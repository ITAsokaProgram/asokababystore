<?php

require_once "../../aa_kon_sett.php";

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: OPTIONS, GET, POST");
header("Access-Control-Allow-Headers: Content-Type, Depth, User-Agent, X-File-Size, X-Requested-With, If-Modified-Since, X-File-Name, Cache-Control");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Ambil parameter pencarian dan kode store
$search = isset($_GET['search']) ? $_GET['search'] : '';
$kd_store_list = $_GET['kode']; 
$kd_store_array = explode(',', $kd_store_list); 

// Membuat placeholders untuk query IN
$placeholders = implode(',', array_fill(0, count($kd_store_array), '?'));
$sql = "SELECT kode_supp FROM supplier WHERE kode_supp LIKE ? AND kd_store IN ($placeholders)";
$stmt = $conn->prepare($sql);

// Format searchTerm
$searchTerm = "%" . $search . "%";

// Gabungkan semua parameter yang akan di-bind (searchTerm + kode store)
$paramTypes = str_repeat('s', 1 + count($kd_store_array)); // 1 string searchTerm + banyak kode store
$params = array_merge([$searchTerm], $kd_store_array);

// Membuat array referensi untuk bind_param
$bindNames[] = $paramTypes;
foreach ($params as $key => $value) {
    $bindNames[] = &$params[$key]; // referensi dibutuhkan oleh bind_param
}

// Bind parameter
call_user_func_array([$stmt, 'bind_param'], $bindNames);

// Eksekusi query
$stmt->execute();

// Ambil hasilnya
$result = $stmt->get_result();
$data = $result->fetch_all(MYSQLI_ASSOC);

// Mengirim data sebagai JSON
header("Content-Type: application/json");
echo json_encode($data);

// Menutup statement dan koneksi
$stmt->close();
$conn->close();

