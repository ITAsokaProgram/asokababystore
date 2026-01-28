<?php
session_start();
ini_set('display_errors', 0);
header('Content-Type: application/json');
require_once __DIR__ . '/../../../aa_kon_sett.php';
require_once __DIR__ . '/../../auth/middleware_login.php';
require_once __DIR__ . '/../../helpers/finance_log_helper.php'; // Load Helper
try {
    $verif = authenticate_request();

    $kd_user = $verif->id ?? $verif->kode ?? 0;
    $json = file_get_contents('php://input');
    $input = json_decode($json, true);
    if (!$input)
        throw new Exception("Data tidak valid");
    $id = isset($input['id']) ? (int) $input['id'] : null;
    $no_lpb = $input['no_lpb'] ?? '';
    $kode_supplier = $input['kode_supplier'] ?? '';
    $kode_store = $input['kode_store'] ?? '';
    $status = $input['status'] ?? '';
    $nama_supplier = $input['nama_supplier'] ?? '';
    $catatan = $input['catatan'] ?? null;
    $no_faktur_pajak = $input['no_faktur'] ?? '';
    $tgl_nota = $input['tgl_nota'] ?? date('Y-m-d');
    $d = DateTime::createFromFormat('Y-m-d', $tgl_nota);
    if (!$d || $d->format('Y-m-d') !== $tgl_nota) {
        throw new Exception("Tanggal nota tidak boleh kosong.");
    }
    $no_invoice = $input['no_invoice'] ?? '';
    $dpp = (float) ($input['dpp'] ?? 0);
    $dpp_nilai_lain = (float) ($input['dpp_nilai_lain'] ?? 0);
    $ppn = (float) ($input['ppn'] ?? 0);
    $total_terima_fp = (float) ($input['total_terima_fp'] ?? 0);
    if (empty($no_invoice) || empty($nama_supplier)) {
        throw new Exception("No Invoice dan Nama Supplier wajib diisi.");
    }
    $conn->begin_transaction();
    if ($id) {
        $qOld = $conn->prepare("SELECT * FROM ff_pembelian WHERE id = ?");
        $qOld->bind_param("i", $id);
        $qOld->execute();
        $oldData = $qOld->get_result()->fetch_assoc();
        $qOld->close();
        $query = "UPDATE ff_pembelian SET 
                    nama_supplier=?, kode_supplier=?, kode_store=?, tgl_nota=?, 
                    no_invoice=?, no_faktur=?, catatan=?, dpp=?, dpp_nilai_lain=?, 
                    ppn=?, total_terima_fp=?, kd_user=?, status=?, edit_pada=NOW() 
                  WHERE id=?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param(
            "sssssssddddisi",
            $nama_supplier,
            $kode_supplier,
            $kode_store,
            $tgl_nota,
            $no_invoice,
            $no_faktur_pajak,
            $catatan,
            $dpp,
            $dpp_nilai_lain,
            $ppn,
            $total_terima_fp,
            $kd_user,
            $status,
            $id
        );
        if (!$stmt->execute())
            throw new Exception("Gagal update ff_pembelian: " . $stmt->error);
        $newData = array_merge($oldData, $input);
        write_finance_log($conn, $kd_user, 'ff_pembelian', $no_invoice, 'UPDATE', $oldData, $newData);
        $message = "Data pembelian berhasil diperbarui (Buku Besar tidak berubah).";
    } else {
        if (!$id) { // Hanya untuk data baru
            $checkQuery = "SELECT id FROM ff_pembelian WHERE no_invoice = ? AND kode_store = ?";
            $stmtCheck = $conn->prepare($checkQuery);
            $stmtCheck->bind_param("ss", $no_invoice, $kode_store);
            $stmtCheck->execute();
            if ($stmtCheck->get_result()->num_rows > 0) {
                throw new Exception("Nomor invoice $no_invoice sudah pernah diinput untuk cabang ini.");
            }
        }
        $queryIns = "INSERT INTO ff_pembelian 
                    (nama_supplier, kode_supplier, kode_store, tgl_nota, no_invoice, no_faktur, catatan, dpp, dpp_nilai_lain, ppn, total_terima_fp, status, kd_user) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmtIns = $conn->prepare($queryIns);
        $stmtIns->bind_param(
            "sssssssddddsi",
            $nama_supplier,
            $kode_supplier,
            $kode_store,
            $tgl_nota,
            $no_invoice,
            $no_faktur_pajak,
            $catatan,
            $dpp,
            $dpp_nilai_lain,
            $ppn,
            $total_terima_fp,
            $status,
            $kd_user
        );
        if (!$stmtIns->execute())
            throw new Exception("Gagal simpan ff_pembelian: " . $stmtIns->error);
        write_finance_log($conn, $kd_user, 'ff_pembelian', $no_invoice, 'INSERT', null, $input);
        $queryBB = "INSERT INTO buku_besar 
                    (tgl_nota, no_faktur, kode_supplier, nama_supplier, status, nilai_faktur, total_bayar, kode_store, kd_user) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmtBB = $conn->prepare($queryBB);
        $total_bayar_awal = 0;
        $stmtBB->bind_param(
            "sssssddss",
            $tgl_nota,
            $no_invoice,
            $kode_supplier,
            $nama_supplier,
            $status,
            $total_terima_fp,
            $total_bayar_awal,
            $kode_store,
            $kd_user
        );
        if (!$stmtBB->execute())
            throw new Exception("Gagal simpan ke Buku Besar: " . $stmtBB->error);

        // $stmtSTN = $conn->prepare("UPDATE serah_terima_nota SET status_bayar = 'Sudah' WHERE no_faktur = ?");
        // $stmtSTN->bind_param("s", $no_invoice);
        // $stmtSTN->execute();
        // $stmtSTN->close();
        $message = "Data berhasil disimpan ke Pembelian.";
    }
    $conn->commit();
    echo json_encode(['success' => true, 'message' => $message]);
} catch (Exception $e) {
    if (isset($conn))
        $conn->rollback();
    http_response_code(200);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} finally {
    if (isset($conn))
        $conn->close();
}
?>