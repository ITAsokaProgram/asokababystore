<?php
session_start();
require_once __DIR__ . '/../../../aa_kon_sett.php';
require_once __DIR__ . '/../../auth/middleware_login.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}

try {
    $verif = authenticate_request();
    $kd_user = $verif->id ?? $verif->kode ?? 0;

    $input = json_decode(file_get_contents('php://input'), true);
    $fakturs = $input['fakturs'] ?? [];

    if (empty($fakturs) || !is_array($fakturs)) {
        throw new Exception('Data faktur tidak valid');
    }

    $success_count = 0;
    $conn->begin_transaction();

    $stmt = $conn->prepare("UPDATE serah_terima_nota SET dicetak_oleh = ?, sudah_dicetak = 'Sudah' WHERE no_faktur = ? AND (sudah_dicetak IS NULL OR sudah_dicetak = 'Belum')");

    foreach ($fakturs as $no_faktur) {
        $stmt->bind_param("is", $kd_user, $no_faktur);
        $stmt->execute();
        if ($stmt->affected_rows > 0) {
            $success_count++;
        }
    }

    $conn->commit();
    $stmt->close();

    echo json_encode([
        'success' => true,
        'message' => "Berhasil update status cetak untuk $success_count nota.",
        'updated_count' => $success_count
    ]);

} catch (Exception $e) {
    if (isset($conn))
        $conn->rollback();
    $code = $e->getCode() ?: 500;
    http_response_code($code == 401 ? 401 : 200);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} finally {
    if (isset($conn))
        $conn->close();
}
?>