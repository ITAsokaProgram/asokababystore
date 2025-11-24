<?php
include '../../../aa_kon_sett.php';
require_once __DIR__ . "/../../auth/middleware_login.php";
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Authorization, Content-Type");
$headers = getallheaders();
$authHeader = $headers['Authorization'] ?? '';
$token = null;
if (preg_match('/^Bearer\s(\S+)$/', $authHeader, $matches)) {
    $token = $matches[1];
}
if (!$token) {
    http_response_code(401);
    echo json_encode(['status' => "Unauthenticated", 'message' => 'Request ditolak user tidak terdaftar']);
    exit;
}
$verif = verify_token($token);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Metode tidak diizinkan']);
    exit;
}
$nama = trim($_POST['name'] ?? '');
$username = trim($_POST['username'] ?? '');
$pass = $_POST['pass'] ?? '';
$c_pass = $_POST['c_pass'] ?? '';
$position = $_POST['position'] ?? '';
$next_kode = intval($_POST['kode'] ?? 0);
$menus = $_POST['menus'] ?? [];
if (is_array($menus)) {
    $menus = array_unique($menus);
}
$kode_toko = '9999';
$kd_store = $_POST['cabang'] ?? '';
if ($kd_store == "all") {
    $kd_store = "Pusat";
}
if (!$nama || !$username || !$pass || !$c_pass || !$position || !$next_kode) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Semua field wajib diisi.']);
    exit;
}
if ($pass !== $c_pass) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Kata sandi dan konfirmasi tidak cocok.']);
    exit;
}
$stmt = $conn->prepare("SELECT 1 FROM user_account WHERE inisial = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    http_response_code(409);
    echo json_encode(['status' => 'error', 'message' => 'Nama pengguna sudah digunakan.']);
    $stmt->close();
    exit;
}
$stmt->close();
$hashed_password = password_hash($pass, PASSWORD_ARGON2ID);
$conn->begin_transaction();
try {
    $stmt = $conn->prepare("
            INSERT INTO user_account (kode, nama, inisial, password, Hak, kode_toko, kd_store)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
    $stmt->bind_param("issssss", $next_kode, $nama, $username, $hashed_password, $position, $kode_toko, $kd_store);
    if (!$stmt->execute()) {
        throw new Exception("Gagal menyimpan user: " . $stmt->error);
    }
    $stmt->close();
    if (!empty($menus)) {
        $stmt_get_url = $conn->prepare("SELECT endpoint_url FROM menu_website WHERE menu_code = ?");
        $stmt_insert_access = $conn->prepare("
                INSERT INTO user_internal_access (id_user, menu_code, endpoint_url, can_view)
                VALUES (?, ?, ?, 1)
            ");
        if (!$stmt_get_url || !$stmt_insert_access) {
            throw new Exception("Gagal prepare statement: " . $conn->error);
        }
        $id_user = $next_kode;
        $menu_code = '';
        $endpoint_url = '';
        $stmt_get_url->bind_param("s", $menu_code);
        $stmt_insert_access->bind_param("iss", $id_user, $menu_code, $endpoint_url);
        foreach ($menus as $menu) {
            $menu_code = $menu;
            if (!$stmt_get_url->execute()) {
                throw new Exception("Gagal ambil endpoint URL: " . $stmt_get_url->error);
            }
            $result = $stmt_get_url->get_result();
            $row = $result->fetch_assoc();
            if (!$row) {
                continue;
            }
            $endpoint_url = $row['endpoint_url'];
            if (!$stmt_insert_access->execute()) {
                throw new Exception("Gagal insert akses menu: " . $stmt_insert_access->error);
            }
        }
        $stmt_get_url->close();
        $stmt_insert_access->close();
    }
    $conn->commit();
    http_response_code(201);
    echo json_encode(['status' => 'success', 'message' => 'User berhasil didaftarkan.']);
} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
$conn->close();
?>