<?php
require_once __DIR__ . '/../../../aa_kon_sett.php';
require_once __DIR__ . '/../../auth/middleware_login.php';

header('Content-Type: application/json');
$decoded = authenticate_request();


$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['conversation_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Conversation ID tidak disertakan.']);
    exit;
}

$conversationId = filter_var($data['conversation_id'], FILTER_VALIDATE_INT);
$namaDisplay = $data['nama_display'] ?? null;

if ($namaDisplay !== null) {
    $namaDisplay = trim(htmlspecialchars($namaDisplay, ENT_QUOTES, 'UTF-8'));
    if (empty($namaDisplay)) {
        $namaDisplay = null;
    }
}

if ($conversationId === false) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Conversation ID tidak valid.']);
    exit;
}

try {
    $stmt = $conn->prepare("UPDATE wa_percakapan SET nama_display = ? WHERE id = ?");
    $stmt->bind_param("si", $namaDisplay, $conversationId);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'Nama tampilan berhasil diperbarui.']);
    } else {
        echo json_encode(['success' => true, 'message' => 'Tidak ada perubahan atau percakapan ditemukan.']);
    }

    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>