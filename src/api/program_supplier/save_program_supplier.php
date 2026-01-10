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
    $kd_user = $verif->kode ?? 'system';
    $input = json_decode(file_get_contents('php://input'), true);
    $old_doc = $input['old_nomor_dokumen'] ?? null;
    $new_doc = $input['nomor_dokumen'] ?? '';
    if (empty($new_doc))
        throw new Exception("Nomor Dokumen wajib diisi");
    $npwp = $input['npwp'] ?? '';
    if (!empty($npwp) && strlen($npwp) !== 16) {
        throw new Exception("NPWP harus 16 karakter");
    }
    $status_ppn = $input['status_ppn'] ?? 'Non PPN';
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
    $top_date_input = $input['top_date'] ?? null;
    if (empty($top_date_input) || strlen($top_date_input) < 10) {
        $top_date = null;
    } else {
        $top_date = $top_date_input;
    }
    if ($old_doc) {
        $conn->begin_transaction();
        try {
            $stmtGetOld = $conn->prepare("SELECT kode_cabang, nomor_program FROM program_supplier WHERE nomor_dokumen = ?");
            $stmtGetOld->bind_param("s", $old_doc);
            $stmtGetOld->execute();
            $resOld = $stmtGetOld->get_result();
            $rowOld = $resOld->fetch_assoc();
            $stmtGetOld->close();
            $old_db_cabang = $rowOld['kode_cabang'];
            $final_nomor_program_update = $rowOld['nomor_program'];
            if ($old_db_cabang !== $kode_cabang) {
                $clean_user = preg_replace('/[^a-zA-Z0-9]/', '', $kd_user);
                $prefix = $kode_cabang . "-PS-" . $clean_user . "-";
                $sqlGetMax = "SELECT nomor_program FROM program_supplier 
                              WHERE nomor_program LIKE ? 
                              ORDER BY LENGTH(nomor_program) DESC, nomor_program DESC 
                              LIMIT 1 FOR UPDATE";
                $stmtMax = $conn->prepare($sqlGetMax);
                $likeParam = $prefix . "%";
                $stmtMax->bind_param("s", $likeParam);
                $stmtMax->execute();
                $resMax = $stmtMax->get_result();
                $next_seq = 1;
                if ($rowMax = $resMax->fetch_assoc()) {
                    $last_code = $rowMax['nomor_program'];
                    $last_seq = (int) str_replace($prefix, "", $last_code);
                    $next_seq = $last_seq + 1;
                }
                $stmtMax->close();
                $final_nomor_program_update = $prefix . $next_seq;
            }
            $sql = "UPDATE program_supplier SET 
                    nomor_dokumen=?, pic=?, nama_supplier=?, npwp=?, status_ppn=?, kode_cabang=?, nama_cabang=?, 
                    periode_program=?, nama_program=?, nilai_program=?, mop=?, top_date=?, 
                    kd_user=?, nomor_program=? 
                    WHERE nomor_dokumen=?";
            $types = "sssssssssdsssss";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param(
                $types,
                $new_doc,
                $pic,
                $nama_supplier,
                $npwp,
                $status_ppn,
                $kode_cabang,
                $nama_cabang,
                $periode,
                $nama_prog,
                $nilai_prog,
                $mop,
                $top_date,
                $kd_user,
                $final_nomor_program_update,
                $old_doc
            );
            if (!$stmt->execute()) {
                throw new Exception("Gagal update: " . $stmt->error);
            }
            $conn->commit();
            $msg = "Data diperbarui. No Program: " . $final_nomor_program_update;
        } catch (Exception $ex) {
            $conn->rollback();
            throw $ex;
        }
    } else {
        $conn->begin_transaction();
        try {
            $clean_user = preg_replace('/[^a-zA-Z0-9]/', '', $kd_user);
            $prefix = $kode_cabang . "-PS-" . $clean_user . "-";
            $sqlGetMax = "SELECT nomor_program FROM program_supplier 
                          WHERE nomor_program LIKE ? 
                          ORDER BY LENGTH(nomor_program) DESC, nomor_program DESC 
                          LIMIT 1 FOR UPDATE";
            $stmtMax = $conn->prepare($sqlGetMax);
            $likeParam = $prefix . "%";
            $stmtMax->bind_param("s", $likeParam);
            $stmtMax->execute();
            $resMax = $stmtMax->get_result();
            $next_seq = 1;
            if ($rowMax = $resMax->fetch_assoc()) {
                $last_code = $rowMax['nomor_program'];
                $last_seq = (int) str_replace($prefix, "", $last_code);
                $next_seq = $last_seq + 1;
            }
            $stmtMax->close();
            $final_nomor_program = $prefix . $next_seq;
            $sql = "INSERT INTO program_supplier 
                    (nomor_dokumen, pic, nama_supplier, npwp, status_ppn, kode_cabang, nama_cabang, 
                     periode_program, nama_program, nomor_program, nilai_program, mop, top_date, kd_user)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $types = "ssssssssssdsss";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param(
                $types,
                $new_doc,
                $pic,
                $nama_supplier,
                $npwp,
                $status_ppn,
                $kode_cabang,
                $nama_cabang,
                $periode,
                $nama_prog,
                $final_nomor_program,
                $nilai_prog,
                $mop,
                $top_date,
                $kd_user
            );
            if (!$stmt->execute()) {
                throw new Exception("Gagal insert: " . $stmt->error);
            }
            $conn->commit();
            $msg = "Data berhasil disimpan. No Program: " . $final_nomor_program;
        } catch (Exception $ex) {
            $conn->rollback();
            throw $ex;
        }
    }
    echo json_encode(['success' => true, 'message' => $msg]);
} catch (Exception $e) {
    http_response_code(200);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} finally {
    if (isset($conn))
        $conn->close();
}
?>