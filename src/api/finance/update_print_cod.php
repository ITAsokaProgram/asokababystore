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
    $no_faktur = $input['no_faktur'] ?? null;
    if (!$no_faktur) {
        throw new Exception('No Faktur tidak valid');
    }
    $stmt_check = $conn->prepare("SELECT dicetak_oleh FROM serah_terima_nota WHERE no_faktur = ?");
    $stmt_check->bind_param("s", $no_faktur);
    $stmt_check->execute();
    $result = $stmt_check->get_result();
    $data = $result->fetch_assoc();
    $stmt_check->close();
    $updated = false;
    if (empty($data['dicetak_oleh'])) {
        $stmt_update = $conn->prepare("UPDATE serah_terima_nota SET dicetak_oleh = ?, sudah_dicetak = ? WHERE no_faktur = ?");
        $done = "Sudah";
        $stmt_update->bind_param("iss", $kd_user, $done, $no_faktur);
        if ($stmt_update->execute()) {
            $updated = true;
        } else {
            throw new Exception("Gagal update database: " . $stmt_update->error);
        }
        $stmt_update->close();
    }
    echo json_encode([
        'success' => true,
        'message' => $updated ? 'Status cetak diperbarui.' : 'Sudah pernah dicetak sebelumnya.',
        'first_print' => $updated
    ]);
} catch (Exception $e) {
    $code = $e->getCode() ?: 500;
    http_response_code($code == 401 ? 401 : 200);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} finally {
    if (isset($conn))
        $conn->close();
}
?>