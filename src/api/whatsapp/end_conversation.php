<?php

require_once __DIR__ . '/../../../aa_kon_sett.php';
require_once __DIR__ . '/../../auth/middleware_login.php';
require_once __DIR__ . '/../../helpers/whatsapp_helper_link.php'; 

header('Content-Type: application/json');

try {
    $headers = getallheaders();
    if (!isset($headers['Authorization']) || !preg_match('/^Bearer\s(\S+)$/', $headers['Authorization'], $matches)) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Token tidak ditemukan atau format salah.']);
        exit;
    }
    
    $token = $matches[1];
    $decoded = verify_token($token);

    $isTokenValidAdmin = (is_object($decoded) && isset($decoded->kode));

    if (!$isTokenValidAdmin) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Token tidak valid atau bukan token admin.']);
        exit;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Token validation error: ' . $e->getMessage()]);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['conversation_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Conversation ID tidak disertakan.']);
    exit;
}

$conversationId = filter_var($data['conversation_id'], FILTER_VALIDATE_INT);

if ($conversationId === false) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Conversation ID tidak valid.']);
    exit;
}

try {
    $stmt_get = $conn->prepare("SELECT nomor_telepon FROM percakapan_whatsapp WHERE id = ? AND status_percakapan = 'live_chat'");
    $stmt_get->bind_param("i", $conversationId);
    $stmt_get->execute();
    $result = $stmt_get->get_result();
    $conversation = $result->fetch_assoc();
    $stmt_get->close();

    if ($conversation) {
        $stmt_update = $conn->prepare("UPDATE percakapan_whatsapp SET status_percakapan = 'open', menu_utama_terkirim = 0 WHERE id = ?");
        $stmt_update->bind_param("i", $conversationId);
        $stmt_update->execute();

        if ($stmt_update->affected_rows > 0) {
            $nomorTelepon = $conversation['nomor_telepon'];
            $pesanTerimaKasih = "Terima kasih telah menghubungi Asoka Baby Store. Jika Ayah/Bunda memiliki pertanyaan lain, jangan ragu untuk menghubungi kami kembali. 😊";
            
            kirimPesanTeks($nomorTelepon, $pesanTerimaKasih);

            echo json_encode(['success' => true, 'message' => 'Percakapan berhasil diakhiri.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal memperbarui status percakapan.']);
        }
        $stmt_update->close();

    } else {
        echo json_encode(['success' => false, 'message' => 'Percakapan tidak ditemukan atau statusnya bukan live chat.']);
    }
    
    $conn->close();

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>