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

try {
    if (isset($_GET['conversation_id'])) {
        $id = filter_var($_GET['conversation_id'], FILTER_VALIDATE_INT);
        if ($id === false) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid Conversation ID.']);
            exit;
        }

        // BARU: Update status_baca untuk pesan dari 'user' menjadi 1 (terbaca)
        try {
            $stmt_update = $conn->prepare("UPDATE wa_pesan SET status_baca = 1 WHERE percakapan_id = ? AND pengirim = 'user' AND status_baca = 0");
            $stmt_update->bind_param("i", $id);
            $stmt_update->execute();
            $stmt_update->close();
        } catch (Exception $e) {
            // Abaikan error jika gagal update, proses select tetap berjalan
        }
        // AKHIR BARU

        // Ambil detail percakapan
        $stmt_convo = $conn->prepare("SELECT id, nomor_telepon, status_percakapan FROM wa_percakapan WHERE id = ?");
        $stmt_convo->bind_param("i", $id);
        $stmt_convo->execute();
        $result_convo = $stmt_convo->get_result();
        $conversation_details = $result_convo->fetch_assoc();
        $stmt_convo->close();

        if (!$conversation_details) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Conversation not found.']);
            exit;
        }

        // DIUBAH: Tambahkan `status_baca`
        $stmt_msgs = $conn->prepare("SELECT pengirim, isi_pesan, tipe_pesan, timestamp, status_baca FROM wa_pesan WHERE percakapan_id = ? ORDER BY timestamp ASC");
        $stmt_msgs->bind_param("i", $id);
        $stmt_msgs->execute();
        $result_msgs = $stmt_msgs->get_result();
        $messages = $result_msgs->fetch_all(MYSQLI_ASSOC);
        $stmt_msgs->close();

        $data = [
            'details' => $conversation_details,
            'messages' => $messages
        ];
    } else {
        $filter = $_GET['filter'] ?? 'semua';
        
        $sql = "
            SELECT id, nomor_telepon, status_percakapan, terakhir_interaksi_pada
            FROM wa_percakapan
        ";

        $whereClause = "";
        if ($filter === 'live_chat') {
            $whereClause = " WHERE status_percakapan = 'live_chat'";
        } elseif ($filter === 'umum') {
            $whereClause = " WHERE status_percakapan IN ('open', 'closed')";
        }

        $sql .= $whereClause;

        $sql .= "
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