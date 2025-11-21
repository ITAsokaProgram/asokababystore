<?php
session_start();
ini_set('display_errors', 0);
require_once __DIR__ . '/../../../aa_kon_sett.php';
require_once __DIR__ . '/../../auth/middleware_login.php';
header('Content-Type: application/json');
try {
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method Not Allowed');
    }
    $input = json_decode(file_get_contents('php://input'), true);
    $stores = $input['stores'] ?? [];
    $nama_manual = strtoupper(preg_replace('/[^A-Z0-9]/', '', $input['nama_manual'] ?? ''));
    $start_sequence = (int) ($input['start_sequence'] ?? 1);
    $jumlah = (int) ($input['jumlah'] ?? 0);
    $nilai = (int) ($input['nilai'] ?? 0);
    $tgl_mulai = $input['tgl_mulai'] ?? '';
    $tgl_akhir = $input['tgl_akhir'] ?? '';
    $pemilik = strtoupper($input['pemilik'] ?? 'SYSTEM');
    $tgl_beli = date('Y-m-d H:i:s');
    if (empty($stores))
        throw new Exception("Toko belum dipilih.");
    if (empty($nama_manual))
        throw new Exception("Nama voucher manual kosong.");
    if (strlen($nama_manual) > 8)
        throw new Exception("Nama voucher manual maksimal 8 karakter.");
    if ($start_sequence > 999)
        throw new Exception("Nomor urut maksimal 999.");
    if ($jumlah < 1)
        throw new Exception("Jumlah voucher minimal 1.");
    if ($nilai <= 0)
        throw new Exception("Nilai voucher tidak valid.");
    if (empty($tgl_mulai) || empty($tgl_akhir))
        throw new Exception("Tanggal wajib diisi.");
    if (strtotime($tgl_mulai) < strtotime(date('Y-m-d'))) {
        throw new Exception("Tanggal mulai tidak boleh tanggal lampau.");
    }
    $tgl_awal_sql = date('Y-m-d 00:00:00', strtotime($tgl_mulai));
    $tgl_akhir_sql = date('Y-m-d 23:59:59', strtotime($tgl_akhir));
    $kd_cust = '999999999';
    $flag = 'False';
    $pakai = 0;
    $sisa = $nilai;
    $conn->begin_transaction();
    $total_inserted = 0;
    $store_details = [];
    if (!empty($stores)) {
        $store_ids_str = implode(',', array_map(function ($id) use ($conn) {
            return "'" . $conn->real_escape_string($id) . "'";
        }, $stores));
        $q_store = $conn->query("SELECT Kd_Store, Nm_Alias FROM kode_store WHERE Kd_Store IN ($store_ids_str)");
        while ($r = $q_store->fetch_assoc()) {
            $store_details[$r['Kd_Store']] = $r['Nm_Alias'];
        }
    }
    $sql_insert = "INSERT INTO voucher_copy (
        kd_voucher, nilai, pakai, sisa, 
        tgl_awal, tgl_akhir, kd_cust, flag, 
        tgl_beli, last_sold, pemilik, kd_store
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NULL, ?, ?)";
    $stmt = $conn->prepare($sql_insert);
    if (!$stmt)
        throw new Exception("Prepare failed: " . $conn->error);
    foreach ($stores as $kd_store) {
        if (!isset($store_details[$kd_store]))
            continue;
        $nm_alias = $store_details[$kd_store]; 
        for ($i = 0; $i < $jumlah; $i++) {
            $current_num = $start_sequence + $i;
            $sequence_str = str_pad($current_num, 3, '0', STR_PAD_LEFT);
            $kd_voucher = $nm_alias . $nama_manual . $sequence_str;
            if (strlen($kd_voucher) > 16) {
                throw new Exception("Panjang Kode Voucher '$kd_voucher' melebihi 16 karakter. Kurangi panjang Nama Manual.");
            }
            $stmt->bind_param(
                "siiisssssss",
                $kd_voucher,
                $nilai,
                $pakai,
                $sisa,
                $tgl_awal_sql,
                $tgl_akhir_sql,
                $kd_cust,
                $flag,
                $tgl_beli,
                $pemilik,
                $kd_store
            );
            if ($stmt->execute()) {
                $total_inserted++;
            } else {
                if ($conn->errno == 1062) {
                    throw new Exception("Gagal Insert: Kode Voucher '$kd_voucher' sudah terdaftar/duplikat. Cek nomor urut atau nama manual.");
                } else {
                    throw new Exception("Database Error pada '$kd_voucher': " . $stmt->error);
                }
            }
        }
    }
    $stmt->close();
    $conn->commit();
    echo json_encode([
        'success' => true,
        'message' => "Berhasil membuat $total_inserted voucher baru.",
        'data' => ['total' => $total_inserted]
    ]);
} catch (Exception $e) {
    $conn->rollback();
    http_response_code(200);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} finally {
    if (isset($conn))
        $conn->close();
}
?>