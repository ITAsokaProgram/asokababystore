<?php
session_start();
ini_set('display_errors', 0);
header('Content-Type: application/json');
require_once __DIR__ . '/../../../aa_kon_sett.php';

try {
    if (!$conn)
        throw new Exception("Koneksi Database Gagal");

    $id = $_GET['id'] ?? '';
    $group_id = $_GET['group_id'] ?? '';

    if (empty($id))
        throw new Exception("ID tidak valid");

    $bind_types = "";
    $bind_params = [];

    // Logika: Jika ada Group ID, ambil semua potongan milik group tersebut
    // Jika tidak, ambil berdasarkan ID buku_besar saja
    if (!empty($group_id)) {
        $sql = "
            SELECT 
                bp.id,
                bp.nominal,
                bp.keterangan,
                bb.no_faktur,
                bb.tgl_nota
            FROM buku_besar_potongan bp
            JOIN buku_besar bb ON bb.id = bp.buku_besar_id
            WHERE bb.group_id = ?
            ORDER BY bb.tgl_nota DESC, bp.id ASC
        ";
        $bind_types = "s";
        $bind_params[] = $group_id;
    } else {
        $sql = "
            SELECT 
                bp.id,
                bp.nominal,
                bp.keterangan,
                bb.no_faktur,
                bb.tgl_nota
            FROM buku_besar_potongan bp
            JOIN buku_besar bb ON bb.id = bp.buku_besar_id
            WHERE bp.buku_besar_id = ?
            ORDER BY bp.id ASC
        ";
        $bind_types = "i";
        $bind_params[] = $id;
    }

    $stmt = $conn->prepare($sql);
    if (!empty($bind_params)) {
        $stmt->bind_param($bind_types, ...$bind_params);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    echo json_encode(['success' => true, 'data' => $data]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} finally {
    if (isset($conn))
        $conn->close();
}
?>