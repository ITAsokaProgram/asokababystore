<?php
require_once __DIR__ . '/../../../aa_kon_sett.php';
require_once __DIR__ . '/../../auth/middleware_login.php'; 

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

    
    $isTokenValidAdmin = false;
    if (is_object($decoded) && isset($decoded->kode)) {
        
        $isTokenValidAdmin = true;
    } elseif (is_array($decoded) && isset($decoded['status']) && $decoded['status'] === 'error') {
        
        $isTokenValidAdmin = false;
    }

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


try {
    
    if (isset($_GET['conversation_id'])) {
        $id = filter_var($_GET['conversation_id'], FILTER_VALIDATE_INT);
        if ($id === false) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid Conversation ID.']);
            exit;
        }

        $sql = "SELECT pengirim, isi_pesan, timestamp FROM pesan_whatsapp WHERE percakapan_id = ? ORDER BY timestamp ASC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    } else {
        
        $sql = "
            SELECT id, nomor_telepon, status_percakapan, terakhir_interaksi_pada
            FROM percakapan_whatsapp
            ORDER BY
                CASE status_percakapan
                    WHEN 'live_chat' THEN 1
                    WHEN 'open' THEN 2
                    ELSE 3
                END,
                terakhir_interaksi_pada DESC
        ";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    }
    
    
    echo json_encode($data);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>