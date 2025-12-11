<?php
session_start();
include '../../../aa_kon_sett.php';
header('Content-Type: application/json');

try {
    // Ambil Input JSON
    $input = json_decode(file_get_contents('php://input'), true);
    
    $mode = $input['mode'] ?? 'insert';
    $kata_kunci = trim($input['kata_kunci'] ?? '');
    $isi_balasan = trim($input['isi_balasan'] ?? '');
    $status_aktif = $input['status_aktif'] ?? '1';
    
    // Validasi Dasar
    if (empty($kata_kunci) || empty($isi_balasan)) {
        throw new Exception("Kata kunci dan isi balasan wajib diisi.");
    }

    // 1. Mode INSERT
    if ($mode === 'insert') {
        $sql = "INSERT INTO wa_balasan_otomatis (kata_kunci, isi_balasan, status_aktif) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $kata_kunci, $isi_balasan, $status_aktif);
        
        if (!$stmt->execute()) {
            // Handle Duplicate Entry (Error Code 1062)
            if ($conn->errno == 1062) {
                throw new Exception("Kata kunci '$kata_kunci' sudah ada. Gunakan kata kunci lain.");
            }
            throw new Exception("Gagal menyimpan: " . $stmt->error);
        }
        $message = "Data berhasil ditambahkan.";
    
    // 2. Mode UPDATE
    } elseif ($mode === 'update') {
        $old_kata_kunci = $input['old_kata_kunci'] ?? '';
        
        if (empty($old_kata_kunci)) {
            throw new Exception("ID Kata kunci lama tidak ditemukan.");
        }

        // Update: Kita ubah juga kata_kunci nya jika user mengeditnya
        $sql = "UPDATE wa_balasan_otomatis SET kata_kunci = ?, isi_balasan = ?, status_aktif = ? WHERE kata_kunci = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $kata_kunci, $isi_balasan, $status_aktif, $old_kata_kunci);
        
        if (!$stmt->execute()) {
            if ($conn->errno == 1062) {
                throw new Exception("Gagal update. Kata kunci '$kata_kunci' sudah digunakan oleh data lain.");
            }
            throw new Exception("Gagal update: " . $stmt->error);
        }
        $message = "Data berhasil diperbarui.";

    } else {
        throw new Exception("Mode tidak valid.");
    }

    echo json_encode(['success' => true, 'message' => $message]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>