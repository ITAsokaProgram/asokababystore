<?php
session_start();
include '../../../aa_kon_sett.php';
header('Content-Type: application/json');

try {
    // Ambil Input JSON
    $input = json_decode(file_get_contents('php://input'), true);

    $mode = $input['mode'] ?? 'insert';
    $id = $input['id'] ?? null;
    $kata_kunci = trim($input['kata_kunci'] ?? '');
    $status_aktif = $input['status_aktif'] ?? '1';

    // list_pesan berupa Array
    $list_pesan = $input['isi_balasan'] ?? [];

    // --- Validasi ---
    if (empty($kata_kunci)) {
        throw new Exception("Kata kunci wajib diisi.");
    }

    if (!is_array($list_pesan) || count($list_pesan) === 0) {
        throw new Exception("Minimal harus ada 1 pesan balasan.");
    }

    // Filter pesan kosong (jika user input tapi isinya spasi doang)
    $list_pesan_bersih = [];
    foreach ($list_pesan as $p) {
        $p = trim($p);
        if (!empty($p))
            $list_pesan_bersih[] = $p;
    }

    if (count($list_pesan_bersih) === 0) {
        throw new Exception("Isi balasan tidak boleh kosong semua.");
    }

    // --- Mulai Transaksi Database ---
    $conn->begin_transaction();

    // 1. Handle Tabel Induk (Kamus)
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
        $id = $conn->insert_id; // Ambil ID baru

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

        // Hapus pesan lama dulu agar bersih (strategy: delete all -> insert new)
        $sql_del = "DELETE FROM wa_balasan_otomatis_pesan WHERE kamus_id = ?";
        $stmt_del = $conn->prepare($sql_del);
        $stmt_del->bind_param("i", $id);
        $stmt_del->execute();

        $message_res = "Data berhasil diperbarui.";

    } else {
        throw new Exception("Mode tidak valid.");
    }

    // 2. Handle Tabel Anak (Pesan) - Looping Insert
    $sql_pesan = "INSERT INTO wa_balasan_otomatis_pesan (kamus_id, isi_pesan, urutan) VALUES (?, ?, ?)";
    $stmt_pesan = $conn->prepare($sql_pesan);

    foreach ($list_pesan_bersih as $index => $pesan) {
        $urutan = $index + 1;
        $stmt_pesan->bind_param("isi", $id, $pesan, $urutan);
        if (!$stmt_pesan->execute()) {
            throw new Exception("Gagal menyimpan pesan urutan ke-$urutan");
        }
    }

    // --- Commit Transaksi ---
    $conn->commit();

    echo json_encode(['success' => true, 'message' => $message_res]);

} catch (Exception $e) {
    // Rollback jika ada error
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>