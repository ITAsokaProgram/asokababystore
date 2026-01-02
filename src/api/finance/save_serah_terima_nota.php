<?php
session_start();
ini_set('display_errors', 0);
header('Content-Type: application/json');
require_once __DIR__ . '/../../../aa_kon_sett.php';
require_once __DIR__ . '/../../auth/middleware_login.php';

try {
    // Auth Check
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    if (!preg_match('/^Bearer\s(\S+)$/', $authHeader, $matches)) {
        throw new Exception('Token tidak ditemukan');
    }
    $verif = verify_token($matches[1]);
    if (!$verif)
        throw new Exception('Token tidak valid');

    // --- AMBIL USER DARI TOKEN (Sesuai referensi src/api/uang_brangkas/insert.php) ---
    $user_login = $verif->id ?? $verif->kode ?? null;

    // Get Input
    $json = file_get_contents('php://input');
    $input = json_decode($json, true);
    if (!$input)
        throw new Exception("Data tidak valid");

    $id = isset($input['id']) ? (int) $input['id'] : null;
    $no_nota = trim($input['no_nota'] ?? '');
    $tgl_nota = $input['tgl_nota'] ?: null;
    $nama_supplier = trim($input['nama_supplier'] ?? '');
    $kode_supplier = trim($input['kode_supplier'] ?? '');
    $no_rev_nota = trim($input['no_rev_nota'] ?? '');

    // Logic Clean Faktur
    $no_faktur_format = trim($input['no_faktur_format'] ?? '');
    $no_faktur = preg_replace('/[\.\,\/\-\s\\\'_]/', '', $no_faktur_format);

    $nominal_awal = (float) ($input['nominal_awal'] ?? 0);
    $nominal_revisi = (float) ($input['nominal_revisi'] ?? 0);
    $selisih_pembayaran = (float) ($input['selisih_pembayaran'] ?? 0);

    $tgl_diserahkan = $input['tgl_diserahkan'] ?: null;
    $tgl_diterima = $input['tgl_diterima'] ?: null;
    $status = $input['status'] ?? 'Belum Terima';
    $diberikan = trim($input['diberikan'] ?? '');
    $penerima = trim($input['penerima'] ?? '');

    if (empty($no_nota))
        throw new Exception("Nomor Nota wajib diisi.");

    // Cek Duplikasi No Nota
    $sqlCheck = "SELECT id FROM serah_terima_nota WHERE no_nota = ? AND id != ?";
    $stmtCheck = $conn->prepare($sqlCheck);
    $checkId = $id ?: 0;
    $stmtCheck->bind_param("si", $no_nota, $checkId);
    $stmtCheck->execute();
    if ($stmtCheck->get_result()->num_rows > 0) {
        throw new Exception("Nomor Nota '$no_nota' sudah terdaftar.");
    }

    if ($id) {
        // --- UPDATE DATA (Isi diedit_oleh) ---
        $query = "UPDATE serah_terima_nota SET 
            no_nota=?, tgl_nota=?, nama_supplier=?, kode_supplier=?, 
            no_rev_nota=?, no_faktur=?, no_faktur_format=?, 
            nominal_awal=?, nominal_revisi=?, selisih_pembayaran=?, 
            tgl_diserahkan=?, tgl_diterima=?, status=?, 
            diberikan=?, penerima=?, edit_pada=NOW(), diedit_oleh=?
            WHERE id=?";

        $stmt = $conn->prepare($query);
        // Tambahan type 's' untuk diedit_oleh dan 'i' untuk id di akhir
        $stmt->bind_param(
            "sssssssdddssssssi",
            $no_nota,
            $tgl_nota,
            $nama_supplier,
            $kode_supplier,
            $no_rev_nota,
            $no_faktur,
            $no_faktur_format,
            $nominal_awal,
            $nominal_revisi,
            $selisih_pembayaran,
            $tgl_diserahkan,
            $tgl_diterima,
            $status,
            $diberikan,
            $penerima,
            $user_login, // diedit_oleh
            $id
        );

        if (!$stmt->execute())
            throw new Exception("Gagal update data: " . $stmt->error);
        $message = "Data berhasil diperbarui.";

    } else {
        // --- INSERT DATA (Isi dibuat_oleh) ---
        $query = "INSERT INTO serah_terima_nota 
            (no_nota, tgl_nota, nama_supplier, kode_supplier, 
            no_rev_nota, no_faktur, no_faktur_format, 
            nominal_awal, nominal_revisi, selisih_pembayaran, 
            tgl_diserahkan, tgl_diterima, status, diberikan, penerima, dibuat_oleh)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($query);
        // Tambahan type 's' di akhir untuk dibuat_oleh
        $stmt->bind_param(
            "sssssssdddssssss",
            $no_nota,
            $tgl_nota,
            $nama_supplier,
            $kode_supplier,
            $no_rev_nota,
            $no_faktur,
            $no_faktur_format,
            $nominal_awal,
            $nominal_revisi,
            $selisih_pembayaran,
            $tgl_diserahkan,
            $tgl_diterima,
            $status,
            $diberikan,
            $penerima,
            $user_login // dibuat_oleh
        );

        if (!$stmt->execute())
            throw new Exception("Gagal simpan data: " . $stmt->error);
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