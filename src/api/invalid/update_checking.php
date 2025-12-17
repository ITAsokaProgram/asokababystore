<?php
require_once __DIR__ . "/../../../aa_kon_sett.php";
require_once __DIR__ . "/../../auth/middleware_login.php";
header("Content-Type:application/json");
header("Access-Control-Allow-Methods: POST");
date_default_timezone_set('Asia/Jakarta');

$headers = getallheaders();
$authHeader = $headers['Authorization'] ?? '';
$token = null;

if (preg_match('/^Bearer\s(\S+)$/', $authHeader, $matches)) {
    $token = $matches[1];
}

if (!$token) {
    http_response_code(401);
    echo json_encode(['status' => "Unauthenticated", 'message' => 'Request ditolak, token tidak ditemukan']);
    exit;
}

$verif = verify_token($token);
$inputJSON = json_decode(file_get_contents('php://input'), true);

try {
    $nama_user_cek_input = trim($inputJSON['nama_user_cek'] ?? '');
    $kode_otorisasi = $inputJSON['kode_otorisasi'] ?? '';

    if (empty($nama_user_cek_input)) {
        throw new Exception("Nama User Check (Inisial) wajib diisi.");
    }
    if (empty($kode_otorisasi)) {
        throw new Exception("Kode Otorisasi wajib diisi.");
    }

    $sql_cari_user = "SELECT kode FROM user_account WHERE inisial = ? LIMIT 1";
    $stmt_cari = $conn->prepare($sql_cari_user);
    if (!$stmt_cari)
        throw new Exception("DB Error: " . $conn->error);

    $stmt_cari->bind_param("s", $nama_user_cek_input);
    $stmt_cari->execute();
    $res_cari = $stmt_cari->get_result();

    if ($res_cari->num_rows === 0) {
        throw new Exception("User dengan inisial '$nama_user_cek_input' tidak ditemukan.");
    }

    $row_user = $res_cari->fetch_assoc();
    $user_cek_kode = $row_user['kode'];
    $stmt_cari->close();

    $sql_auth = "SELECT kode_user FROM otorisasi_user WHERE kode_user = ? AND PASSWORD = ?";
    $stmt_auth = $conn->prepare($sql_auth);
    if (!$stmt_auth)
        throw new Exception("DB Error: " . $conn->error);

    $stmt_auth->bind_param("is", $user_cek_kode, $kode_otorisasi);
    $stmt_auth->execute();

    if ($stmt_auth->get_result()->num_rows === 0) {
        throw new Exception("Otorisasi Gagal! Password salah atau User belum set otorisasi.");
    }
    $stmt_auth->close();

    $items = isset($inputJSON['items']) ? $inputJSON['items'] : [$inputJSON];
    $ket_global = $inputJSON['ket'] ?? '';
    $nama_cek_input = $nama_user_cek_input; // Ini variabel nama user yang menginput
    $tipe_cek = $inputJSON['tipe_cek'] ?? 'area';

    if (empty($items)) {
        throw new Exception('Tidak ada data yang dikirim', 400);
    }

    $successCount = 0;

    $sql_select = "SELECT nama_cek, ket_cek FROM invtrans WHERE kode_toko = ? AND plu = ? AND kode_kasir = ? AND tgl_trans = ? AND jam_trs = ?";
    $stmt_sel = $conn->prepare($sql_select);

    $sql_update = "UPDATE invtrans SET ket_cek = ?, nama_cek = ? WHERE kode_toko = ? AND plu = ? AND kode_kasir = ? AND tgl_trans = ? AND jam_trs = ?";
    $stmt_upd = $conn->prepare($sql_update);

    foreach ($items as $item) {
        $plu = $item['plu'];
        $kode_toko = $item['kd_store'] ?? $item['cabang'] ?? $item['toko'] ?? '';
        $kode_kasir = $item['kasir'] ?? $item['kode_kasir'] ?? '';
        $tgl_trans = $item['tgl'];
        $jam_trans = $item['jam'];
        $ket_input = !empty($item['ket']) ? $item['ket'] : $ket_global;

        $stmt_sel->bind_param("sssss", $kode_toko, $plu, $kode_kasir, $tgl_trans, $jam_trans);
        $stmt_sel->execute();
        $res_sel = $stmt_sel->get_result();

        if ($res_sel->num_rows > 0) {
            $old_data = $res_sel->fetch_assoc();

            $nama_parts = explode(',', $old_data['nama_cek'] ?? '');
            $ket_parts = explode(',', $old_data['ket_cek'] ?? '');

            // Pastikan array length minimal 2 (Index 0: Area, Index 1: Leader)
            if (count($nama_parts) < 2)
                $nama_parts = array_pad($nama_parts, 2, '');
            if (count($ket_parts) < 2)
                $ket_parts = array_pad($ket_parts, 2, '');

            if ($tipe_cek === 'area') {
                $nama_parts[0] = $nama_cek_input;
                $ket_parts[0] = $ket_input;
            } else if ($tipe_cek === 'leader') {
                $nama_parts[1] = $nama_cek_input;
                $ket_parts[1] = $ket_input;
            }

            // Gabungkan kembali dengan koma
            $nama_final = $nama_parts[0] . ',' . $nama_parts[1];
            $ket_final = $ket_parts[0] . ',' . $ket_parts[1];

            $stmt_upd->bind_param("sssssss", $ket_final, $nama_final, $kode_toko, $plu, $kode_kasir, $tgl_trans, $jam_trans);

            if ($stmt_upd->execute()) {
                $successCount++;
            }
        }
    }

    $stmt_sel->close();
    $stmt_upd->close();

    if ($successCount > 0) {
        http_response_code(200);
        // PERBAIKAN DISINI: Ganti $nama_cek_final menjadi $nama_cek_input
        echo json_encode([
            'status' => 'success',
            'message' => "Berhasil update $successCount data (Otorisasi: $nama_cek_input)"
        ]);
    } else {
        throw new Exception('Gagal mengupdate data atau data tidak ditemukan/tidak berubah', 500);
    }

} catch (Exception $e) {
    if (isset($conn))
        $conn->close();

    $code = $e->getCode() ?: 500;
    if ($code < 100 || $code > 599)
        $code = 500;

    http_response_code($code);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>