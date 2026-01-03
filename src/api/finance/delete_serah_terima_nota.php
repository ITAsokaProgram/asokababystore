<?php
session_start();
ini_set('display_errors', 0);
header('Content-Type: application/json');
require_once __DIR__ . '/../../../aa_kon_sett.php';
require_once __DIR__ . '/../../auth/middleware_login.php';

try {
    // ... (Auth Logic sama) ...
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    if (!preg_match('/^Bearer\s(\S+)$/', $authHeader, $matches)) {
        throw new Exception('Token tidak ditemukan');
    }
    $verif = verify_token($matches[1]);
    if (!$verif)
        throw new Exception('Token tidak valid');
    $user_login = $verif->id ?? $verif->kode ?? 'SYSTEM';

    $json = file_get_contents('php://input');
    $input = json_decode($json, true);

    // UBAH: Ambil no_faktur, bukan ID
    $no_faktur = isset($input['no_faktur']) ? trim($input['no_faktur']) : '';

    if (empty($no_faktur))
        throw new Exception("Nomor Faktur tidak valid");

    // Query pakai no_faktur
    $query = "UPDATE serah_terima_nota SET visibilitas = 'Nonaktif', dihapus_pada = NOW(), dihapus_oleh = ? WHERE no_faktur = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $user_login, $no_faktur);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(['success' => true, 'message' => "Data berhasil dihapus."]);
        } else {
            throw new Exception("Data tidak ditemukan atau sudah dihapus.");
        }
    } else {
        throw new Exception("Gagal menghapus data.");
    }

} catch (Exception $e) {
    http_response_code(200);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} finally {
    if (isset($conn))
        $conn->close();
}
?>