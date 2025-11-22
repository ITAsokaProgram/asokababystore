<?php
header('Content-Type: application/json');
include '../../../aa_kon_sett.php';

$no_faktur = $_GET['no_faktur'] ?? '';
$kode_dari = $_GET['kode_dari'] ?? '';
$tgl_mutasi = $_GET['tgl_mutasi'] ?? ''; // Format Y-m-d

if (empty($no_faktur)) {
    echo json_encode(['data' => []]);
    exit;
}

try {
    // Query detail sesuai logika VB User
    // Menggunakan @nomor_urut variabel
    $conn->query("SET @nomor_urut = 0");

    $sql = "
        SELECT 
            @nomor_urut := @nomor_urut + 1 AS no_urut,
            plu,
            barcode,
            descp,
            qty,
            satuan,
            hrg_beli,
            ppn,
            (hrg_beli * qty) as total_netto_row,
            ((hrg_beli + ppn) * qty) as total_row
        FROM mutasi_in_copy
        WHERE no_faktur = ? 
          AND kode_dari = ?
          AND DATE(tgl_mutasi) = ?
        ORDER BY jam ASC
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $no_faktur, $kode_dari, $tgl_mutasi);
    $stmt->execute();
    $result = $stmt->get_result();

    $details = [];
    while ($row = $result->fetch_assoc()) {
        $details[] = $row;
    }

    echo json_encode(['data' => $details]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>