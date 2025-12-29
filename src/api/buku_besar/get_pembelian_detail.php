<?php
session_start();
ini_set('display_errors', 0);
header('Content-Type: application/json');
require_once __DIR__ . '/../../../aa_kon_sett.php';
try {
    $raw_term = $_GET['no_faktur'] ?? '';
    $term = trim(urldecode($raw_term));
    $kode_store = $_GET['kode_store'] ?? '';
    if (empty($term)) {
        throw new Exception("Parameter pencarian kosong");
    }
    $queryBB = "SELECT * FROM buku_besar WHERE no_faktur = ? ";
    $paramsType = "s";
    $paramsVal = [$term];
    if (!empty($kode_store)) {
        $queryBB .= " AND kode_store = ? ";
        $paramsType .= "s";
        $paramsVal[] = $kode_store;
    }
    $queryBB .= " LIMIT 1";
    $stmtBB = $conn->prepare($queryBB);
    $stmtBB->bind_param($paramsType, ...$paramsVal);
    $stmtBB->execute();
    $resBB = $stmtBB->get_result();
    $dataBB = $resBB->fetch_assoc();

    if ($dataBB) {
        // --- LOGIKA TAMBAHAN START ---
        $groupTotals = null;
        if (!empty($dataBB['group_id'])) {
            // Hitung total satu group
            $queryGroup = "SELECT 
                            SUM(nilai_faktur) as total_nilai_group, 
                            SUM(total_bayar) as total_bayar_group,
                            SUM(potongan) as total_potongan_group
                           FROM buku_besar 
                           WHERE group_id = ?";
            $stmtGroup = $conn->prepare($queryGroup);
            $stmtGroup->bind_param("s", $dataBB['group_id']);
            $stmtGroup->execute();
            $resGroup = $stmtGroup->get_result()->fetch_assoc();

            if ($resGroup) {
                $groupTotals = [
                    'nilai_faktur' => (float) $resGroup['total_nilai_group'],
                    'total_bayar' => (float) $resGroup['total_bayar_group'],
                    'potongan' => (float) $resGroup['total_potongan_group']
                ];
            }
        }
        // --- LOGIKA TAMBAHAN END ---

        echo json_encode([
            'success' => true,
            'found' => true,
            'source' => 'buku_besar',
            'data' => [
                'id' => $dataBB['id'],
                'group_id' => $dataBB['group_id'],
                'no_faktur' => $dataBB['no_faktur'],
                'tgl_nota' => $dataBB['tgl_nota'],
                'kode_supplier' => $dataBB['kode_supplier'],
                'nama_supplier' => $dataBB['nama_supplier'],
                'kode_store' => $dataBB['kode_store'],
                'nilai_faktur' => (float) $dataBB['nilai_faktur'],
                'total_bayar' => (float) $dataBB['total_bayar'],
                'potongan' => (float) $dataBB['potongan'],
                'ket_potongan' => $dataBB['ket_potongan'],
                'top' => $dataBB['top'],
                'status' => $dataBB['status'],
                'group_totals' => $groupTotals // <--- Tambahkan ini ke response JSON
            ]
        ]);
        exit();
    }
    $query = "
        SELECT 
            id,
            no_faktur,
            no_invoice,
            tgl_nota,
            kode_supplier,
            nama_supplier,
            kode_store,
            total_terima_fp as total_nilai,
            status
        FROM ff_pembelian
        WHERE no_faktur = ? OR no_invoice = ?
        LIMIT 1
    ";
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception("Database Error: " . $conn->error);
    }
    $stmt->bind_param("ss", $term, $term);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    if ($data) {
        if (!empty($data['tgl_nota'])) {
            $data['tgl_nota'] = date('Y-m-d', strtotime($data['tgl_nota']));
        }
        echo json_encode([
            'success' => true,
            'found' => true,
            'source' => 'pembelian',
            'data' => [
                'id_pembelian' => $data['id'],
                'status' => $data['status'],
                'no_faktur' => !empty($data['no_invoice']) ? $data['no_invoice'] : $data['no_faktur'],
                'no_invoice_asli' => $data['no_invoice'],
                'tgl_nota' => $data['tgl_nota'],
                'kode_supplier' => $data['kode_supplier'],
                'nama_supplier' => $data['nama_supplier'],
                'kode_store' => $data['kode_store'],
                'total_bayar' => (float) $data['total_nilai'],
            ]
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'found' => false,
            'message' => "Data tidak ditemukan berdasarkan No Invoice maupun No Faktur."
        ]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>