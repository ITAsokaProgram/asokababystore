<?php
require_once __DIR__ . "/../../../aa_kon_sett.php";
require_once __DIR__ . "/../../auth/middleware_login.php";

header("Content-Type:application/json");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Metode yang diizinkan hanya POST']);
    exit;
}

try {
    // Autentikasi user dari cookie/token
    $user = getAuthenticatedUser();
    $userId = $user->id;

    $data = json_decode(file_get_contents('php://input'), true);
    $noHp = $data['no_hp'] ?? '';

    // Validasi format dasar
    if (!preg_match('/^08\d{8,11}$/', $noHp)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Format nomor HP tidak valid.']);
        exit;
    }

    // Cek apakah nomor HP baru sudah dipakai oleh PENGGUNA LAIN
    $stmt = $conn->prepare("SELECT id_user FROM user_asoka WHERE no_hp = ? AND id_user != ?");
    $stmt->bind_param("si", $noHp, $userId);
    $stmt->execute();
    $isExists = $stmt->get_result()->num_rows > 0;
    $stmt->close();

    // Kirim response
    http_response_code(200);
    echo json_encode(['exists' => $isExists]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan pada server.', 'error' => $e->getMessage()]);
}

?>