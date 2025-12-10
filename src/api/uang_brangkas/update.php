<?php
session_start();
ini_set('display_errors', 0);
require_once __DIR__ . '/../../../aa_kon_sett.php';
require_once __DIR__ . '/../../auth/middleware_login.php';
header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method Not Allowed');
    }
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    if (preg_match('/^Bearer\s(\S+)$/', $authHeader, $matches)) {
        $token = $matches[1];
        if (!verify_token($token)) {
            throw new Exception("Sesi login berakhir/tidak valid.");
        }
    } else {
        throw new Exception("Token otorisasi diperlukan.");
    }
    $input = json_decode(file_get_contents('php://input'), true);
    $pk_tanggal = $input['pk_tanggal'] ?? null;
    $pk_jam = $input['pk_jam'] ?? null;
    $pk_user_hitung = $input['pk_user_hitung'] ?? null;
    if (!$pk_tanggal || !$pk_jam || !$pk_user_hitung) {
        throw new Exception("Data tidak valid. Primary Key hilang.");
    }
    $nama_user_cek_input = trim($input['nama_user_cek'] ?? '');
    $kode_otorisasi = $input['kode_otorisasi'] ?? '';
    $keterangan = $input['keterangan'] ?? '';
    if (empty($nama_user_cek_input) || empty($kode_otorisasi)) {
        throw new Exception("Update memerlukan Nama User Cek dan Kode Otorisasi.");
    }
    $sql_cari_user = "SELECT kode FROM user_account WHERE inisial = ? LIMIT 1";
    $stmt_cari = $conn->prepare($sql_cari_user);
    $stmt_cari->bind_param("s", $nama_user_cek_input);
    $stmt_cari->execute();
    $res_cari = $stmt_cari->get_result();
    if ($res_cari->num_rows === 0) {
        throw new Exception("User dengan nama '$nama_user_cek_input' tidak ditemukan.");
    }
    $row_user = $res_cari->fetch_assoc();
    $user_cek = $row_user['kode'];
    $stmt_cari->close();

    // --- UBAH DISINI: HAPUS TANGGAL DARI CEK OTORISASI ---
    $sql_auth = "SELECT kode_user FROM otorisasi_user WHERE kode_user = ? AND PASSWORD = ?";
    $stmt_auth = $conn->prepare($sql_auth);
    $stmt_auth->bind_param("is", $user_cek, $kode_otorisasi);
    $stmt_auth->execute();

    if ($stmt_auth->get_result()->num_rows === 0) {
        throw new Exception("Otorisasi Gagal! Kode salah atau User '$nama_user_cek_input' belum set otorisasi.");
    }
    $stmt_auth->close();

    $denominations = [
        'qty_100rb' => 100000,
        'qty_50rb' => 50000,
        'qty_20rb' => 20000,
        'qty_10rb' => 10000,
        'qty_5rb' => 5000,
        'qty_2rb' => 2000,
        'qty_1rb' => 1000,
        'qty_1000_koin' => 1000,
        'qty_500_koin' => 500,
        'qty_200_koin' => 200,
        'qty_100_koin' => 100
    ];
    $total_nominal = 0;
    $qty_values = [];
    foreach ($denominations as $key => $val) {
        $qty = (int) ($input[$key] ?? 0);
        $qty_values[] = $qty;
        $total_nominal += ($qty * $val);
    }
    $conn->begin_transaction();
    $sql_update = "
        UPDATE uang_brangkas SET
            user_cek = ?,
            kode_otorisasi_input = ?,
            qty_100rb = ?, qty_50rb = ?, qty_20rb = ?, qty_10rb = ?, 
            qty_5rb = ?, qty_2rb = ?, qty_1rb = ?,
            qty_1000_koin = ?, qty_500_koin = ?, qty_200_koin = ?, qty_100_koin = ?,
            total_nominal = ?,
            keterangan = ?
        WHERE 
            tanggal = ? AND jam = ? AND user_hitung = ?
    ";
    $stmt = $conn->prepare($sql_update);
    $types = "is" . str_repeat("i", 11) . "dsssi";
    $params = array_merge(
        [$user_cek, $kode_otorisasi],
        $qty_values,
        [$total_nominal, $keterangan, $pk_tanggal, $pk_jam, $pk_user_hitung]
    );
    $stmt->bind_param($types, ...$params);
    if (!$stmt->execute()) {
        throw new Exception("Gagal Update: " . $stmt->error);
    }
    $stmt->close();
    $conn->commit();
    echo json_encode([
        'success' => true,
        'message' => 'Data berhasil diperbarui.',
        'total_nominal' => $total_nominal
    ]);
} catch (Exception $e) {
    if (isset($conn))
        $conn->rollback();
    http_response_code(200);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} finally {
    if (isset($conn))
        $conn->close();
}
?>