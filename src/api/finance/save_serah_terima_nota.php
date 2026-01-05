<?php
session_start();
ini_set('display_errors', 0);
mysqli_report(MYSQLI_REPORT_OFF);
header('Content-Type: application/json');

require_once __DIR__ . '/../../../aa_kon_sett.php';
require_once __DIR__ . '/../../auth/middleware_login.php';
require_once __DIR__ . '/../../helpers/surat_terima_nota_helper.php';

try {
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
    $no_faktur = preg_replace('/[^a-zA-Z0-9]/', '', $no_faktur_format); // Hapus karakter spesial

    // Update: Menggunakan kolom 'nominal' tunggal
    $nominal = (float) ($input['nominal'] ?? 0);

    $tgl_diserahkan = $input['tgl_diserahkan'] ?: null;
    $status = 'Belum Terima';
    $diberikan = trim($input['diberikan'] ?? '');
    $visibilitas = 'Aktif';

    if (empty($no_faktur))
        throw new Exception("Nomor Faktur wajib diisi.");

    // Cek Duplikat berdasarkan Faktur & Visibilitas Aktif
    $sqlCheck = "SELECT no_faktur FROM serah_terima_nota WHERE no_faktur = ? AND visibilitas = 'Aktif'";
    $typesCheck = "s";
    $paramsCheck = [$no_faktur];

    if (!empty($original_no_faktur)) {
        // Jika update, exclude faktur diri sendiri
        $sqlCheck .= " AND no_faktur != ?";
        $typesCheck .= "s";
        $paramsCheck[] = $original_no_faktur;
    }

    $stmtCheck = $conn->prepare($sqlCheck);
    $stmtCheck->bind_param($typesCheck, ...$paramsCheck);
    $stmtCheck->execute();

    if ($stmtCheck->get_result()->num_rows > 0) {
        throw new Exception("Nomor Faktur '$no_faktur_format' sudah ada.");
    }

    if (!empty($original_no_faktur)) {
        // --- UPDATE MODE ---
        $stmtOld = $conn->prepare("SELECT * FROM serah_terima_nota WHERE no_faktur = ?");
        $stmtOld->bind_param("s", $original_no_faktur);
        $stmtOld->execute();
        $old_data = $stmtOld->get_result()->fetch_assoc();

        $query = "UPDATE serah_terima_nota SET 
            tgl_nota=?, nama_supplier=?, kode_supplier=?, 
            no_faktur=?, no_faktur_format=?, 
            nominal=?, 
            tgl_diserahkan=?, status=?, 
            diberikan=?, edit_pada=NOW(), diedit_oleh=?
            WHERE no_faktur=?";

        $stmt = $conn->prepare($query);
        $stmt->bind_param(
            "sssssdsssss",
            $tgl_nota,
            $nama_supplier,
            $kode_supplier,
            $no_faktur,
            $no_faktur_format,
            $nominal,
            $tgl_diserahkan,
            $status,
            $diberikan,
            $user_login,
            $original_no_faktur
        );

        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }

        log_nota($conn, $user_login, 'UPDATE', $no_faktur, $old_data, $input);
        $message = "Data berhasil diperbarui.";

    } else {
        // --- INSERT MODE ---
        $query = "INSERT INTO serah_terima_nota 
            (tgl_nota, nama_supplier, kode_supplier, 
            no_faktur, no_faktur_format, 
            nominal, 
            tgl_diserahkan, status, diberikan, visibilitas, dibuat_oleh)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($query);
        $stmt->bind_param(
            "sssssdsssss",
            $tgl_nota,
            $nama_supplier,
            $kode_supplier,
            $no_faktur,
            $no_faktur_format,
            $nominal,
            $tgl_diserahkan,
            $status,
            $diberikan,
            $visibilitas,
            $user_login
        );

        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }

        log_nota($conn, $user_login, 'INSERT', $no_faktur, null, $input);
        $message = "Data berhasil disimpan.";
    }

    echo json_encode(['success' => true, 'message' => $message]);

} catch (Exception $e) {
    http_response_code(200);
    $msg = $e->getMessage();
    if (strpos($msg, 'Duplicate entry') !== false) {
        $msg = "Nomor Faktur sudah terdaftar di database (Mungkin di data Arsip/Hidden). Mohon gunakan nomor lain.";
    }
    echo json_encode(['success' => false, 'message' => $msg]);
} finally {
    if (isset($conn))
        $conn->close();
}
?>