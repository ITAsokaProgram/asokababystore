<?php
session_start();
include '../../../aa_kon_sett.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);

    $id = $input['id'] ?? null;
    $nsfp = $input['nsfp'] ?? null;

    if (!$id || !$nsfp) {
        throw new Exception("ID Pembelian atau NSFP tidak valid.");
    }

    // Update ff_pembelian
    $sql = "UPDATE ff_pembelian SET ada_di_coretax = 1, nsfp = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("si", $nsfp, $id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Data berhasil dikonfirmasi ke Coretax']);
    } else {
        throw new Exception("Gagal mengupdate data: " . $stmt->error);
    }

    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>