<?php
session_start();
ini_set('display_errors', 0);
header('Content-Type: application/json');
require_once __DIR__ . '/../../../aa_kon_sett.php';
require_once __DIR__ . '/../../auth/middleware_login.php';

try {
    // ... (Auth Logic sama) ...
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    if (!preg_match('/^Bearer\s(\S+)$/', $authHeader, $matches)) {
        throw new Exception('Token tidak ditemukan');
    }
    $verif = verify_token($matches[1]);
    if (!$verif)
        throw new Exception('Token tidak valid');
    $user_login = $verif->id ?? $verif->kode ?? null;

    $json = file_get_contents('php://input');
    $input = json_decode($json, true);
    if (!$input)
        throw new Exception("Data tidak valid");

    $original_no_faktur = isset($input['original_no_faktur']) ? trim($input['original_no_faktur']) : '';

    $tgl_nota = $input['tgl_nota'] ?: null;
    $nama_supplier = trim($input['nama_supplier'] ?? '');
    $kode_supplier = trim($input['kode_supplier'] ?? '');

    $no_faktur_format = trim($input['no_faktur_format'] ?? '');
    $no_faktur = preg_replace('/[\.\,\/\-\s\\\'_]/', '', $no_faktur_format);

    $nominal_awal = (float) ($input['nominal_awal'] ?? 0);
    $nominal_revisi = (float) ($input['nominal_revisi'] ?? 0);
    $selisih_pembayaran = (float) ($input['selisih_pembayaran'] ?? 0);
    $tgl_diserahkan = $input['tgl_diserahkan'] ?: null;

    // HAPUS $tgl_diterima dan $penerima

    $status = $input['status'] ?? 'Belum Terima';
    $diberikan = trim($input['diberikan'] ?? '');
    $visibilitas = 'Aktif';

    if (empty($no_faktur))
        throw new Exception("Nomor Faktur wajib diisi.");

    // CEK DUPLIKASI
    $sqlCheck = "SELECT no_faktur FROM serah_terima_nota WHERE no_faktur = ? AND visibilitas = 'Aktif'";
    $typesCheck = "s";
    $paramsCheck = [$no_faktur];

    if (!empty($original_no_faktur)) {
        $sqlCheck .= " AND no_faktur != ?";
        $typesCheck .= "s";
        $paramsCheck[] = $original_no_faktur;
    }

    $stmtCheck = $conn->prepare($sqlCheck);
    $stmtCheck->bind_param($typesCheck, ...$paramsCheck);
    $stmtCheck->execute();
    if ($stmtCheck->get_result()->num_rows > 0) {
        throw new Exception("Nomor Faktur '$no_faktur_format' sudah terdaftar.");
    }

    if (!empty($original_no_faktur)) {
        // --- UPDATE DATA ---
        // Hapus penerima dan tgl_diterima dari Query UPDATE
        $query = "UPDATE serah_terima_nota SET 
            tgl_nota=?, nama_supplier=?, kode_supplier=?, 
            no_faktur=?, no_faktur_format=?, 
            nominal_awal=?, nominal_revisi=?, selisih_pembayaran=?, 
            tgl_diserahkan=?, status=?, 
            diberikan=?, edit_pada=NOW(), diedit_oleh=?
            WHERE no_faktur=?";

        $stmt = $conn->prepare($query);
        // Types: sssssdddsssss (13 parameter)
        $stmt->bind_param(
            "sssssdddsssss",
            $tgl_nota,
            $nama_supplier,
            $kode_supplier,
            $no_faktur,
            $no_faktur_format,
            $nominal_awal,
            $nominal_revisi,
            $selisih_pembayaran,
            $tgl_diserahkan,
            $status,
            $diberikan,
            $user_login,
            $original_no_faktur
        );

        if (!$stmt->execute())
            throw new Exception("Gagal update: " . $stmt->error);
        $message = "Data berhasil diperbarui.";

    } else {
        // --- INSERT DATA ---
        // Hapus penerima dan tgl_diterima dari Query INSERT
        $query = "INSERT INTO serah_terima_nota 
            (tgl_nota, nama_supplier, kode_supplier, 
            no_faktur, no_faktur_format, 
            nominal_awal, nominal_revisi, selisih_pembayaran, 
            tgl_diserahkan, status, diberikan, visibilitas, dibuat_oleh)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($query);
        // Types: sssssdddsssss (13 parameter)
        $stmt->bind_param(
            "sssssdddsssss",
            $tgl_nota,
            $nama_supplier,
            $kode_supplier,
            $no_faktur,
            $no_faktur_format,
            $nominal_awal,
            $nominal_revisi,
            $selisih_pembayaran,
            $tgl_diserahkan,
            $status,
            $diberikan,
            $visibilitas,
            $user_login
        );

        if (!$stmt->execute())
            throw new Exception("Gagal simpan: " . $stmt->error);
        $message = "Data berhasil disimpan.";
    }

    echo json_encode(['success' => true, 'message' => $message]);

} catch (Exception $e) {
    http_response_code(200);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} finally {
    if (isset($conn))
        $conn->close();
}
?>