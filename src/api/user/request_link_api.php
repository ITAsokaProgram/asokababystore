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
    $user = getAuthenticatedUser();
    $userId = $user->id;

    $data = json_decode(file_get_contents('php://input'), true);
    $noHpBaru = $data['no_hp'] ?? '';

    if (!preg_match('/^08\d{8,11}$/', $noHpBaru)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Format nomor HP tidak valid. Contoh: 081234567890.']);
        exit;
    }

    $stmt = $conn->prepare("SELECT id_user FROM user_asoka WHERE no_hp = ? AND id_user != ?");
    $stmt->bind_param("si", $noHpBaru, $userId);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        http_response_code(409);
        echo json_encode(['success' => false, 'message' => 'Nomor HP ini sudah digunakan oleh akun lain.']);
        exit;
    }
    $stmt->close();
    
    $tokenKirimUser = bin2hex(random_bytes(32));
    $kedaluwarsa = (new DateTime())->modify('+15 minutes')->format('Y-m-d H:i:s');

    $sql = "INSERT INTO verifikasi_nomor_hp (id_user, nomor_hp_baru, token_kirim_user, kedaluwarsa_pada) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isss", $userId, $noHpBaru, $tokenKirimUser, $kedaluwarsa);
    
    if (!$stmt->execute()) {
        throw new Exception("Gagal menyimpan data verifikasi: " . $stmt->error);
    }
    $stmt->close();

    $linkInternal = "https://asokababystore.com/verifikasi-wa?token=" . $tokenKirimUser;

    $nomorTujuan = "6287722752786";
    $pesanWA = "Halo Asoka, saya ingin verifikasi penggantian nomor HP. Link verifikasi saya: " . $linkInternal;
    $whatsappUrl = "https://wa.me/" . $nomorTujuan . "?text=" . urlencode($pesanWA);

    http_response_code(200);
    echo json_encode([
        'success' => true, 
        'message' => 'Link verifikasi telah dibuat.',
        'data' => [
            'whatsapp_url' => $whatsappUrl
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan pada server.', 'error' => $e->getMessage()]);
}
?>