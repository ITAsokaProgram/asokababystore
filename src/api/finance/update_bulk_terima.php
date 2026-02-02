<?php
session_start();
ini_set('display_errors', 0);
header('Content-Type: application/json');
require_once __DIR__ . '/../../../aa_kon_sett.php';
require_once __DIR__ . '/../../auth/middleware_login.php';
require_once __DIR__ . '/../../helpers/finance_log_helper.php';
try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method Not Allowed');
    }
    $input = json_decode(file_get_contents('php://input'), true);
    $verif = authenticate_request();
    $user_login = $verif->id ?? $verif->kode ?? null;
    $fakturs = $input['fakturs'] ?? []; 
    $tgl_terima = $input['tgl_terima'] ?? null;
    $penerima = trim($input['penerima'] ?? '');
    $nama_user_cek = trim($input['nama_user_cek'] ?? '');
    $kode_otorisasi = $input['kode_otorisasi'] ?? '';
    if (empty($fakturs)) throw new Exception("Tidak ada data yang dipilih.");
    if (empty($tgl_terima)) throw new Exception("Tanggal Terima wajib diisi.");
    if (empty($penerima)) throw new Exception("Nama Penerima wajib diisi.");
    if (empty($nama_user_cek) || empty($kode_otorisasi)) throw new Exception("Otorisasi (User & Password) wajib diisi.");
    $stmt_cari = $conn->prepare("SELECT kode FROM user_account WHERE inisial = ? LIMIT 1");
    $stmt_cari->bind_param("s", $nama_user_cek);
    $stmt_cari->execute();
    $res_cari = $stmt_cari->get_result();
    if ($res_cari->num_rows === 0) throw new Exception("User Otorisasi '$nama_user_cek' tidak ditemukan.");
    $user_auth_data = $res_cari->fetch_assoc();
    $kode_user_auth = $user_auth_data['kode'];
    $stmt_cari->close();
    $stmt_auth = $conn->prepare("SELECT kode_user FROM otorisasi_user WHERE kode_user = ? AND PASSWORD = ?");
    $stmt_auth->bind_param("is", $kode_user_auth, $kode_otorisasi);
    $stmt_auth->execute();
    if ($stmt_auth->get_result()->num_rows === 0) throw new Exception("Password Otorisasi Salah!");
    $stmt_auth->close();
    $conn->begin_transaction();
    $success_count = 0;
    $stmt_upd = $conn->prepare("UPDATE serah_terima_nota SET status = 'Sudah Terima', tgl_diterima = ?, penerima = ?, edit_pada = NOW(), diedit_oleh = ? WHERE no_faktur = ?");
    foreach ($fakturs as $no_faktur) {
        $stmt_upd->bind_param("ssss", $tgl_terima, $penerima, $user_login, $no_faktur);
        $stmt_upd->execute();
        if ($stmt_upd->affected_rows > 0) {
            $success_count++;
        }
    }
    $stmt_upd->close();
    $conn->commit();
    echo json_encode([
        'success' => true,
        'message' => "Berhasil mengupdate $success_count data menjadi Sudah Terima."
    ]);
} catch (Exception $e) {
    if (isset($conn)) $conn->rollback();
    http_response_code(200); 
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>