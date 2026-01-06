<?php
session_start();
ini_set('display_errors', 0);
header('Content-Type: application/json');
require_once __DIR__ . '/../../../aa_kon_sett.php';
require_once __DIR__ . '/../../auth/middleware_login.php';
require_once __DIR__ . '/../../helpers/surat_terima_nota_helper.php';
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
    $user_login = $verif->id ?? $verif->kode ?? null;
    $no_faktur_lama = $input['no_faktur_lama'] ?? null;
    $input_faktur_baru = $input['no_faktur_baru'] ?? null;
    $no_faktur_baru_clean = preg_replace('/[^a-zA-Z0-9]/', '', $input_faktur_baru);
    $nominal_input = isset($input['nominal']) ? $input['nominal'] : null;
    $new_status = $input['status'] ?? 'Belum Terima';
    $input_tgl_diterima = $input['tgl_diterima'] ?? null;
    $penerima = trim($input['penerima'] ?? '');
    $new_status_kontra = $input['status_kontra'] ?? 'Belum';
    $new_status_bayar = $input['status_bayar'] ?? 'Belum';
    $new_status_pinjam = $input['status_pinjam'] ?? 'Tidak';
    $nama_user_cek = trim($input['nama_user_cek'] ?? '');
    $kode_otorisasi = $input['kode_otorisasi'] ?? '';
    if (!$no_faktur_lama) {
        throw new Exception("Data tidak valid (Faktur Lama hilang).");
    }
    if (empty($nama_user_cek) || empty($kode_otorisasi)) {
        throw new Exception("User dan Password Otorisasi wajib diisi.");
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
    $stmt_old = $conn->prepare("SELECT * FROM serah_terima_nota WHERE no_faktur = ?");
    $stmt_old->bind_param("s", $no_faktur_lama);
    $stmt_old->execute();
    $res_old = $stmt_old->get_result();
    $old_data = $res_old->fetch_assoc();
    $stmt_old->close();
    if (!$old_data) {
        throw new Exception("Data nota lama tidak ditemukan di database.");
    }
    if ($nominal_input !== null) {
        $nominal = (float) $nominal_input;
    } else {
        $nominal = (float) $old_data['nominal'];
    }
    // if ($old_data['status_bayar'] === 'Sudah') {
    //     if ($no_faktur_baru_clean !== $no_faktur_lama) {
    //         throw new Exception("Gagal: No Faktur tidak dapat diubah karena status sudah Dibayar.");
    //     }
    //     if (abs((float) $nominal - (float) $old_data['nominal']) > 1.0) {
    //         throw new Exception("Gagal: Nominal tidak dapat diubah karena status sudah Dibayar.");
    //     }
    // }
    if ($old_data['status'] === 'Sudah Terima' && !empty($old_data['tgl_diterima']) && !empty($old_data['penerima'])) {
        if ($input_tgl_diterima !== $old_data['tgl_diterima']) {
            $input_tgl_diterima = $old_data['tgl_diterima'];
        }
        if ($penerima !== $old_data['penerima']) {
            $penerima = $old_data['penerima'];
        }
    }
    $is_setting_kontra = ($new_status_kontra === 'Sudah');
    $is_setting_bayar = ($new_status_bayar === 'Sudah');
    $is_setting_pinjam = ($new_status_pinjam === 'Pinjam');
    if ($is_setting_kontra || $is_setting_bayar || $is_setting_pinjam) {
        if ($new_status !== 'Sudah Terima') {
            throw new Exception("Gagal: Status harus 'Sudah Terima' sebelum mengisi Kontra, Bayar, atau Pinjam.");
        }
        if (empty($penerima)) {
            throw new Exception("Gagal: Nama Penerima wajib diisi.");
        }
        if (empty($input_tgl_diterima)) {
            throw new Exception("Gagal: Tanggal Diterima wajib diisi.");
        }
    }
    if ($old_data['status_kontra'] === 'Sudah' && $new_status_kontra === 'Belum') {
        throw new Exception("Gagal: Status Kontra yang sudah selesai tidak dapat diubah kembali ke Belum.");
    }
    if ($old_data['status_bayar'] === 'Sudah' && $new_status_bayar === 'Belum') {
        throw new Exception("Gagal: Status Bayar yang sudah selesai tidak dapat diubah kembali ke Belum.");
    }
    if ($no_faktur_baru_clean !== $no_faktur_lama) {
        $stmt_cek = $conn->prepare("SELECT no_faktur FROM serah_terima_nota WHERE no_faktur = ? AND no_faktur != ?");
        $stmt_cek->bind_param("ss", $no_faktur_baru_clean, $no_faktur_lama);
        $stmt_cek->execute();
        if ($stmt_cek->get_result()->num_rows > 0) {
            throw new Exception("Gagal: No Faktur '$input_faktur_baru' (ID: $no_faktur_baru_clean) sudah digunakan.");
        }
        $stmt_cek->close();
    }
    $tgl_db = !empty($input_tgl_diterima) ? $input_tgl_diterima : null;
    if ($new_status === 'Sudah Terima') {
        if (empty($penerima)) {
            throw new Exception("Gagal: Nama Penerima wajib diisi.");
        }
        if (empty($tgl_db)) {
            throw new Exception("Gagal: Tanggal Diterima wajib diisi (Jangan Kosong).");
        }
    }
    $sql = "UPDATE serah_terima_nota SET 
                no_faktur = ?, 
                no_faktur_format = ?, 
                nominal = ?, 
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
        "ssdssssssss",
        $no_faktur_baru_clean,
        $input_faktur_baru,
        $nominal,
        $new_status,
        $new_status_kontra,
        $new_status_bayar,
        $new_status_pinjam,
        $tgl_db,
        $penerima,
        $user_login,
        $no_faktur_lama
    );
    if (!$stmt_upd->execute()) {
        throw new Exception("Database Error: " . $stmt_upd->error);
    }
    $log_new_data = [
        'no_faktur' => $no_faktur_baru_clean,
        'no_faktur_format' => $input_faktur_baru,
        'nominal' => $nominal,
        'status' => $new_status,
        'status_kontra' => $new_status_kontra,
        'status_bayar' => $new_status_bayar,
        'status_pinjam' => $new_status_pinjam,
        'tgl_diterima' => $tgl_db,
        'penerima' => $penerima,
        'diotorisasi_oleh' => $nama_user_cek
    ];
    log_nota($conn, $user_login, 'UPDATE_STATUS', $no_faktur_lama, $old_data, $log_new_data);
    echo json_encode([
        'success' => true,
        'message' => "Data berhasil diperbarui."
    ]);
} catch (Exception $e) {
    http_response_code(200);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>