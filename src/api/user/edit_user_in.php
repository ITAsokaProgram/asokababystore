<?php
require_once __DIR__ . "/../../../aa_kon_sett.php";
require_once __DIR__ . "/../../auth/middleware_login.php";

header("Content-Type:application/json");
$headers = getallheaders();
if(!isset($headers['Authorization'])){
    http_response_code(401);
    echo json_encode(['status' => "Unauthenticated", 'message' => 'Request ditolak user tidak terdaftar']);
    exit;
}
$authHeader = $headers['Authorization'];
$token = null;
if (preg_match('/^Bearer\s(\S+)$/', $authHeader, $matches)) {
    $token = $matches[1]; // ini yang aman dan baku
}
if (!$token) {
    http_response_code(401);
    echo json_encode(['status' => "Unauthenticated", 'message' => 'Request ditolak user tidak terdaftar']);
    exit;
}

$verif = verify_token($token);
// Ambil JSON dari body request
$data = json_decode(file_get_contents("php://input"), true);

// Ambil field dan validasi
$id_user  = $data['id_user'] ?? null;
$name     = $data['name'] ?? null;
$position = $data['position'] ?? null;
$username = $data['username'] ?? null;
$menus    = $data['menus'] ?? [];
$kode_cabang = $data['kode_cabang'] ?? null;
if($kode_cabang == "all"){
    $kode_cabang = "Pusat";
}
if (!$id_user || !$name || !$position || !$username) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Data tidak lengkap']);
    exit;
}

// Update data user
$updateUser = $conn->prepare("UPDATE user_account SET nama=?, hak=?, inisial=?, kd_store=? WHERE kode=?");
if (!$updateUser) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Gagal prepare update user']);
    exit;
}
$updateUser->bind_param("ssssi", $name, $position, $username, $kode_cabang, $id_user);
$updateUser->execute();
$updateUser->close();

// Hapus menu akses lama
$deleteStmt = $conn->prepare("DELETE FROM user_internal_access WHERE id_user = ?");
if (!$deleteStmt) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Gagal prepare hapus akses']);
    exit;
}
$deleteStmt->bind_param("i", $id_user);
$deleteStmt->execute();
$deleteStmt->close();

// Tambahkan menu akses baru
// Ambil endpoint_url dari menu_website dan simpan ulang akses
$getUrl = $conn->prepare("SELECT endpoint_url FROM menu_website WHERE menu_code = ?");
$insert = $conn->prepare("
    INSERT INTO user_internal_access (id_user, menu_code, endpoint_url, can_view)
    VALUES (?, ?, ?, 1)
");

if (!$getUrl || !$insert) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Gagal prepare akses menu']);
    exit;
}

$menu_code = '';
$endpoint_url = '';
$getUrl->bind_param("s", $menu_code);
$insert->bind_param("iss", $id_user, $menu_code, $endpoint_url);

foreach ($menus as $menu_code) {
    $getUrl->execute();
    $result = $getUrl->get_result();
    $row = $result->fetch_assoc();

    if (!$row) {
        continue; // Skip jika menu_code tidak ditemukan
    }

    $endpoint_url = $row['endpoint_url'];
    $insert->execute();
}

$getUrl->close();
$insert->close();

// Sukses
echo json_encode(['status' => 'success', 'message' => 'Data pengguna berhasil diperbarui']);
$conn->close();
