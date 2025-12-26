<?php
session_start();
ini_set('display_errors', 0);
header('Content-Type: application/json');
require_once __DIR__ . '/../../../aa_kon_sett.php';
require_once __DIR__ . '/../../auth/middleware_login.php';
try {
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    if (!preg_match('/^Bearer\s(\S+)$/', $authHeader, $matches))
        throw new Exception("Token missing");
    $verif = verify_token($matches[1]);
    if (!$verif)
        throw new Exception("Token invalid");
    $kd_user = $verif->id ?? $verif->kode ?? 0;
    $json = file_get_contents('php://input');
    $input = json_decode($json, true);
    $id = isset($input['id']) && !empty($input['id']) ? (int) $input['id'] : null;
    $no_faktur = trim($input['no_faktur'] ?? '');
    $kode_store = $input['kode_store'] ?? '';
    $store_bayar = $input['store_bayar'] ?? ''; // AMBIL DATA BARU
    if (empty($no_faktur) || empty($kode_store)) {
        throw new Exception("No Faktur dan Cabang wajib diisi.");
    }
    $tgl_nota = !empty($input['tgl_nota']) ? $input['tgl_nota'] : null;
    $kode_supplier = $input['kode_supplier'] ?? '';
    $nama_supplier = $input['nama_supplier'] ?? '';
    $potongan = (float) ($input['potongan'] ?? 0);
    $ket_potongan = $input['ket_potongan'] ?? '';
    $total_bayar = (float) ($input['total_bayar'] ?? 0);
    $tanggal_bayar = !empty($input['tanggal_bayar']) ? $input['tanggal_bayar'] : null;
    $ket = $input['ket'] ?? '';
    $nilai_faktur = (float) ($input['nilai_faktur'] ?? 0);
    if ($id) {
        // UPDATE QUERY
        $query = "UPDATE buku_besar SET 
                  tgl_nota=?, no_faktur=?, kode_supplier=?, nama_supplier=?, 
                  potongan=?, ket_potongan=?, nilai_faktur=?, total_bayar=?, tanggal_bayar=?, 
                  kode_store=?, store_bayar=?, ket=?, kd_user=?, edit_pada=NOW() 
                  WHERE id=?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param(
            "ssssdsddsssssi", // Perhatikan jumlah 's' bertambah satu untuk store_bayar
            $tgl_nota,
            $no_faktur,
            $kode_supplier,
            $nama_supplier,
            $potongan,
            $ket_potongan,
            $nilai_faktur,
            $total_bayar,
            $tanggal_bayar,
            $kode_store,
            $store_bayar, // Bind variable baru
            $ket,
            $kd_user,
            $id
        );
    } else {
        // INSERT QUERY
        $query = "INSERT INTO buku_besar 
                  (tgl_nota, no_faktur, kode_supplier, nama_supplier, 
                  potongan, ket_potongan, nilai_faktur, total_bayar, tanggal_bayar, 
                  kode_store, store_bayar, ket, kd_user)
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param(
            "ssssdsddsssss", // Tambah tipe data string
            $tgl_nota,
            $no_faktur,
            $kode_supplier,
            $nama_supplier,
            $potongan,
            $ket_potongan,
            $nilai_faktur,
            $total_bayar,
            $tanggal_bayar,
            $kode_store,
            $store_bayar, // Bind variable baru
            $ket,
            $kd_user
        );
    }
    if (!$stmt->execute()) {
        throw new Exception("Database Error: " . $stmt->error);
    }
    $message = $id ? "Data updated successfully" : "Data saved successfully";
    echo json_encode(['success' => true, 'message' => $message]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
