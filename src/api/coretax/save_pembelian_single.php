<?php
session_start();
ini_set('display_errors', 0);
header('Content-Type: application/json');
require_once __DIR__ . '/../../../aa_kon_sett.php';
require_once __DIR__ . '/../../auth/middleware_login.php';
try {
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    if (!preg_match('/^Bearer\s(\S+)$/', $authHeader, $matches)) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Token tidak ditemukan']);
        exit;
    }
    $token = $matches[1];
    $verif = verify_token($token);
    if (!$verif) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Token tidak valid']);
        exit;
    }
    $kd_user = $verif->id ?? $verif->kode ?? 0;
    $json = file_get_contents('php://input');
    $input = json_decode($json, true);
    if (!$input) {
        throw new Exception("Data tidak valid");
    }
    $id = isset($input['id']) ? (int) $input['id'] : null;
    $no_lpb = $input['no_lpb'] ?? '';
    $kode_supplier = $input['kode_supplier'] ?? '';
    $kode_store = $input['kode_store'] ?? '';
    $status = $input['status'] ?? '';
    $nama_supplier = $input['nama_supplier'] ?? '';
    $catatan = $input['catatan'] ?? null; // TAMBAHAN AMBIL DATA
    $no_faktur = $input['no_faktur'] ?? '';
    $tgl_nota = $input['tgl_nota'] ?? date('Y-m-d');
    $d = DateTime::createFromFormat('Y-m-d', $tgl_nota);
    if (!$d || $d->format('Y-m-d') !== $tgl_nota) {
        throw new Exception("Format tanggal atau nilai tanggal nota salah, cek tanggal yang diinput");
    }
    $year = (int) $d->format('Y');
    if ($year < 2000 || $year > 2099) {
        throw new Exception("Tahun tanggal nota tidak valid ($year). Mohon periksa input tanggal.");
    }
    $no_invoice = $input['no_invoice'] ?? '';
    $dpp = (float) ($input['dpp'] ?? 0);
    $dpp_nilai_lain = (float) ($input['dpp_nilai_lain'] ?? 0);
    $ppn = (float) ($input['ppn'] ?? 0);
    $total_terima_fp = (float) ($input['total_terima_fp'] ?? 0);
    if (empty($status)) {
        throw new Exception("Status wajib dipilih (PKP/NON PKP/BTKP).");
    }
    if (empty($no_invoice) || empty($nama_supplier)) {
        throw new Exception("No Invoice dan Nama Supplier wajib diisi.");
    }
    if ($id) {
        $query = "UPDATE ff_pembelian SET 
                    nama_supplier=?, 
                    kode_supplier=?,  
                    kode_store=?, 
                    tgl_nota=?, 
                    no_invoice=?, 
                    no_faktur=?, 
                    catatan=?, /* TAMBAHAN */
                    dpp=?, 
                    dpp_nilai_lain=?, 
                    ppn=?, 
                    total_terima_fp=?, 
                    kd_user=?, 
                    status=?,  
                    edit_pada=NOW() 
                  WHERE id=?";
        $stmt = $conn->prepare($query);
        if (!$stmt)
            throw new Exception("Prepare Update Error: " . $conn->error);

        // Perhatikan urutan tipe data bind_param (s = string, d = double, i = integer)
        // Ditambah satu 's' untuk catatan setelah no_faktur
        $stmt->bind_param(
            "sssssssddddisi", // Tambah 's'
            $nama_supplier,
            $kode_supplier,
            $kode_store,
            $tgl_nota,
            $no_invoice,
            $no_faktur,
            $catatan, // TAMBAHAN VARIABLE
            $dpp,
            $dpp_nilai_lain,
            $ppn,
            $total_terima_fp,
            $kd_user,
            $status,
            $id
        );
        if (!$stmt->execute()) {
            throw new Exception("Gagal mengupdate data: " . $stmt->error);
        }
        $message = "Data berhasil diperbarui.";
    } else {
        $query = "INSERT INTO ff_pembelian 
                  (nama_supplier, kode_supplier, kode_store, tgl_nota, no_invoice, no_faktur, catatan, dpp, dpp_nilai_lain, ppn, total_terima_fp, status, kd_user) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"; // Tambah ? satu lagi
        $stmt = $conn->prepare($query);
        if (!$stmt)
            throw new Exception("Prepare Insert Error: " . $conn->error);

        // Tambah 's' pada tipe data
        $stmt->bind_param(
            "sssssssddddsi", // Tambah 's'
            $nama_supplier,
            $kode_supplier,
            $kode_store,
            $tgl_nota,
            $no_invoice,
            $no_faktur,
            $catatan, // TAMBAHAN VARIABLE
            $dpp,
            $dpp_nilai_lain,
            $ppn,
            $total_terima_fp,
            $status,
            $kd_user
        );
        if (!$stmt->execute()) {
            throw new Exception("Gagal menyimpan data: " . $stmt->error);
        }
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