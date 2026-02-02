<?php
session_start();
ini_set('display_errors', 0);
header('Content-Type: application/json');
require_once __DIR__ . '/../../../aa_kon_sett.php';
try {
    if (!isset($_GET['group_id'])) {
        throw new Exception("Group ID is required");
    }
    $group_id = $_GET['group_id'];
    $query = "SELECT bb.*, 
                     ks.Nm_Alias as nm_alias,
                     ks_bayar.Nm_Alias as nm_alias_bayar 
              FROM buku_besar bb
              LEFT JOIN kode_store ks ON bb.kode_store = ks.Kd_Store
              LEFT JOIN kode_store ks_bayar ON bb.store_bayar = ks_bayar.Kd_Store
              WHERE bb.group_id = ?
              ORDER BY bb.id ASC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $group_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $row['id'] = (int) $row['id'];
        $row['nilai_faktur'] = (float) $row['nilai_faktur'];
        $row['potongan'] = (float) $row['potongan'];
        $row['nilai_tambahan'] = (float) ($row['nilai_tambahan'] ?? 0); 
        $row['total_bayar'] = (float) $row['total_bayar'];
        $details_potongan = [];
        $qPot = $conn->query("SELECT nominal, keterangan FROM buku_besar_potongan WHERE buku_besar_id = " . $row['id']);
        if ($qPot) {
            while ($rp = $qPot->fetch_assoc()) {
                $details_potongan[] = [
                    'nominal' => (float) $rp['nominal'],
                    'keterangan' => $rp['keterangan']
                ];
            }
        }
        $row['details_potongan'] = $details_potongan;
        $details_tambahan = [];
        $qTam = $conn->query("SELECT nominal, keterangan FROM buku_besar_tambahan WHERE buku_besar_id = " . $row['id']);
        if ($qTam) {
            while ($rt = $qTam->fetch_assoc()) {
                $details_tambahan[] = [
                    'nominal' => (float) $rt['nominal'],
                    'keterangan' => $rt['keterangan']
                ];
            }
        }
        $row['details_tambahan'] = $details_tambahan;
        $data[] = $row;
    }
    echo json_encode(['success' => true, 'data' => $data]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>