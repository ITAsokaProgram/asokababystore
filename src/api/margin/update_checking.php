<?php
require_once __DIR__ . "/../../../aa_kon_sett.php";
require_once __DIR__ . "/../../auth/middleware_login.php";

header("Content-Type:application/json");
header("Access-Control-Allow-Methods: POST");

try {
    $verif = authenticate_request();

    $input = json_decode(file_get_contents("php://input"), true);

    $nama_user_cek_input = trim($input['nama_user_cek'] ?? '');
    $kode_otorisasi = $input['kode_otorisasi'] ?? '';
    $tipe_cek = $input['tipe_cek'] ?? 'area'; // Default area

    if (empty($nama_user_cek_input)) {
        throw new Exception("Nama User Check (Inisial) wajib diisi.");
    }
    if (empty($kode_otorisasi)) {
        throw new Exception("Kode Otorisasi wajib diisi.");
    }

    // --- 1. Validasi User & Otorisasi ---
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

    // --- 2. Persiapan Data ---
    $items = isset($input['items']) ? $input['items'] : [];
    if (empty($items) && isset($input['plu'])) {
        $items[] = $input;
    }

    $ket_global = $input['keterangan'] ?? ($input['ket'] ?? '-');
    $tanggal_timestamp = date('Y-m-d H:i:s');

    if (empty($items)) {
        throw new Exception('Tidak ada data yang dikirim', 400);
    }

    // Statement Select untuk ambil data lama (agar bisa di-explode)
    $sql_select = "SELECT nama_cek, ket_cek FROM margin WHERE plu = ? AND no_bon = ? AND kd_store = ? AND tanggal = ? LIMIT 1";
    $stmt_select = $conn->prepare($sql_select);

    // Statement Upsert
    $sql_upsert = "INSERT INTO margin 
    (plu, no_bon, descp, qty, gross, net, avg_cost, ppn, margin_min, tanggal, kd_store, cabang, ket_cek, nama_cek, status_cek, tanggal_cek) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, ?) 
    ON DUPLICATE KEY UPDATE 
        ket_cek = VALUES(ket_cek),
        nama_cek = VALUES(nama_cek),
        status_cek = 1,
        tanggal_cek = VALUES(tanggal_cek)";

    $stmt = $conn->prepare($sql_upsert);
    if (!$stmt)
        throw new Exception('Server Error: ' . $conn->error, 500);

    $successCount = 0;

    foreach ($items as $item) {
        $plu = $item['plu'];
        $bon = $item['bon'] ?? $item['no_bon'];
        $kd_store = $item['kd'] ?? $item['kd_store'];
        $tgl = $item['tgl'] ?? $item['tanggal'];
        $cabang = $item['cabang'];

        // Data lain
        $barang = $item['barang'] ?? $item['descp'] ?? '';
        $qty = $item['qty'] ?? 0;
        $gros = $item['gros'] ?? $item['gross'] ?? 0;
        $net = $item['net'] ?? 0;
        $avg = $item['avg'] ?? $item['avg_cost'] ?? 0;
        $ppn = $item['ppn'] ?? 0;
        $margin = $item['margin'] ?? 0;

        $ket_input = !empty($item['keterangan']) ? $item['keterangan'] : $ket_global;

        // A. Ambil data lama
        $stmt_select->bind_param("ssss", $plu, $bon, $kd_store, $tgl);
        $stmt_select->execute();
        $res_old = $stmt_select->get_result();

        $old_nama_str = "";
        $old_ket_str = "";

        if ($res_old->num_rows > 0) {
            $row_old = $res_old->fetch_assoc();
            $old_nama_str = $row_old['nama_cek'];
            $old_ket_str = $row_old['ket_cek'];
        }

        // B. Logic Koma (Index 0: Area, Index 1: Leader)
        $nama_parts = explode(',', $old_nama_str ?? '');
        $ket_parts = explode(',', $old_ket_str ?? '');

        // Pastikan array minimal 2 elemen
        if (count($nama_parts) < 2)
            $nama_parts = array_pad($nama_parts, 2, '');
        if (count($ket_parts) < 2)
            $ket_parts = array_pad($ket_parts, 2, '');

        // Update sesuai tipe
        if ($tipe_cek === 'area') {
            $nama_parts[0] = $nama_user_cek_input;
            $ket_parts[0] = $ket_input;
        } else if ($tipe_cek === 'leader') {
            $nama_parts[1] = $nama_user_cek_input;
            $ket_parts[1] = $ket_input;
        }

        // Gabung lagi jadi string
        $nama_final = $nama_parts[0] . ',' . $nama_parts[1];
        $ket_final = $ket_parts[0] . ',' . $ket_parts[1];

        // C. Eksekusi
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
            $ket_final,
            $nama_final,
            $tanggal_timestamp
        );

        if ($stmt->execute()) {
            $successCount++;
        }
    }

    $stmt_select->close();
    $stmt->close();
    $conn->close();

    if ($successCount > 0) {
        http_response_code(200);
        echo json_encode([
            'status' => 'success',
            'message' => "Berhasil update $successCount data (Otorisasi: $nama_user_cek_input sebagai $tipe_cek)"
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
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>