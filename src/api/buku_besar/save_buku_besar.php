<?php
session_start();
ini_set('display_errors', 0);
header('Content-Type: application/json');
require_once __DIR__ . '/../../../aa_kon_sett.php';
require_once __DIR__ . '/../../auth/middleware_login.php';
try {
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    if (!preg_match('/^Bearer\s(\S+)$/', $authHeader, $matches)) {
        throw new Exception("Token missing");
    }
    $verif = verify_token($matches[1]);
    if (!$verif) {
        throw new Exception("Token invalid");
    }
    $kd_user = $verif->id ?? $verif->kode ?? 0;
    $json = file_get_contents('php://input');
    $input = json_decode($json, true);
    $is_single_transaction = false;
    if (!isset($input['details']) && isset($input['no_faktur'])) {
        $single_item = $input;
        $is_single_transaction = true;
        $header = [
            'store_bayar' => $input['store_bayar'] ?? '',
            'tanggal_bayar' => $input['tanggal_bayar'] ?? date('Y-m-d'),
            'ket' => $input['ket'] ?? '',
            'kode_supplier' => $input['kode_supplier'] ?? '',
            'nama_supplier' => $input['nama_supplier'] ?? ''
        ];
        $input = [
            'header' => $header,
            'details' => [$single_item],
            'deleted_ids' => []
        ];
    }
    if (!isset($input['details']) || !is_array($input['details'])) {
        throw new Exception("Format request tidak valid. Data kosong.");
    }
    $header = $input['header'] ?? [];
    $details = $input['details'];
    $deletedIds = $input['deleted_ids'] ?? [];
    if (empty($details) && empty($deletedIds)) {
        throw new Exception("Tidak ada item untuk disimpan.");
    }
    $store_bayar_global = $header['store_bayar'] ?? '';
    $tanggal_bayar_global = !empty($header['tanggal_bayar']) ? $header['tanggal_bayar'] : date('Y-m-d');
    $existing_group_id = $header['group_id'] ?? null;
    if ($existing_group_id) {
        $gen_group_id = $existing_group_id;
    } elseif ($is_single_transaction) {
        $gen_group_id = null;
    } else {
        $gen_group_id = "BB-" . date('YmdHis') . "-" . uniqid();
    }
    $conn->begin_transaction();
    try {
        if (!empty($deletedIds)) {
            $deletedIds = array_map('intval', $deletedIds);
            $idsPlaceholder = implode(',', array_fill(0, count($deletedIds), '?'));
            $stmtDel = $conn->prepare("DELETE FROM buku_besar WHERE id IN ($idsPlaceholder)");
            $types = str_repeat('i', count($deletedIds));
            $stmtDel->bind_param($types, ...$deletedIds);
            $stmtDel->execute();
        }
        $stmtCheck = $conn->prepare("SELECT id, total_bayar FROM buku_besar WHERE no_faktur = ? AND kode_store = ? LIMIT 1");
        $stmtInsertHead = $conn->prepare("INSERT INTO buku_besar 
              (group_id, tgl_nota, no_faktur, kode_supplier, nama_supplier, 
               potongan, ket_potongan, nilai_faktur, total_bayar, tanggal_bayar, 
               kode_store, store_bayar, ket, kd_user, top, status)
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmtUpdateHead = $conn->prepare("UPDATE buku_besar SET 
            total_bayar = total_bayar + ?, 
            tanggal_bayar = ?, 
            store_bayar = ?, 
            ket = ?,
            top = ?,
            status = ?,
            edit_pada = NOW() 
            WHERE id = ?");
        $stmtInsertAngsuran = $conn->prepare("INSERT INTO buku_besar_angsuran 
              (buku_besar_id, tanggal_bayar, nominal_bayar, store_bayar, ket, kd_user)
              VALUES (?, ?, ?, ?, ?, ?)");
        foreach ($details as $item) {
            $no_faktur = trim($item['no_faktur'] ?? '');
            $kode_store = $item['kode_store'] ?? '';
            if (empty($no_faktur) || empty($kode_store)) {
                throw new Exception("No Faktur dan Cabang Invoice wajib diisi.");
            }
            $kode_supplier = $item['kode_supplier'] ?? ($header['kode_supplier'] ?? '');
            $nama_supplier = $item['nama_supplier'] ?? ($header['nama_supplier'] ?? '');
            $tgl_nota = !empty($item['tgl_nota']) ? $item['tgl_nota'] : null;
            $top = !empty($item['top']) ? $item['top'] : null;
            $status = !empty($item['status']) ? $item['status'] : null;
            $nilai_faktur = (float) ($item['nilai_faktur'] ?? 0);
            $potongan = (float) ($item['potongan'] ?? 0);
            $ket_potongan = $item['ket_potongan'] ?? '';
            $nominal_bayar_ini = (float) ($item['total_bayar'] ?? 0);
            $store_bayar = !empty($item['store_bayar']) ? $item['store_bayar'] : $store_bayar_global;
            $tanggal_bayar = !empty($item['tanggal_bayar']) ? $item['tanggal_bayar'] : $tanggal_bayar_global;
            $ket_mop = !empty($item['ket']) ? $item['ket'] : ($header['ket'] ?? '');
            $stmtCheck->bind_param("ss", $no_faktur, $kode_store);
            $stmtCheck->execute();
            $resCheck = $stmtCheck->get_result();
            $existingData = $resCheck->fetch_assoc();
            $buku_besar_id = 0;
            if ($existingData) {
                $buku_besar_id = $existingData['id'];
                $stmtUpdateHead->bind_param(
                    "dsssssi",
                    $nominal_bayar_ini,
                    $tanggal_bayar,
                    $store_bayar,
                    $ket_mop,
                    $top,
                    $status,
                    $buku_besar_id
                );
                if (!$stmtUpdateHead->execute()) {
                    throw new Exception("Gagal update angsuran (ID: $buku_besar_id): " . $stmtUpdateHead->error);
                }
            } else {
                $stmtInsertHead->bind_param(
                    "sssssdsddsssssss",
                    $gen_group_id,
                    $tgl_nota,
                    $no_faktur,
                    $kode_supplier,
                    $nama_supplier,
                    $potongan,
                    $ket_potongan,
                    $nilai_faktur,
                    $nominal_bayar_ini,
                    $tanggal_bayar,
                    $kode_store,
                    $store_bayar,
                    $ket_mop,
                    $kd_user,
                    $top,
                    $status
                );
                if (!$stmtInsertHead->execute()) {
                    throw new Exception("Gagal insert header faktur baru: " . $stmtInsertHead->error);
                }
                $buku_besar_id = $conn->insert_id;
            }
            $is_full_payment = (abs($nominal_bayar_ini - $nilai_faktur) < 100);

            if (!$is_full_payment && $nominal_bayar_ini > 0) {
                $stmtInsertAngsuran->bind_param(
                    "isdsss",
                    $buku_besar_id,
                    $tanggal_bayar,
                    $nominal_bayar_ini,
                    $store_bayar,
                    $ket_mop,
                    $kd_user
                );
                if (!$stmtInsertAngsuran->execute()) {
                    throw new Exception("Gagal mencatat histori angsuran: " . $stmtInsertAngsuran->error);
                }
            }
        }
        $conn->commit();
        echo json_encode([
            'success' => true,
            'message' => "Data berhasil disimpan." . ($is_full_payment ? "" : " Pembayaran angsuran tercatat.")
        ]);
    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} finally {
    if (isset($conn))
        $conn->close();
}
?>