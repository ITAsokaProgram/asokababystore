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
    $kode_supplier = '';
    $kode_store = $input['kode_store'] ?? '';
    $status = $input['status'] ?? 'PKP';
    $nama_supplier = $input['nama_supplier'] ?? '';
    $tgl_nota = $input['tgl_nota'] ?? date('Y-m-d');
    $d = DateTime::createFromFormat('Y-m-d', $tgl_nota);
    if (!$d || $d->format('Y-m-d') !== $tgl_nota) {
        throw new Exception("Format tanggal atau nilai tanggal nota salah, cek tanggal yang diinput");
    }

    $year = (int) $d->format('Y');
    if ($year < 2000 || $year > 2099) {
        throw new Exception("Tahun tanggal nota tidak valid ($year). Mohon periksa input tanggal.");
    }
    $no_faktur = $input['no_faktur'] ?? '';
    $dpp = (float) ($input['dpp'] ?? 0);
    $dpp_nilai_lain = (float) ($input['dpp_nilai_lain'] ?? 0);
    $ppn = (float) ($input['ppn'] ?? 0);
    $total_terima_fp = (float) ($input['total_terima_fp'] ?? 0);
    if (empty($no_faktur) || empty($nama_supplier)) {
        throw new Exception("No Invoice dan Nama Supplier wajib diisi.");
    }
    if ($id) {
        // UPDATE QUERY
        $query = "UPDATE ff_pembelian SET 
                    nama_supplier=?, 
                    kode_store=?, 
                    tgl_nota=?, 
                    no_faktur=?, 
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
        $stmt->bind_param(
            "ssssddddisi",
            $nama_supplier,
            $kode_store,
            $tgl_nota,
            $no_faktur,
            $dpp,
            $dpp_nilai_lain,
            $ppn,
            $total_terima_fp,
            $kd_user,
            $status, // string
            $id
        );
        if (!$stmt->execute()) {
            throw new Exception("Gagal mengupdate data: " . $stmt->error);
        }
        $message = "Data berhasil diperbarui.";
    } else {
        // INSERT QUERY
        $query = "INSERT INTO ff_pembelian 
                  (nama_supplier, kode_supplier, kode_store, tgl_nota, no_faktur, dpp, dpp_nilai_lain, ppn, total_terima_fp, status, kd_user) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        // Types: sssssddddsi
        $stmt->bind_param(
            "sssssddddsi",
            $nama_supplier,
            $kode_supplier,
            $kode_store,
            $tgl_nota,
            $no_faktur,
            $dpp,
            $dpp_nilai_lain,
            $ppn,
            $total_terima_fp,
            $status, // string
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