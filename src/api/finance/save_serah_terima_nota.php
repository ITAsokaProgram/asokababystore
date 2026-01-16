<?php
session_start();
ini_set('display_errors', 0);
mysqli_report(MYSQLI_REPORT_OFF);
header('Content-Type: application/json');
require_once __DIR__ . '/../../../aa_kon_sett.php';
require_once __DIR__ . '/../../auth/middleware_login.php';
require_once __DIR__ . '/../../helpers/finance_log_helper.php';
try {
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    if (!preg_match('/^Bearer\s(\S+)$/', $authHeader, $matches)) {
        throw new Exception('Token tidak ditemukan');
    }
    $verif = verify_token($matches[1]);
    if (!$verif) {
        throw new Exception('Token tidak valid');
    }
    $user_login = $verif->id ?? $verif->kode ?? null;
    $json = file_get_contents('php://input');
    $input = json_decode($json, true);
    if (!$input) {
        throw new Exception("Data tidak valid");
    }
    $original_no_faktur = isset($input['original_no_faktur']) ? trim($input['original_no_faktur']) : '';
    $tgl_nota = $input['tgl_nota'] ?: null;
    $nama_supplier = trim($input['nama_supplier'] ?? '');
    $kode_supplier = trim($input['kode_supplier'] ?? '');
    $no_faktur_format = trim($input['no_faktur_format'] ?? '');
    $no_faktur = preg_replace('/[^a-zA-Z0-9]/', '', $no_faktur_format);
    $nominal = (float) ($input['nominal'] ?? 0);
    $tgl_diserahkan = $input['tgl_diserahkan'] ?: null;
    $status = 'Belum Terima';
    $diberikan = trim($input['diberikan'] ?? '');
    $visibilitas = 'Aktif';
    $cod = isset($input['cod']) && $input['cod'] === 'Ya' ? 'Ya' : 'Tidak';
    if ($cod === 'Ya') {
        $nota_tanggal_masuk = $input['nota_tanggal_masuk'] ?: null;
        $cabang_penerima = trim($input['cabang_penerima'] ?? '');
        $lengkap = isset($input['lengkap']) ? $input['lengkap'] : null;

        $no_rek = trim($input['no_rek'] ?? '');
        $nama_bank = trim($input['nama_bank'] ?? '');
        $atas_nama_rek = trim($input['atas_nama_rek'] ?? '');

        if (!$lengkap) {
            throw new Exception("Status Kelengkapan wajib dipilih untuk COD.");
        }

        if (!$cabang_penerima) {
            throw new Exception("Cabang Penerima wajib diisi untuk COD.");
        }

        if ($lengkap === 'Ya' && !$nota_tanggal_masuk) {
            throw new Exception("Tanggal Nota Masuk wajib diisi jika status Lengkap.");
        }
    } else {
        $nota_tanggal_masuk = null;
        $cabang_penerima = null;
        $lengkap = null;
        $no_rek = null;
        $nama_bank = null;
        $atas_nama_rek = null;
    }
    if (empty($no_faktur)) {
        throw new Exception("Nomor Faktur wajib diisi.");
    }
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
        throw new Exception("Nomor Faktur '$no_faktur_format' sudah ada dalam sistem.");
    }
    if (!empty($original_no_faktur)) {
        $stmtOld = $conn->prepare("SELECT * FROM serah_terima_nota WHERE no_faktur = ?");
        $stmtOld->bind_param("s", $original_no_faktur);
        $stmtOld->execute();
        $old_data = $stmtOld->get_result()->fetch_assoc();
        $query = "UPDATE serah_terima_nota SET 
            tgl_nota=?, nama_supplier=?, kode_supplier=?, 
            no_faktur=?, no_faktur_format=?, 
            nominal=?, 
            tgl_diserahkan=?, status=?, diberikan=?, 
            -- Field COD Baru
            cod=?, nota_tanggal_masuk=?, cabang_penerima=?, lengkap=?, 
            no_rek=?, nama_bank=?, atas_nama_rek=?, 
            edit_pada=NOW(), diedit_oleh=?
            WHERE no_faktur=?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param(
            "sssssdssssssssssss",
            $tgl_nota,
            $nama_supplier,
            $kode_supplier,
            $no_faktur,
            $no_faktur_format,
            $nominal,
            $tgl_diserahkan,
            $status,
            $diberikan,
            $cod,
            $nota_tanggal_masuk,
            $cabang_penerima,
            $lengkap,
            $no_rek,
            $nama_bank,
            $atas_nama_rek,
            $user_login,
            $original_no_faktur
        );
        if (!$stmt->execute()) {
            throw new Exception("Database Error: " . $stmt->error);
        }
        $newData = array_merge($old_data, $input);
        write_finance_log($conn, $user_login, 'serah_terima_nota', $no_faktur, 'UPDATE', $old_data, $newData);
        $message = "Data berhasil diperbarui.";
    } else {
        $query = "INSERT INTO serah_terima_nota 
            (
                tgl_nota, nama_supplier, kode_supplier, 
                no_faktur, no_faktur_format, 
                nominal, 
                tgl_diserahkan, status, diberikan, visibilitas, dibuat_oleh,
                -- Field COD Baru
                cod, nota_tanggal_masuk, cabang_penerima, lengkap, 
                no_rek, nama_bank, atas_nama_rek
            )
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param(
            "sssssdssssssssssss",
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
            $user_login,
            $cod,
            $nota_tanggal_masuk,
            $cabang_penerima,
            $lengkap,
            $no_rek,
            $nama_bank,
            $atas_nama_rek
        );
        if (!$stmt->execute()) {
            throw new Exception("Database Error: " . $stmt->error);
        }
        write_finance_log($conn, $user_login, 'serah_terima_nota', $no_faktur, 'INSERT', null, $input);
        $message = "Data berhasil disimpan.";
    }
    echo json_encode(['success' => true, 'message' => $message]);
} catch (Exception $e) {
    http_response_code(200);
    $msg = $e->getMessage();
    if (strpos($msg, 'Duplicate entry') !== false) {
        $msg = "Nomor Faktur sudah terdaftar di database. Mohon cek kembali.";
    }
    echo json_encode(['success' => false, 'message' => $msg]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>