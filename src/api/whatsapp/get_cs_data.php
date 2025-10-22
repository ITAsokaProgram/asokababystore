<?php
require_once __DIR__ . '/../../../aa_kon_sett.php';
require_once __DIR__ . '/../../auth/middleware_login.php';
require_once __DIR__ . '/../../utils/Logger.php';

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
    $logger = new AppLogger('whatsapp_dashboard');
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

        $stmt_convo = $conn->prepare("SELECT id, nomor_telepon, nama_profil, nama_display, status_percakapan FROM wa_percakapan WHERE id = ?");
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

        
        $stmt_labels = $conn->prepare("
            SELECT l.id, l.nama_label, l.warna
            FROM wa_labels l
            JOIN wa_percakapan_labels pl ON l.id = pl.label_id
            WHERE pl.percakapan_id = ?
            ORDER BY l.nama_label
        ");
        $stmt_labels->bind_param("i", $id);
        $stmt_labels->execute();
        $result_labels = $stmt_labels->get_result();
        $labels = $result_labels->fetch_all(MYSQLI_ASSOC);
        $stmt_labels->close();

        $data = [
            'details' => $conversation_details,
            'messages' => $messages,
            'labels' => $labels
        ];
    } else {
        
        $filter = $_GET['filter'] ?? 'semua';
    
        $live_chat_count = 0;
        $umum_count = 0;

        try {
            $sql_counts = "
                SELECT
                    p.status_percakapan,
                    COUNT(DISTINCT m.percakapan_id) AS unread_convo_count
                FROM wa_pesan m
                JOIN wa_percakapan p ON m.percakapan_id = p.id
                WHERE m.pengirim = 'user' AND m.status_baca = 0
                GROUP BY p.status_percakapan
            ";
            
            $stmt_counts = $conn->prepare($sql_counts);
            if ($stmt_counts === false) {
                throw new Exception("Prepare statement (sql_counts) failed: " . $conn->error);
            }
            
            $stmt_counts->execute();
            $counts_result = $stmt_counts->get_result();
            
            while ($row = $counts_result->fetch_assoc()) {
                if ($row['status_percakapan'] == 'live_chat') {
                    $live_chat_count = (int)$row['unread_convo_count'];
                } else if (in_array($row['status_percakapan'], ['open', 'closed'])) {
                    $umum_count += (int)$row['unread_convo_count'];
                }
            }
            $stmt_counts->close();

        } catch (Exception $e) {
            $logger->error("Error calculating unread counts: " . $e->getMessage());
        }
        
        
        $sql = "
            SELECT
                p.id,
                p.nomor_telepon,
                p.nama_profil,
                p.nama_display,
                p.status_percakapan,
                p.terakhir_interaksi_pada,
                COUNT(DISTINCT m.id) AS jumlah_belum_terbaca,
                GROUP_CONCAT(DISTINCT CONCAT(l.id, ':', l.nama_label, ':', l.warna) SEPARATOR ';') AS labels_concat
            FROM
                wa_percakapan p
            LEFT JOIN
                wa_pesan m ON p.id = m.percakapan_id AND m.pengirim = 'user' AND m.status_baca = 0
            LEFT JOIN
                wa_percakapan_labels pl ON p.id = pl.percakapan_id
            LEFT JOIN
                wa_labels l ON pl.label_id = l.id
        ";

        $whereClause = "";
        if ($filter === 'live_chat') {
            $whereClause = " WHERE p.status_percakapan = 'live_chat'";
        } elseif ($filter === 'umum') {
            $whereClause = " WHERE p.status_percakapan IN ('open', 'closed')";
        }

        $sql .= $whereClause;
        $sql .= "
            GROUP BY
                p.id, p.nomor_telepon, p.nama_profil, p.nama_display, p.status_percakapan, p.terakhir_interaksi_pada
            ORDER BY
                p.terakhir_interaksi_pada DESC
        ";
        
        
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        $conversations = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        
        
        foreach ($conversations as $key => $convo) {
            $labels = [];
            if (!empty($convo['labels_concat'])) {
                $pairs = explode(';', $convo['labels_concat']);
                foreach ($pairs as $pair) {
                    
                    if (substr_count($pair, ':') >= 2) {
                        list($id, $nama, $warna) = explode(':', $pair, 3);
                        $labels[] = ['id' => $id, 'nama_label' => $nama, 'warna' => $warna];
                    }
                }
            }
            $conversations[$key]['labels'] = $labels;
            unset($conversations[$key]['labels_concat']);
        }
        
        $data = [
            'conversations' => $conversations,
            'unread_counts' => [
                'live_chat' => (int)$live_chat_count,
                'umum' => (int)$umum_count
            ]
        ];
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