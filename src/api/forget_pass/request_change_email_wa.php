<?php
require_once __DIR__ . "/../../../aa_kon_sett.php";
header("Content-Type:application/json");
$env = parse_ini_file(__DIR__ . '/../../../.env');

try {
    $data = json_decode(file_get_contents('php://input'), true);
    $noHp = $data['no_hp'] ?? '';
    $newEmail = $data['new_email'] ?? '';

    if (!preg_match('/^08\d{8,11}$/', $noHp) || !filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Data tidak valid.']);
        exit;
    }

    $stmt = $conn->prepare("SELECT id_user FROM user_asoka WHERE no_hp = ?");
    $stmt->bind_param("s", $noHp);
    $stmt->execute();
    if ($stmt->get_result()->num_rows === 0) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Nomor HP tidak terdaftar.']);
        exit;
    }
    $stmt->close();

    $token = "resetmail_" . bin2hex(random_bytes(30));
    $expiredAt = date('Y-m-d H:i:s', strtotime('+15 minutes'));

    $stmtInsert = $conn->prepare("INSERT INTO reset_token (no_hp, token, kadaluarsa, used) VALUES (?, ?, ?, 0)");
    $stmtInsert->bind_param("sss", $noHp, $token, $expiredAt);
    $stmtInsert->execute();

    $nomorAdmin = $env['WHATSAPP_NOMOR_TUJUAN'];
    $pesanWA = "Halo Asoka, saya ingin GANTI EMAIL.\nNo HP: $noHp\nEmail Baru: $newEmail\nToken: $token";
    $whatsappUrl = "https://wa.me/" . $nomorAdmin . "?text=" . urlencode($pesanWA);

    echo json_encode(['success' => true, 'whatsapp_url' => $whatsappUrl]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}