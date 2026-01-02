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

    // 1. Verifikasi Token Login Utama
    $header = getAllHeaders();
    $authHeader = $header['Authorization'] ?? $header['authorization'] ?? '';
    if (!preg_match('/^Bearer\s(\S+)$/', $authHeader, $matches)) {
        http_response_code(401);
        throw new Exception('Token tidak ditemukan');
    }
    $verif = verify_token($matches[1]);
    if (!$verif)
        throw new Exception('Token tidak valid');

    // --- AMBIL USER DARI TOKEN ---
    $user_login = $verif->id ?? $verif->kode ?? null;

    $input = json_decode(file_get_contents('php://input'), true);

    $id = $input['id'] ?? null;
    $new_status = $input['status'] ?? null;
    $nama_user_cek = trim($input['nama_user_cek'] ?? '');
    $kode_otorisasi = $input['kode_otorisasi'] ?? '';

    if (!$id || !$new_status)
        throw new Exception("Data tidak lengkap.");
    if (empty($nama_user_cek) || empty($kode_otorisasi))
        throw new Exception("Otorisasi wajib diisi.");

    // 2. LOGIC OTORISASI (Tetap sama)
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

    // 3. UPDATE STATUS & DIEDIT_OLEH
    $tgl_diterima = null;

    if ($new_status === 'Sudah Terima') {
        $tgl_diterima = date('Y-m-d');
    }

    // Tambahkan diedit_oleh = ?
    $sql = "UPDATE serah_terima_nota SET status = ?, tgl_diterima = ?, edit_pada = NOW(), diedit_oleh = ? WHERE id = ?";
    $stmt_upd = $conn->prepare($sql);

    // Bind: status(s), tgl_diterima(s), diedit_oleh(s), id(i)
    $stmt_upd->bind_param("sssi", $new_status, $tgl_diterima, $user_login, $id);

    if (!$stmt_upd->execute()) {
        throw new Exception("Gagal update status: " . $stmt_upd->error);
    }

    echo json_encode([
        'success' => true,
        'message' => "Status berhasil diubah menjadi $new_status"
    ]);

} catch (Exception $e) {
    http_response_code(200);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>