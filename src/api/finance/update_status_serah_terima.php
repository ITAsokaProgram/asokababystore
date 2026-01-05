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
    if (!$verif)
        throw new Exception('Token tidak valid');
    $user_login = $verif->id ?? $verif->kode ?? null;
    $no_faktur_lama = $input['no_faktur_lama'] ?? null;
    $no_faktur_baru = $input['no_faktur_baru'] ?? null;
    $nominal_revisi = $input['nominal_revisi'] ?? 0;
    $new_status = $input['status'] ?? null;
    $new_status_kontra = $input['status_kontra'] ?? 'Belum';
    $new_status_bayar = $input['status_bayar'] ?? 'Belum';
    $new_status_pinjam = $input['status_pinjam'] ?? 'Tidak';
    $nama_user_cek = trim($input['nama_user_cek'] ?? '');
    $kode_otorisasi = $input['kode_otorisasi'] ?? '';
    $input_tgl_diterima = $input['tgl_diterima'] ?? null;
    $penerima = $input['penerima'] ?? '';
    if (!$no_faktur_lama || !$new_status)
        throw new Exception("Data tidak lengkap (Faktur/Status).");
    if (empty($nama_user_cek) || empty($kode_otorisasi))
        throw new Exception("Otorisasi wajib diisi.");
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
    $tgl_diterima = !empty($input_tgl_diterima) ? $input_tgl_diterima : date('Y-m-d');
    if ($no_faktur_baru !== $no_faktur_lama) {
        $stmt_cek = $conn->prepare("SELECT no_faktur FROM serah_terima_nota WHERE no_faktur = ? AND no_faktur != ?");
        $stmt_cek->bind_param("ss", $no_faktur_baru, $no_faktur_lama);
        $stmt_cek->execute();
        if ($stmt_cek->get_result()->num_rows > 0) {
            throw new Exception("Gagal: No Faktur '$no_faktur_baru' sudah ada di database (Duplikat).");
        }
        $stmt_cek->close();
    }
    $sql = "UPDATE serah_terima_nota SET 
                no_faktur = ?, 
                nominal_revisi = ?,
                status = ?, 
                status_kontra = ?, 
                status_bayar = ?, 
                status_pinjam = ?, 
                tgl_diterima = ?, 
                penerima = ?, 
                edit_pada = NOW(), 
                diedit_oleh = ? 
            WHERE no_faktur = ?";
    $stmt_upd = $conn->prepare($sql);
    $stmt_upd->bind_param(
        "sdssssssss",
        $no_faktur_baru,
        $nominal_revisi,
        $new_status,
        $new_status_kontra,
        $new_status_bayar,
        $new_status_pinjam,
        $tgl_diterima,
        $penerima,
        $user_login,
        $no_faktur_lama
    );
    if (!$stmt_upd->execute()) {
        throw new Exception("Gagal update data: " . $stmt_upd->error);
    }
    echo json_encode([
        'success' => true,
        'message' => "Data berhasil diperbarui."
    ]);
} catch (Exception $e) {
    http_response_code(200);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>