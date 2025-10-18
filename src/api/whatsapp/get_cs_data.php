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

        try {
            $stmt_update = $conn->prepare("UPDATE wa_pesan SET status_baca = 1 WHERE percakapan_id = ? AND pengirim = 'user' AND status_baca = 0");
            $stmt_update->bind_param("i", $id);
            $stmt_update->execute();
            $stmt_update->close();

            $totalUnread = 0;
            $sql_count = "SELECT COUNT(id) AS total_unread FROM wa_pesan WHERE pengirim = 'user' AND status_baca = 0";
            $result_count = $conn->query($sql_count);
            if ($result_count) {
                $row_count = $result_count->fetch_assoc();
                $totalUnread = (int)$row_count['total_unread'];
            }
            
            $ws_url = 'http://127.0.0.1:8081/notify';
            $payload = json_encode([
                'event' => 'unread_count_update',
                'total_unread_count' => $totalUnread
            ]);
            
            $ch = curl_init($ws_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($payload)
            ]);
            curl_setopt($ch, CURLOPT_TIMEOUT_MS, 100);
            curl_setopt($ch, CURLOPT_NOSIGNAL, 1);
            curl_exec($ch);
            curl_close($ch);
        } catch (Exception $e) {
        }

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
            SELECT
                p.id,
                p.nomor_telepon,
                p.status_percakapan,
                p.terakhir_interaksi_pada,
                COUNT(m.id) AS jumlah_belum_terbaca
            FROM
                wa_percakapan p
            LEFT JOIN
                wa_pesan m ON p.id = m.percakapan_id AND m.pengirim = 'user' AND m.status_baca = 0
        ";

        $whereClause = "";
        if ($filter === 'live_chat') {
            $whereClause = " WHERE p.status_percakapan = 'live_chat'";
        } elseif ($filter === 'umum') {
            $whereClause = " WHERE p.status_percakapan IN ('open', 'closed')";
        }

        $sql .= $whereClause;

        $sql .= " GROUP BY p.id, p.nomor_telepon, p.status_percakapan, p.terakhir_interaksi_pada ";

        $sql .= "
            ORDER BY
                CASE p.status_percakapan
                    WHEN 'live_chat' THEN 1
                    WHEN 'open' THEN 2
                    ELSE 3
                END,
                p.terakhir_interaksi_pada DESC
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