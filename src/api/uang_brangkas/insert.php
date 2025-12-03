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
    if (!preg_match('/^Bearer\s(\S+)$/', $authHeader, $matches)) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Token tidak ditemukan']);
        exit;
    }
    $token = $matches[1];
    $verif = verify_token($token);
    if (!$verif) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Token tidak valid']);
        exit;
    }
    $user_hitung = $verif->id ?? $verif->kode ?? null;
    if (empty($user_hitung)) {
        throw new Exception("ID User tidak valid atau tidak ditemukan dalam token.");
    }
    $input = json_decode(file_get_contents('php://input'), true);
    $tanggal = $input['tanggal'] ?? date('Y-m-d');
    $jam = $input['jam'] ?? date('H:i:s');
    $user_cek = $input['user_cek'] ?? null;
    $kode_otorisasi = $input['kode_otorisasi'] ?? '';
    $keterangan = $input['keterangan'] ?? '';
    if (empty($user_cek)) {
        throw new Exception("User Cek wajib diisi.");
    }
    if (empty($kode_otorisasi)) {
        throw new Exception("Kode Otorisasi wajib diisi.");
    }
    $sql_auth = "SELECT kode_user FROM otorisasi_user WHERE kode_user = ? AND PASSWORD = ? AND tanggal = ?";
    $stmt_auth = $conn->prepare($sql_auth);
    $stmt_auth->bind_param("iss", $user_cek, $kode_otorisasi, $tanggal);
    $stmt_auth->execute();
    if ($stmt_auth->get_result()->num_rows === 0) {
        throw new Exception("Otorisasi Gagal! Kode salah atau tidak berlaku untuk User Cek pada tanggal tersebut.");
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
    $sql_insert = "
        INSERT INTO uang_brangkas (
            tanggal, jam, user_hitung, user_cek, kode_otorisasi_input,
            qty_100rb, qty_50rb, qty_20rb, qty_10rb, qty_5rb, qty_2rb, qty_1rb,
            qty_1000_koin, qty_500_koin, qty_200_koin, qty_100_koin,
            total_nominal, keterangan
        ) VALUES (
            ?, ?, ?, ?, ?,
            ?, ?, ?, ?, ?, ?, ?,
            ?, ?, ?, ?,
            ?, ?
        )
    ";
    $stmt = $conn->prepare($sql_insert);
    if (!$stmt)
        throw new Exception("Database Error: " . $conn->error);
    $types = "ssiis" . str_repeat("i", 11) . "ds";
    $params = array_merge(
        [$tanggal, $jam, $user_hitung, $user_cek, $kode_otorisasi],
        $qty_values,
        [$total_nominal, $keterangan]
    );
    $stmt->bind_param($types, ...$params);
    if (!$stmt->execute()) {
        if ($conn->errno == 1062) {
            throw new Exception("Data duplikat! Anda sudah melakukan hitung pada tanggal dan jam yang sama.");
        }
        throw new Exception("Gagal Insert: " . $stmt->error);
    }
    $stmt->close();
    $conn->commit();
    echo json_encode([
        'success' => true,
        'message' => 'Data uang brangkas berhasil disimpan.',
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