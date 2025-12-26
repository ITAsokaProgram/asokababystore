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
    $is_single_transaction = false;
    if (!isset($input['details']) && isset($input['no_faktur'])) {
        $single_item = $input;
        $is_single_transaction = true;
        $header = [
            'store_bayar' => $input['store_bayar'] ?? '',
            'tanggal_bayar' => $input['tanggal_bayar'] ?? date('Y-m-d'),
            'ket' => $input['ket'] ?? '',
            'group_id' => null,
            'kode_supplier' => $input['kode_supplier'] ?? '',
            'nama_supplier' => $input['nama_supplier'] ?? ''
        ];
        $input = [
            'header' => $header,
            'details' => [$single_item],
            'deleted_ids' => []
        ];
    }
    if (isset($input['details']) && is_array($input['details'])) {
        $header = $input['header'] ?? [];
        $details = $input['details'];
        $deletedIds = $input['deleted_ids'] ?? [];
        if (empty($details) && empty($deletedIds))
            throw new Exception("Tidak ada item untuk disimpan.");
        $store_bayar = $header['store_bayar'] ?? '';
        $tanggal_bayar = !empty($header['tanggal_bayar']) ? $header['tanggal_bayar'] : null;
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
            $queryInsert = "INSERT INTO buku_besar 
                      (group_id, tgl_nota, no_faktur, kode_supplier, nama_supplier, 
                      potongan, ket_potongan, nilai_faktur, total_bayar, tanggal_bayar, 
                      kode_store, store_bayar, ket, kd_user)
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $queryUpdate = "UPDATE buku_besar SET 
                            tgl_nota=?, no_faktur=?, kode_supplier=?, nama_supplier=?, 
                            potongan=?, ket_potongan=?, nilai_faktur=?, total_bayar=?, tanggal_bayar=?, 
                            kode_store=?, store_bayar=?, ket=?, kd_user=?, edit_pada=NOW()
                            WHERE id=?";
            $stmtInsert = $conn->prepare($queryInsert);
            $stmtUpdate = $conn->prepare($queryUpdate);
            $processed_count = 0;
            foreach ($details as $item) {
                $id = isset($item['id']) && $item['id'] !== "" ? (int) $item['id'] : null;
                $no_faktur = trim($item['no_faktur'] ?? '');
                $kode_store = $item['kode_store'] ?? '';
                $kode_supplier = $header['kode_supplier'] ?? ($item['kode_supplier'] ?? '');
                $nama_supplier = $header['nama_supplier'] ?? ($item['nama_supplier'] ?? '');
                $tgl_nota = !empty($item['tgl_nota']) ? $item['tgl_nota'] : null;
                $nilai_faktur = (float) ($item['nilai_faktur'] ?? 0);
                $potongan = (float) ($item['potongan'] ?? 0);
                $ket_potongan = $item['ket_potongan'] ?? '';
                $total_bayar = (float) ($item['total_bayar'] ?? 0);
                $ket_item = !empty($item['ket']) ? $item['ket'] : ($header['ket'] ?? '');
                if (empty($no_faktur) || empty($kode_store)) {
                    throw new Exception("No Faktur dan Cabang Invoice wajib diisi.");
                }
                if ($id) {
                    $stmtUpdate->bind_param(
                        "ssssdsddsssssi",
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
                        $store_bayar,
                        $ket_item,
                        $kd_user,
                        $id
                    );
                    if (!$stmtUpdate->execute())
                        throw new Exception("Gagal update ID $id: " . $stmtUpdate->error);
                } else {
                    $stmtInsert->bind_param(
                        "sssssdsddsssss",
                        $gen_group_id,
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
                        $store_bayar,
                        $ket_item,
                        $kd_user
                    );
                    if (!$stmtInsert->execute())
                        throw new Exception("Gagal insert: " . $stmtInsert->error);
                }
                $processed_count++;
            }
            $conn->commit();
            echo json_encode([
                'success' => true,
                'message' => "Berhasil menyimpan data."
            ]);
        } catch (Exception $e) {
            $conn->rollback();
            throw $e;
        }
    } else {
        throw new Exception("Format request tidak valid. Data kosong.");
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>