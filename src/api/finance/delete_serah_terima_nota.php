<?php
session_start();
ini_set('display_errors', 0);
header('Content-Type: application/json');
require_once __DIR__ . '/../../../aa_kon_sett.php';
require_once __DIR__ . '/../../auth/middleware_login.php';
try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method Not Allowed');
    }
    $input = json_decode(file_get_contents('php://input'), true);
    $header = getAllHeaders();
    $authHeader = $header['Authorization'] ?? $header['authorization'] ?? '';
    if (!preg_match('/^Bearer\s(\S+)$/', $authHeader, $matches)) {
        http_response_code(401);
        throw new Exception('Token tidak ditemukan');
    }
    $verif = verify_token($matches[1]);
    if (!$verif) {
        throw new Exception('Token tidak valid atau kadaluwarsa');
    }
    $user_login_id = $verif->id ?? $verif->kode ?? null;
    $no_faktur = $input['no_faktur'] ?? null;
    $nama_user_cek = trim($input['nama_user_cek'] ?? '');
    $kode_otorisasi = $input['kode_otorisasi'] ?? '';
    if (!$no_faktur) {
        throw new Exception("No Faktur tidak valid.");
    }
    if (empty($nama_user_cek) || empty($kode_otorisasi)) {
        throw new Exception("User dan Kode Otorisasi wajib diisi.");
    }
    $stmt_cari = $conn->prepare("SELECT kode FROM user_account WHERE inisial = ? LIMIT 1");
    $stmt_cari->bind_param("s", $nama_user_cek);
    $stmt_cari->execute();
    $res_cari = $stmt_cari->get_result();
    if ($res_cari->num_rows === 0) {
        throw new Exception("User Otorisasi '$nama_user_cek' tidak ditemukan.");
    }
    $user_auth_data = $res_cari->fetch_assoc();
    $kode_user_auth = $user_auth_data['kode'];
    $stmt_cari->close();
    $stmt_auth = $conn->prepare("SELECT kode_user FROM otorisasi_user WHERE kode_user = ? AND PASSWORD = ?");
    $stmt_auth->bind_param("is", $kode_user_auth, $kode_otorisasi);
    $stmt_auth->execute();
    if ($stmt_auth->get_result()->num_rows === 0) {
        throw new Exception("Password Otorisasi Salah!");
    }
    $stmt_auth->close();
    $sql = "UPDATE serah_terima_nota SET 
            visibilitas = 'Nonaktif', 
            dihapus_pada = NOW(), 
            dihapus_oleh = ? 
            WHERE no_faktur = ?";
    $stmt_del = $conn->prepare($sql);
    $stmt_del->bind_param("ss", $user_login_id, $no_faktur);
    if (!$stmt_del->execute()) {
        throw new Exception("Database Error: " . $stmt_del->error);
    }
    if ($stmt_del->affected_rows === 0) {
        throw new Exception("Data tidak ditemukan atau sudah dihapus.");
    }
    echo json_encode([
        'success' => true,
        'message' => "Data nota $no_faktur berhasil dihapus."
    ]);
} catch (Exception $e) {
    http_response_code(200);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>