<?php
session_start();
include '../../../aa_kon_sett.php';
header('Content-Type: application/json');
try {
    $input = json_decode(file_get_contents('php://input'), true);
    $mode = $input['mode'] ?? 'insert';
    $id = $input['id'] ?? null;
    $kata_kunci = trim($input['kata_kunci'] ?? '');
    $status_aktif = $input['status_aktif'] ?? '1';
    $list_pesan = $input['isi_balasan'] ?? [];
    if (empty($kata_kunci)) {
        throw new Exception("Kata kunci wajib diisi.");
    }
    if (!is_array($list_pesan) || count($list_pesan) === 0) {
        throw new Exception("Minimal harus ada 1 pesan balasan.");
    }
    $conn->begin_transaction();
    if ($mode === 'insert') {
        $sql = "INSERT INTO wa_balasan_otomatis_kamus (kata_kunci, status_aktif) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $kata_kunci, $status_aktif);
        if (!$stmt->execute()) {
            if ($conn->errno == 1062) {
                throw new Exception("Kata kunci '$kata_kunci' sudah ada. Gunakan yang lain.");
            }
            throw new Exception("Gagal menyimpan keyword: " . $stmt->error);
        }
        $id = $conn->insert_id;
        $message_res = "Data berhasil ditambahkan.";
    } elseif ($mode === 'update') {
        if (empty($id))
            throw new Exception("ID data tidak valid.");
        $sql = "UPDATE wa_balasan_otomatis_kamus SET kata_kunci = ?, status_aktif = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $kata_kunci, $status_aktif, $id);
        if (!$stmt->execute()) {
            if ($conn->errno == 1062) {
                throw new Exception("Kata kunci '$kata_kunci' sudah digunakan.");
            }
            throw new Exception("Gagal update keyword: " . $stmt->error);
        }
        $sql_del = "DELETE FROM wa_balasan_otomatis_pesan WHERE kamus_id = ?";
        $stmt_del = $conn->prepare($sql_del);
        $stmt_del->bind_param("i", $id);
        $stmt_del->execute();
        $message_res = "Data berhasil diperbarui.";
    } else {
        throw new Exception("Mode tidak valid.");
    }
    $sql_pesan = "INSERT INTO wa_balasan_otomatis_pesan (kamus_id, jenis_pesan, isi_pesan, urutan) VALUES (?, ?, ?, ?)";
    $stmt_pesan = $conn->prepare($sql_pesan);
    foreach ($list_pesan as $index => $item) {
        $urutan = $index + 1;
        $jenis = $item['type'];
        $isi = $item['content'];
        if (is_array($isi) || is_object($isi)) {
            $isi = json_encode($isi);
        }
        if (is_string($isi)) {
            $isi = trim($isi);
        }
        if (empty($isi) && $isi !== '0') {
            continue;
        }
        $stmt_pesan->bind_param("isss", $id, $jenis, $isi, $urutan);
        if (!$stmt_pesan->execute()) {
            throw new Exception("Gagal menyimpan pesan urutan ke-$urutan");
        }
    }
    $conn->commit();
    echo json_encode(['success' => true, 'message' => $message_res]);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>