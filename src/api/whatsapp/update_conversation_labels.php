<?php

require_once __DIR__ . '/../../../aa_kon_sett.php';
require_once __DIR__ . '/../../auth/middleware_login.php';
require_once __DIR__ . '/../../utils/Logger.php';

$env = parse_ini_file(__DIR__ . '/../../../.env');

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
    $isTokenValidAdmin = is_object($decoded) && isset($decoded->kode);

    if (!$isTokenValidAdmin) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Token tidak valid.']);
        exit;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Token validation error: ' . $e->getMessage()]);
    exit;
}
$logger = new AppLogger('update');


$data = json_decode(file_get_contents('php://input'), true);
$logger->debug("Request body: " . json_encode($data));

if (!$data || !isset($data['conversation_id']) || !isset($data['label_ids']) || !is_array($data['label_ids'])) {
    http_response_code(400);
    $logger->warning("Invalid input: " . json_encode($data));
    echo json_encode(['success' => false, 'message' => 'Input tidak valid. Membutuhkan conversation_id dan array label_ids.']);
    exit;
}

$conversationId = filter_var($data['conversation_id'], FILTER_VALIDATE_INT);
$labelIds = array_map('intval', $data['label_ids']); 
$labelIds = array_filter($labelIds, function($id) {
    return $id > 0;
});
if ($conversationId === false) {
    http_response_code(400);
    $logger->warning("Invalid Conversation ID: " . $data['conversation_id']);
    echo json_encode(['success' => false, 'message' => 'Conversation ID tidak valid.']);
    exit;
}


try {
    $logger->info("Starting transaction for conversation ID: $conversationId");
    $conn->begin_transaction();

    
    $stmt_nomor = $conn->prepare("SELECT nomor_telepon FROM wa_percakapan WHERE id = ?");
    $stmt_nomor->bind_param("i", $conversationId);
    $stmt_nomor->execute();
    $result_nomor = $stmt_nomor->get_result();
    $convo = $result_nomor->fetch_assoc();
    $stmt_nomor->close();

    if (!$convo) {
        throw new Exception("Percakapan tidak ditemukan (ID: $conversationId).");
    }
    $nomorTelepon = $convo['nomor_telepon'];
    $logger->info("Found nomor_telepon: $nomorTelepon");

    
    $stmt_delete = $conn->prepare("DELETE FROM wa_percakapan_labels WHERE percakapan_id = ?");
    $stmt_delete->bind_param("i", $conversationId);
    $stmt_delete->execute();
    $logger->info("Deleted " . $stmt_delete->affected_rows . " old labels for percakapan_id: $conversationId");
    $stmt_delete->close();

    
    if (!empty($labelIds)) {
        $stmt_insert = $conn->prepare("INSERT INTO wa_percakapan_labels (percakapan_id, label_id, nomor_telepon) VALUES (?, ?, ?)");
        
        foreach ($labelIds as $labelId) {
            $stmt_insert->bind_param("iis", $conversationId, $labelId, $nomorTelepon);
            $stmt_insert->execute();
        }
        
        // [FIX 2] Pindahkan 'close()' ke LUAR loop
        $stmt_insert->close(); 
        $logger->info("Inserted new labels: " . json_encode($labelIds));
    } else {
        $logger->info("No new labels to insert (label_ids is empty).");
    }

    $conn->commit();
    $logger->success("Transaction committed for conversation ID: $conversationId");

    
    $stmt_new_labels = $conn->prepare("
        SELECT l.id, l.nama_label, l.warna
        FROM wa_labels l
        JOIN wa_percakapan_labels pl ON l.id = pl.label_id
        WHERE pl.percakapan_id = ?
        ORDER BY l.nama_label
    ");
    $stmt_new_labels->bind_param("i", $conversationId);
    $stmt_new_labels->execute();
    $result_new_labels = $stmt_new_labels->get_result();
    $newLabels = $result_new_labels->fetch_all(MYSQLI_ASSOC);
    $stmt_new_labels->close();
    $logger->info("Fetched " . count($newLabels) . " new labels for response.");

    // [FIX 3] Ganti key 'labels' menjadi 'new_labels' agar sesuai dengan JS
    echo json_encode(['success' => true, 'message' => 'Label berhasil diperbarui.', 'new_labels' => $newLabels]);

} catch (Exception $e) {
    $logger->error("Exception caught: " . $e->getMessage() . " on line " . $e->getLine());
    $conn->rollback();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);

} finally {
    if (isset($conn)) {
        $conn->close();
        $logger->info("--- Request finished ---");
    }
}


?>