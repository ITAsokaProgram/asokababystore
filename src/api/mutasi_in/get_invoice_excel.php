<?php
header('Content-Type: application/json');
include '../../../aa_kon_sett.php';

$no_faktur = $_GET['no_faktur'] ?? '';
$kode_dari = $_GET['kode_dari'] ?? '';
$tgl_mutasi = $_GET['tgl_mutasi'] ?? ''; // Format Y-m-d

if (empty($no_faktur) || empty($kode_dari)) {
    http_response_code(400);
    echo json_encode(['error' => 'Parameter tidak lengkap']);
    exit;
}

try {
    // 1. QUERY HEADER (Sesuai VB)
    // Note: Menggunakan mutasi_in_copy sesuai standar sistem Anda saat ini
    $sqlHeader = "
        SELECT 
            mi.tgl_mutasi,
            mi.no_faktur,
            (SUM(mi.qty * mi.netto) + SUM(mi.qty * IFNULL(mi.ppn, 0))) AS Total,
            kds.nm_npwp as d_cv,
            kds.alm_NPWP as d_alm,
            kds.no_npwp as d_npwp,
            kd.nm_npwp as t_cv,
            kd.alm_NPWP as t_alm,
            kd.no_npwp as t_npwp,
            SUM(mi.qty * mi.netto) AS Sub_Total, 
            IFNULL(SUM(mi.qty * mi.ppn), 0) AS Ppn1 
        FROM mutasi_in_copy mi
        LEFT JOIN kode_store kd ON mi.kode_tujuan = kd.kd_store
        LEFT JOIN kode_store kds ON mi.kode_dari = kds.kd_store
        WHERE mi.kode_dari = ? 
          AND DATE(mi.tgl_mutasi) = ? 
          AND mi.no_faktur = ?
        GROUP BY mi.no_faktur, mi.kode_dari
    ";

    $stmtHeader = $conn->prepare($sqlHeader);
    $stmtHeader->bind_param("sss", $kode_dari, $tgl_mutasi, $no_faktur);
    $stmtHeader->execute();
    $resHeader = $stmtHeader->get_result()->fetch_assoc();

    if (!$resHeader) {
        throw new Exception("Data Header Faktur tidak ditemukan.");
    }

    // 2. QUERY DETAIL (Sesuai VB)
    $conn->query("SET @nomor_urut = 0");
    
    $sqlDetail = "
        SELECT 
            @nomor_urut := @nomor_urut + 1 AS 'No',
            plu,
            descp,
            qty,
            satuan,
            netto,
            ppn,
            (netto + ppn) * qty AS total 
        FROM mutasi_in_copy 
        WHERE kode_dari = ? 
          AND DATE(tgl_mutasi) = ? 
          AND no_faktur = ? 
        ORDER BY jam
    ";

    $stmtDetail = $conn->prepare($sqlDetail);
    $stmtDetail->bind_param("sss", $kode_dari, $tgl_mutasi, $no_faktur);
    $stmtDetail->execute();
    $resDetail = $stmtDetail->get_result();

    $details = [];
    while ($row = $resDetail->fetch_assoc()) {
        $details[] = $row;
    }

    echo json_encode([
        'success' => true,
        'header' => $resHeader,
        'details' => $details
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>