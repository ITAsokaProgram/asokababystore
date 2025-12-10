<?php
require_once __DIR__ . "/../../../aa_kon_sett.php";
require_once __DIR__ . "/../../auth/middleware_login.php";

header("Content-Type:application/json");
header("Access-Control-Allow-Methods: POST");
date_default_timezone_set('Asia/Jakarta');

try {
    $headers = getallheaders();
    $authHeader = $headers['Authorization'] ?? '';
    $token = null;
    if (preg_match('/^Bearer\s(\S+)$/', $authHeader, $matches)) {
        $token = $matches[1];
    }
    if (!$token) {
        throw new Exception('Request ditolak user tidak terdaftar', 401);
    }
    $verif = verify_token($token);

    $input = json_decode(file_get_contents("php://input"), true);

    $nama_user_cek_input = trim($input['nama_user_cek'] ?? '');
    $kode_otorisasi = $input['kode_otorisasi'] ?? '';

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

    $items = isset($input['items']) ? $input['items'] : [];
    if (empty($items) && isset($input['plu'])) {
        $items[] = $input;
    }

    $ket_global = $input['keterangan'] ?? ($input['ket'] ?? '-');
    $nama_user = $input['nama'] ?? 'User';
    $tanggal_timestamp = date('Y-m-d H:i:s');

    if (empty($items)) {
        throw new Exception('Tidak ada data yang dikirim', 400);
    }

    $sql = "INSERT INTO margin
    (plu, no_bon, descp, qty, gross, net, avg_cost, ppn, margin_min, tanggal, kd_store, cabang, ket_cek, nama_cek, status_cek, tanggal_cek)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, ?)
    ON DUPLICATE KEY UPDATE 
        ket_cek = VALUES(ket_cek),
        nama_cek = VALUES(nama_cek),
        status_cek = 1,
        tanggal_cek = VALUES(tanggal_cek)";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception('Server Error: ' . $conn->error, 500);
    }

    $successCount = 0;
    foreach ($items as $item) {
        $plu = $item['plu'];
        $bon = $item['bon'] ?? $item['no_bon'];
        $barang = $item['barang'] ?? $item['descp'];
        $qty = $item['qty'];
        $gros = $item['gros'] ?? $item['gross'] ?? 0;
        $net = $item['net'] ?? 0;
        $avg = $item['avg'] ?? $item['avg_cost'] ?? 0;
        $ppn = $item['ppn'] ?? 0;
        $margin = $item['margin'] ?? 0;
        $tgl = $item['tgl'] ?? $item['tanggal'];
        $kd_store = $item['kd'] ?? $item['kd_store'];
        $cabang = $item['cabang'];

        $ket = !empty($item['keterangan']) ? $item['keterangan'] : $ket_global;
        $nama_cek_final = $nama_user_cek_input;

        $stmt->bind_param(
            "sssidddddssssss",
            $plu,
            $bon,
            $barang,
            $qty,
            $gros,
            $net,
            $avg,
            $ppn,
            $margin,
            $tgl,
            $kd_store,
            $cabang,
            $ket,
            $nama_cek_final,
            $tanggal_timestamp
        );

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
            'message' => "Berhasil update $successCount data (Otorisasi: $nama_user_cek_input)"
        ]);
    } else {
        throw new Exception('Gagal update data atau data tidak berubah', 500);
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