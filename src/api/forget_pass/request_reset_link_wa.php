<?php
require_once __DIR__ . "/../../../aa_kon_sett.php";
header("Content-Type:application/json");
$env = parse_ini_file(__DIR__ . '/../../../.env');


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Metode yang diizinkan hanya POST']);
    exit;
}

try {
    $data = json_decode(file_get_contents('php://input'), true);
    $noHp = $data['no_hp'] ?? '';

    if (!preg_match('/^08\d{8,11}$/', $noHp)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Format nomor HP tidak valid.']);
        exit;
    }

    $stmt = $conn->prepare("SELECT id_user FROM user_asoka WHERE no_hp = ?");
    $stmt->bind_param("s", $noHp);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Nomor HP ini tidak terdaftar di akun manapun.']);
        exit;
    }
    $stmt->close();

    $token = "resetpw_" . bin2hex(random_bytes(30)); 
    $createdAt = date('Y-m-d H:i:s');
    $expiredAt = date('Y-m-d H:i:s', strtotime('+15 minutes'));

    $stmtDelete = $conn->prepare("DELETE FROM reset_token WHERE no_hp = ?");
    $stmtDelete->bind_param("s", $noHp);
    $stmtDelete->execute();
    $stmtDelete->close();
    
    $stmtInsert = $conn->prepare("INSERT INTO reset_token (no_hp, token, dibuat_tgl, kadaluarsa, used) VALUES (?, ?, ?, ?, 0)");
    $stmtInsert->bind_param("ssss", $noHp, $token, $createdAt, $expiredAt);
    if (!$stmtInsert->execute()) {
        throw new Exception("Gagal menyimpan token reset: " . $stmtInsert->error);
    }
    $stmtInsert->close();

    $nomorTujuan = $env['WHATSAPP_NOMOR_TUJUAN'];
    $pesanWA = "Halo Asoka, saya ingin reset password. Token saya: " . $token;
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