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

    // --- UBAH DISINI: HAPUS TANGGAL DARI CEK OTORISASI ---
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
    $nama_cek_final = $nama_user_cek_input;
    if (empty($items)) {
        throw new Exception('Tidak ada data yang dikirim', 400);
    }
    $successCount = 0;
    $sql_update = "UPDATE invtrans SET ket_cek = ?, nama_cek = ? WHERE kode_toko = ? AND plu = ? AND kode_kasir = ? AND tgl_trans = ? AND jam_trs = ?";
    $stmt = $conn->prepare($sql_update);
    if (!$stmt)
        throw new Exception("DB Error: " . $conn->error);
    foreach ($items as $item) {
        $plu = $item['plu'];
        $kode_toko = $item['kd_store'] ?? $item['cabang'] ?? $item['toko'] ?? '';
        $kode_kasir = $item['kasir'] ?? $item['kode_kasir'] ?? '';
        $tgl_trans = $item['tgl'];
        $jam_trans = $item['jam'];
        $keterangan = !empty($item['ket']) ? $item['ket'] : $ket_global;
        $stmt->bind_param("sssssss", $keterangan, $nama_cek_final, $kode_toko, $plu, $kode_kasir, $tgl_trans, $jam_trans);
        if ($stmt->execute()) {
            $successCount++;
        }
    }
    $stmt->close();
    $conn->close();
    if ($successCount > 0) {
        http_response_code(200);
        echo json_encode([
            'status' => 'success',
            'message' => "Berhasil update $successCount data (Otorisasi: $nama_cek_final)"
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