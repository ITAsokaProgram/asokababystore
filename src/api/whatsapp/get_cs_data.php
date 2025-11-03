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
    if (isset($_GET['counts_only'])) {
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
            $stmt_counts->execute();
            $counts_result = $stmt_counts->get_result();

            while ($row = $counts_result->fetch_assoc()) {
                if ($row['status_percakapan'] == 'live_chat') {
                    $live_chat_count = (int) $row['unread_convo_count'];
                } else if (in_array($row['status_percakapan'], ['open', 'closed'])) {
                    $umum_count += (int) $row['unread_convo_count'];
                }
            }
            $stmt_counts->close();
        } catch (Exception $e) {
            $logger->error("Error calculating unread counts (counts_only): " . $e->getMessage());
        }

        echo json_encode([
            'unread_counts' => [
                'live_chat' => (int) $live_chat_count,
                'umum' => (int) $umum_count
            ]
        ]);
        $conn->close();
        exit;
    }
    if (isset($_GET['conversation_id'])) {
        $id = filter_var($_GET['conversation_id'], FILTER_VALIDATE_INT);
        if ($id === false) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid Conversation ID.']);
            exit;
        }

        $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        if ($page < 1)
            $page = 1;

        if ($page == 1) {
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
                    $totalUnread = (int) $row_count['total_unread'];
                }

                $live_chat_count_ws = 0;
                $umum_count_ws = 0;
                try {
                    $sql_counts_ws = "
                        SELECT
                            p.status_percakapan,
                            COUNT(DISTINCT m.percakapan_id) AS unread_convo_count
                        FROM wa_pesan m
                        JOIN wa_percakapan p ON m.percakapan_id = p.id
                        WHERE m.pengirim = 'user' AND m.status_baca = 0
                        GROUP BY p.status_percakapan
                    ";
                    $stmt_counts_ws = $conn->prepare($sql_counts_ws);
                    $stmt_counts_ws->execute();
                    $counts_result_ws = $stmt_counts_ws->get_result();
                    while ($row_ws = $counts_result_ws->fetch_assoc()) {
                        if ($row_ws['status_percakapan'] == 'live_chat') {
                            $live_chat_count_ws = (int) $row_ws['unread_convo_count'];
                        } else if (in_array($row_ws['status_percakapan'], ['open', 'closed'])) {
                            $umum_count_ws += (int) $row_ws['unread_convo_count'];
                        }
                    }
                    $stmt_counts_ws->close();
                } catch (Exception $e) {
                }

                $ws_url = 'http://127.0.0.1:8081/notify';
                $payload = json_encode([
                    'event' => 'unread_count_update',
                    'total_unread_count' => $totalUnread,
                    'unread_counts' => [
                        'live_chat' => $live_chat_count_ws,
                        'umum' => $umum_count_ws
                    ]
                ]);
                try {
                    $ch = curl_init($ws_url);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, [
                        'Content-Type: application/json',
                        'Content-Length: ' . strlen($payload)
                    ]);

                    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, 50);
                    curl_setopt($ch, CURLOPT_TIMEOUT_MS, 100);

                    curl_setopt($ch, CURLOPT_NOSIGNAL, 1);
                    curl_exec($ch);
                    if (curl_errno($ch)) {
                        $logger->warning("cURL error to WS bridge: " . curl_error($ch));
                    }
                    curl_close($ch);
                } catch (Exception $e) {
                    $logger->error("Failed to notify WS bridge: " . $e->getMessage());
                }


            } catch (Exception $e) {
                $logger->error("Error during page=1 read/notify logic: " . $e->getMessage());
            }
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

        $limit = 40;
        $offset = ($page - 1) * $limit;

        $stmt_count = $conn->prepare("SELECT COUNT(id) AS total FROM wa_pesan WHERE percakapan_id = ?");
        $stmt_count->bind_param("i", $id);
        $stmt_count->execute();
        $total_messages = $stmt_count->get_result()->fetch_assoc()['total'];
        $total_pages = ceil($total_messages / $limit);
        $stmt_count->close();

        $stmt_msgs = $conn->prepare("
            SELECT * FROM (
                SELECT id, pengirim, isi_pesan, tipe_pesan, timestamp, status_baca, wamid, status_pengiriman
                FROM wa_pesan
                WHERE percakapan_id = ?
                ORDER BY timestamp DESC, id DESC
                LIMIT ? OFFSET ?
            ) AS paged_messages
            ORDER BY timestamp ASC, id ASC
        ");
        $stmt_msgs->bind_param("iii", $id, $limit, $offset);
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
            'labels' => $labels,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => (int) $total_pages,
                'has_more' => $page < $total_pages
            ]
        ];
    } else {

        $filter = $_GET['filter'] ?? 'semua';
        $search = $_GET['search'] ?? '';
        $live_chat_count = 0;
        $umum_count = 0;

        $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        if ($page < 1)
            $page = 1;
        $limit = 12;
        $offset = ($page - 1) * $limit;

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
                    $live_chat_count = (int) $row['unread_convo_count'];
                } else if (in_array($row['status_percakapan'], ['open', 'closed'])) {
                    $umum_count += (int) $row['unread_convo_count'];
                }
            }
            $stmt_counts->close();

        } catch (Exception $e) {
            $logger->error("Error calculating unread counts: " . $e->getMessage());
        }

        // ini ga bener
        // $sql = "
        //     SELECT
        //         p.id,
        //         p.nomor_telepon,
        //         p.nama_profil,
        //         p.nama_display,
        //         p.status_percakapan,

        //         COALESCE(
        //             (SELECT MAX(m.timestamp) FROM wa_pesan m WHERE m.percakapan_id = p.id),
        //             p.terakhir_interaksi_pada
        //         ) AS urutan_interaksi,

        //         (SELECT COUNT(m.id)
        //          FROM wa_pesan m
        //          WHERE m.percakapan_id = p.id AND m.pengirim = 'user' AND m.status_baca = 0
        //         ) AS jumlah_belum_terbaca,

        //         (SELECT GROUP_CONCAT(DISTINCT CONCAT(l.id, ':', l.nama_label, ':', l.warna) SEPARATOR ';')
        //          FROM wa_percakapan_labels pl
        //          JOIN wa_labels l ON pl.label_id = l.id
        //          WHERE pl.percakapan_id = p.id
        //         ) AS labels_concat

        //     FROM
        //         wa_percakapan p
        // ";
        $sql = "
            SELECT
                p.id,
                p.nomor_telepon,
                p.nama_profil,
                p.nama_display,
                p.status_percakapan,
                
                COALESCE(
                    (SELECT MAX(m.timestamp) FROM wa_pesan m WHERE m.percakapan_id = p.id),
                    p.terakhir_interaksi_pada
                ) AS urutan_interaksi,
                
                (SELECT COUNT(m.id)
                 FROM wa_pesan m
                 WHERE m.percakapan_id = p.id AND m.pengirim = 'user' AND m.status_baca = 0
                ) AS jumlah_belum_terbaca,

                (SELECT m.isi_pesan 
                 FROM wa_pesan m 
                 WHERE m.percakapan_id = p.id 
                 ORDER BY m.timestamp DESC, m.id DESC 
                 LIMIT 1
                ) AS latest_message_content,
                
                (SELECT m.tipe_pesan 
                 FROM wa_pesan m 
                 WHERE m.percakapan_id = p.id 
                 ORDER BY m.timestamp DESC, m.id DESC 
                 LIMIT 1
                ) AS latest_message_type,
                
                (SELECT GROUP_CONCAT(DISTINCT CONCAT(l.id, ':', l.nama_label, ':', l.warna) SEPARATOR ';')
                 FROM wa_percakapan_labels pl
                 JOIN wa_labels l ON pl.label_id = l.id
                 WHERE pl.percakapan_id = p.id
                ) AS labels_concat
                
            FROM
                wa_percakapan p
        ";


        $whereConditions = [];
        $params = [];
        $types = '';

        if ($filter === 'live_chat') {
            $whereConditions[] = "p.status_percakapan = ?";
            $params[] = 'live_chat';
            $types .= 's';
        } elseif ($filter === 'umum') {
            $whereConditions[] = "p.status_percakapan IN ('open', 'closed')";
        }

        if (!empty($search)) {
            $searchTermLike = "%" . $search . "%";

            $searchConditions = ["p.nama_display LIKE ?", "p.nomor_telepon LIKE ?"];
            $params[] = $searchTermLike;
            $params[] = $searchTermLike;
            $types .= 'ss';

            if (ctype_digit($search)) {

                if (strpos($search, '0') === 0) {
                    $normalizedSearch = "62" . substr($search, 1);
                    $normalizedSearchLike = "%" . $normalizedSearch . "%";

                    $searchConditions[] = "p.nomor_telepon LIKE ?";
                    $params[] = $normalizedSearchLike;
                    $types .= 's';
                } else if (strpos($search, '62') === 0) {
                    $normalizedSearch = "0" . substr($search, 2);
                    $normalizedSearchLike = "%" . $normalizedSearch . "%";

                    $searchConditions[] = "p.nomor_telepon LIKE ?";
                    $params[] = $normalizedSearchLike;
                    $types .= 's';
                }
            }

            $whereConditions[] = "(" . implode(' OR ', $searchConditions) . ")";
        }

        $whereClause = "";
        if (!empty($whereConditions)) {
            $whereClause = " WHERE " . implode(' AND ', $whereConditions);
        }

        $sql_count_total = "SELECT COUNT(DISTINCT p.id) AS total_conversations FROM wa_percakapan p";
        $sql_count_total .= $whereClause;

        $stmt_count_total = $conn->prepare($sql_count_total);
        if (!empty($params)) {
            $stmt_count_total->bind_param($types, ...$params);
        }
        $stmt_count_total->execute();
        $total_conversations = $stmt_count_total->get_result()->fetch_assoc()['total_conversations'];
        $total_pages = ceil($total_conversations / $limit);
        $stmt_count_total->close();

        $sql .= $whereClause;
        $sql .= "
            ORDER BY
                urutan_interaksi DESC
        ";

        $sql .= " LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        $types .= 'ii';


        $stmt = $conn->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
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
                'live_chat' => (int) $live_chat_count,
                'umum' => (int) $umum_count
            ],
            'pagination' => [
                'current_page' => $page,
                'total_pages' => (int) $total_pages,
                'has_more' => $page < $total_pages
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