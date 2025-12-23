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

    $header = getAllHeaders();
    $authHeader = $header['Authorization'] ?? $header['authorization'] ?? '';
    if (!preg_match('/^Bearer\s(\S+)$/', $authHeader, $matches)) {
        http_response_code(401);
        exit(json_encode(['success' => false, 'message' => 'Token tidak ditemukan']));
    }

    $verif = verify_token($matches[1]);
    if (!$verif) {
        http_response_code(401);
        exit(json_encode(['success' => false, 'message' => 'Token tidak valid']));
    }

    $input = json_decode(file_get_contents('php://input'), true);

    // --- AMBIL KD_STORE DARI INPUT MODAL ---
    $kd_store_input = $input['kd_store'] ?? null;
    if (empty($kd_store_input)) {
        throw new Exception("Cabang Toko wajib dipilih.");
    }

    $user_hitung = $verif->id ?? $verif->kode ?? null;
    $tanggal = $input['tanggal'] ?? date('Y-m-d');
    $jam = $input['jam'] ?? date('H:i:s');
    $nama_user_cek_input = trim($input['nama_user_cek'] ?? '');
    $kode_otorisasi = $input['kode_otorisasi'] ?? '';
    $keterangan = $input['keterangan'] ?? '';

    // Cari User Cek
    $stmt_cari = $conn->prepare("SELECT kode FROM user_account WHERE inisial = ? LIMIT 1");
    $stmt_cari->bind_param("s", $nama_user_cek_input);
    $stmt_cari->execute();
    $res_cari = $stmt_cari->get_result();
    if ($res_cari->num_rows === 0)
        throw new Exception("User Check '$nama_user_cek_input' tidak ditemukan.");
    $user_cek = $res_cari->fetch_assoc()['kode'];
    $stmt_cari->close();

    // Verifikasi Otorisasi
    $stmt_auth = $conn->prepare("SELECT kode_user FROM otorisasi_user WHERE kode_user = ? AND PASSWORD = ?");
    $stmt_auth->bind_param("is", $user_cek, $kode_otorisasi);
    $stmt_auth->execute();
    if ($stmt_auth->get_result()->num_rows === 0)
        throw new Exception("Otorisasi Gagal!");
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
    $sql_insert = "INSERT INTO uang_brangkas (
            tanggal, jam, user_hitung, user_cek, kd_store, kode_otorisasi_input,
            qty_100rb, qty_50rb, qty_20rb, qty_10rb, qty_5rb, qty_2rb, qty_1rb,
            qty_1000_koin, qty_500_koin, qty_200_koin, qty_100_koin, total_nominal, keterangan
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql_insert);
    $params = array_merge([$tanggal, $jam, $user_hitung, $user_cek, $kd_store_input, $kode_otorisasi], $qty_values, [$total_nominal, $keterangan]);
    $stmt->bind_param("ssisssiiiiiiiiiiids", ...$params);

    if (!$stmt->execute())
        throw new Exception("Gagal Insert: " . $stmt->error);

    $stmt->close();
    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Data berhasil disimpan.', 'total_nominal' => $total_nominal]);

} catch (Exception $e) {
    if (isset($conn))
        $conn->rollback();
    http_response_code(200);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}