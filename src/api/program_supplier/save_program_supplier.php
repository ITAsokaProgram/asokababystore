<?php
session_start();
ini_set('display_errors', 0);
header('Content-Type: application/json');
require_once __DIR__ . '/../../../aa_kon_sett.php';
require_once __DIR__ . '/../../auth/middleware_login.php';
try {
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    if (!preg_match('/^Bearer\s(\S+)$/', $authHeader, $matches)) {
        throw new Exception("Token tidak ditemukan");
    }
    $verif = verify_token($matches[1]);
    if (!$verif)
        throw new Exception("Token tidak valid");
    $kd_user = $verif->id ?? 'system';
    $input = json_decode(file_get_contents('php://input'), true);
    $old_doc = $input['old_nomor_dokumen'] ?? null;
    $new_doc = $input['nomor_dokumen'] ?? '';
    if (empty($new_doc))
        throw new Exception("Nomor Dokumen wajib diisi");
    if (!$old_doc || ($old_doc && $old_doc !== $new_doc)) {
        $stmtCheck = $conn->prepare("SELECT nomor_dokumen FROM program_supplier WHERE nomor_dokumen = ?");
        $stmtCheck->bind_param("s", $new_doc);
        $stmtCheck->execute();
        if ($stmtCheck->get_result()->num_rows > 0) {
            throw new Exception("Nomor Dokumen '$new_doc' sudah ada di database.");
        }
        $stmtCheck->close();
    }
    $kode_cabang = $input['kode_cabang'] ?? '';
    $nama_cabang = '';
    if ($kode_cabang) {
        $resCab = $conn->query("SELECT Nm_Alias FROM kode_store WHERE Kd_Store = '$kode_cabang' LIMIT 1");
        if ($resCab && $r = $resCab->fetch_assoc())
            $nama_cabang = $r['Nm_Alias'];
    }
    $nama_supplier = $input['nama_supplier'];
    $pic = $input['pic'];
    $periode = $input['periode_program'];
    $nama_prog = $input['nama_program'];
    $nilai_prog = (float) $input['nilai_program'];
    $mop = $input['mop'];
    $top_date = !empty($input['top_date']) ? $input['top_date'] : null;
    $nilai_tf = (float) $input['nilai_transfer'];
    $tgl_tf = !empty($input['tanggal_transfer']) ? $input['tanggal_transfer'] : null;
    $tgl_fpk = !empty($input['tgl_fpk']) ? $input['tgl_fpk'] : null;
    $nsfp = $input['nsfp'];
    $dpp = (float) $input['dpp'];
    $ppn = (float) $input['ppn'];
    $pph = (float) $input['pph'];
    $bukpot = $input['nomor_bukpot'];
    if ($old_doc) {
        $sql = "UPDATE program_supplier SET 
                nomor_dokumen=?, pic=?, nama_supplier=?, kode_cabang=?, nama_cabang=?, 
                periode_program=?, nama_program=?, nilai_program=?, mop=?, top_date=?, 
                nilai_transfer=?, tanggal_transfer=?, tgl_fpk=?, nsfp=?, dpp=?, ppn=?, pph=?, 
                nomor_bukpot=?, kd_user=? 
                WHERE nomor_dokumen=?";
        $types = "sssssssdssdsssdddsss";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            $types,
            $new_doc,
            $pic,
            $nama_supplier,
            $kode_cabang,
            $nama_cabang,
            $periode,
            $nama_prog,
            $nilai_prog,
            $mop,
            $top_date,
            $nilai_tf,
            $tgl_tf,
            $tgl_fpk,
            $nsfp,
            $dpp,
            $ppn,
            $pph,
            $bukpot,
            $kd_user,
            $old_doc
        );
        $msg = "Data berhasil diperbarui.";
    } else {
        $sql = "INSERT INTO program_supplier 
                (nomor_dokumen, pic, nama_supplier, kode_cabang, nama_cabang, periode_program, nama_program, 
                 nilai_program, mop, top_date, nilai_transfer, tanggal_transfer, tgl_fpk, nsfp, dpp, ppn, pph, nomor_bukpot, kd_user)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $types = "sssssssdssdsssdddss";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            $types,
            $new_doc,
            $pic,
            $nama_supplier,
            $kode_cabang,
            $nama_cabang,
            $periode,
            $nama_prog,
            $nilai_prog,
            $mop,
            $top_date,
            $nilai_tf,
            $tgl_tf,
            $tgl_fpk,
            $nsfp,
            $dpp,
            $ppn,
            $pph,
            $bukpot,
            $kd_user
        );
        $msg = "Data berhasil disimpan.";
    }
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => $msg]);
    } else {
        throw new Exception("Database Error: " . $stmt->error);
    }
} catch (Exception $e) {
    http_response_code(200);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} finally {
    if (isset($conn))
        $conn->close();
}
?>