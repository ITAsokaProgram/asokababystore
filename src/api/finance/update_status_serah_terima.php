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
    $no_faktur_lama = $input['no_faktur_lama'] ?? null;
    $mode = $input['mode'] ?? 'full';
    $nama_user_cek = trim($input['nama_user_cek'] ?? '');
    $kode_otorisasi = $input['kode_otorisasi'] ?? '';
    if (!$no_faktur_lama)
        throw new Exception("Data tidak valid (Faktur Lama hilang).");
    if (empty($nama_user_cek) || empty($kode_otorisasi))
        throw new Exception("User dan Password Otorisasi wajib diisi.");
    $stmt_cari = $conn->prepare("SELECT kode FROM user_account WHERE inisial = ? LIMIT 1");
    $stmt_cari->bind_param("s", $nama_user_cek);
    $stmt_cari->execute();
    $res_cari = $stmt_cari->get_result();
    if ($res_cari->num_rows === 0)
        throw new Exception("User Otorisasi '$nama_user_cek' tidak ditemukan.");
    $user_auth_data = $res_cari->fetch_assoc();
    $kode_user_auth = $user_auth_data['kode'];
    $stmt_cari->close();
    $stmt_auth = $conn->prepare("SELECT kode_user FROM otorisasi_user WHERE kode_user = ? AND PASSWORD = ?");
    $stmt_auth->bind_param("is", $kode_user_auth, $kode_otorisasi);
    $stmt_auth->execute();
    if ($stmt_auth->get_result()->num_rows === 0)
        throw new Exception("Password Otorisasi Salah!");
    $stmt_auth->close();
    $stmt_old = $conn->prepare("SELECT * FROM serah_terima_nota WHERE no_faktur = ?");
    $stmt_old->bind_param("s", $no_faktur_lama);
    $stmt_old->execute();
    $res_old = $stmt_old->get_result();
    $old_data = $res_old->fetch_assoc();
    $stmt_old->close();
    if (!$old_data)
        throw new Exception("Data nota lama tidak ditemukan di database.");
    if ($mode === 'status' || $mode === 'full') {
        $input_faktur_baru = $input['no_faktur_baru'] ?? $old_data['no_faktur_format'];
        $no_faktur_baru_clean = !empty($input['no_faktur_baru']) ? preg_replace('/[^a-zA-Z0-9]/', '', $input_faktur_baru) : $old_data['no_faktur'];
        $nominal = isset($input['nominal']) ? (float) $input['nominal'] : (float) $old_data['nominal'];
        $new_status = $input['status'] ?? 'Belum Terima';
        $new_status_kontra = $input['status_kontra'] ?? $old_data['status_kontra'];
        $new_status_bayar = $input['status_bayar'] ?? $old_data['status_bayar'];
        $new_status_pinjam = $input['status_pinjam'] ?? $old_data['status_pinjam'];
        $input_tgl_diterima = !empty($input['tgl_diterima']) ? $input['tgl_diterima'] : $old_data['tgl_diterima'];
        $penerima = !empty($input['penerima']) ? trim($input['penerima']) : $old_data['penerima'];
    } else {
        $input_faktur_baru = $old_data['no_faktur_format'];
        $no_faktur_baru_clean = $old_data['no_faktur'];
        $nominal = (float) $old_data['nominal'];
        $new_status = $old_data['status'];
        $new_status_kontra = $old_data['status_kontra'];
        $new_status_bayar = $old_data['status_bayar'];
        $new_status_pinjam = $old_data['status_pinjam'];
        $input_tgl_diterima = $old_data['tgl_diterima'];
        $penerima = $old_data['penerima'];
    }
    $getVal = function ($key) use ($input, $old_data, $mode) {
        if ($mode === 'kontra' || $mode === 'full') {
            return array_key_exists($key, $input) ? ($input[$key] ?: null) : $old_data[$key];
        } else {
            return $old_data[$key];
        }
    };
    $r_tanggal_tukar_faktur = $getVal('r_tanggal_tukar_faktur');
    $r_diterima_oleh = $getVal('r_diterima_oleh');
    $r_tgl_diserahkan_ke_md = $getVal('r_tgl_diserahkan_ke_md');
    $md_divalidasi_oleh = $getVal('md_divalidasi_oleh');
    $md_tgl_diserahkan_ke_finance = $getVal('md_tgl_diserahkan_ke_finance');
    $finance_divalidasi_oleh = $getVal('finance_divalidasi_oleh');
    $tax_tgl_diterima_dari_r = $getVal('tax_tgl_diterima_dari_r');
    $tax_divalidasi_oleh = $getVal('tax_divalidasi_oleh');
    $ket = $getVal('ket');
    if ($no_faktur_baru_clean !== $no_faktur_lama) {
        $stmt_cek = $conn->prepare("SELECT no_faktur FROM serah_terima_nota WHERE no_faktur = ? AND no_faktur != ?");
        $stmt_cek->bind_param("ss", $no_faktur_baru_clean, $no_faktur_lama);
        $stmt_cek->execute();
        if ($stmt_cek->get_result()->num_rows > 0) {
            throw new Exception("Gagal: No Faktur '$input_faktur_baru' sudah digunakan.");
        }
        $stmt_cek->close();
    }
    if ($mode === 'status') {
        if ($new_status_kontra === 'Sudah' && ($new_status !== 'Sudah Terima' || empty($penerima) || empty($input_tgl_diterima))) {
            throw new Exception("Status Kontra 'Sudah' butuh Status Terima, Tanggal, dan Penerima.");
        }
    }
    $tgl_db = (!empty($input_tgl_diterima) && $input_tgl_diterima !== '0000-00-00') ? $input_tgl_diterima : null;
    $sql = "UPDATE serah_terima_nota SET 
                no_faktur = ?, no_faktur_format = ?, nominal = ?, status = ?, 
                status_kontra = ?, status_bayar = ?, status_pinjam = ?, 
                tgl_diterima = ?, penerima = ?, 
                r_tanggal_tukar_faktur = ?, r_diterima_oleh = ?, r_tgl_diserahkan_ke_md = ?, 
                md_divalidasi_oleh = ?, md_tgl_diserahkan_ke_finance = ?, 
                finance_divalidasi_oleh = ?, 
                tax_tgl_diterima_dari_r = ?, tax_divalidasi_oleh = ?, 
                ket = ?, 
                edit_pada = NOW(), diedit_oleh = ? 
            WHERE no_faktur = ?";
    $stmt_upd = $conn->prepare($sql);
    $stmt_upd->bind_param(
        "ssdsssssssssssssssss",
        $no_faktur_baru_clean,
        $input_faktur_baru,
        $nominal,
        $new_status,
        $new_status_kontra,
        $new_status_bayar,
        $new_status_pinjam,
        $tgl_db,
        $penerima,
        $r_tanggal_tukar_faktur,
        $r_diterima_oleh,
        $r_tgl_diserahkan_ke_md,
        $md_divalidasi_oleh,
        $md_tgl_diserahkan_ke_finance,
        $finance_divalidasi_oleh,
        $tax_tgl_diterima_dari_r,
        $tax_divalidasi_oleh,
        $ket,
        $user_login,
        $no_faktur_lama
    );
    if (!$stmt_upd->execute()) {
        throw new Exception("Database Error: " . $stmt_upd->error);
    }
    $newData = array_merge($old_data, $input);
    $newData['otorisasi_oleh'] = $nama_user_cek;
    write_finance_log($conn, $user_login, 'serah_terima_nota', $no_faktur_lama, 'UPDATE', $old_data, $newData);
    echo json_encode([
        'success' => true,
        'message' => "Data ($mode) berhasil diperbarui."
    ]);
} catch (Exception $e) {
    http_response_code(200);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>