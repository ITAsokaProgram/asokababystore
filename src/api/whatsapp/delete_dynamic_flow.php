<?php
session_start();
include '../../../aa_kon_sett.php';
header('Content-Type: application/json');

try {
    $input = json_decode(file_get_contents('php://input'), true);
    $id = $input['id'] ?? null;

    if (!$id)
        throw new Exception("ID tidak valid");

    $conn->begin_transaction();

    // Hapus steps dulu (FK)
    $conn->query("DELETE FROM wa_flow_steps WHERE flow_id = $id");

    // Hapus flow
    $conn->query("DELETE FROM wa_flows WHERE id = $id");

    // Hapus sesi aktif terkait (Opsional, agar bersih)
    $conn->query("DELETE FROM wa_flow_sessions WHERE flow_id = $id");

    $conn->commit();
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>