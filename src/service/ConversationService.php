<?php
class ConversationService
{
    public $conn;
    private $logger;
    public function __construct($dbConnection, $logger)
    {
        $this->conn = $dbConnection;
        $this->logger = $logger;
    }
    public function getOrCreateConversation($phoneNumber, $profileName = 'Pelanggan')
    {
        $stmt = $this->conn->prepare("SELECT * FROM wa_percakapan WHERE nomor_telepon = ?");
        $stmt->bind_param("s", $phoneNumber);
        $stmt->execute();
        $result = $stmt->get_result();
        $conversation = $result->fetch_assoc();
        $stmt->close();
        if ($conversation) {
            if ($conversation['nama_profil'] !== $profileName && $profileName !== 'Pelanggan') {
                $stmt_update = $this->conn->prepare("UPDATE wa_percakapan SET nama_profil = ? WHERE nomor_telepon = ?");
                $stmt_update->bind_param("ss", $profileName, $phoneNumber);
                $stmt_update->execute();
                $stmt_update->close();
                $conversation['nama_profil'] = $profileName;
            }
            if ($conversation['status_percakapan'] === 'open') {
                $this->updateInteractionTimestamp($phoneNumber);
            }
            return $conversation;
        } else {
            $stmt = $this->conn->prepare("INSERT INTO wa_percakapan (nomor_telepon, nama_profil, status_percakapan) VALUES (?, ?, 'closed')");
            $stmt->bind_param("ss", $phoneNumber, $profileName);
            $stmt->execute();
            $stmt->close();
            return $this->getOrCreateConversation($phoneNumber);
        }
    }
    public function openConversation($phoneNumber)
    {
        $stmt = $this->conn->prepare("UPDATE wa_percakapan SET status_percakapan = 'open', terakhir_interaksi_pada = NOW(), menu_utama_terkirim = 0 WHERE nomor_telepon = ?");
        $stmt->bind_param("s", $phoneNumber);
        $stmt->execute();
        $stmt->close();
    }
    public function setMenuSent($phoneNumber)
    {
        $stmt = $this->conn->prepare("UPDATE wa_percakapan SET menu_utama_terkirim = 1 WHERE nomor_telepon = ?");
        $stmt->bind_param("s", $phoneNumber);
        $stmt->execute();
        $stmt->close();
    }
    private function updateInteractionTimestamp($phoneNumber)
    {
        $stmt = $this->conn->prepare("UPDATE wa_percakapan SET terakhir_interaksi_pada = NOW() WHERE nomor_telepon = ?");
        $stmt->bind_param("s", $phoneNumber);
        $stmt->execute();
        $stmt->close();
    }
    public function startLiveChat($phoneNumber)
    {
        $stmt = $this->conn->prepare("UPDATE wa_percakapan SET status_percakapan = 'live_chat', terakhir_interaksi_pada = NOW() WHERE nomor_telepon = ?");
        $stmt->bind_param("s", $phoneNumber);
        $stmt->execute();
        $stmt->close();
    }
    public function saveMessage($conversationId, $senderType, $messageType, $messageContent, $wamid = null, $isBot = 0)
    {
        $statusPengiriman = ($senderType === 'admin' && $wamid !== null) ? 'sent' : null;

        if ($senderType === 'user') {
            $isBot = 0;
        }

        $stmt = $this->conn->prepare("INSERT INTO wa_pesan (percakapan_id, pengirim, tipe_pesan, isi_pesan, wamid, status_pengiriman, dikirim_oleh_bot, timestamp) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("isssssi", $conversationId, $senderType, $messageType, $messageContent, $wamid, $statusPengiriman, $isBot);

        $stmt->execute();
        $newId = $stmt->insert_id;
        $stmt->close();

        if ($newId > 0) {
            try {
                $stmt_update = $this->conn->prepare("UPDATE wa_percakapan SET terakhir_interaksi_pada = NOW() WHERE id = ?");
                $stmt_update->bind_param("i", $conversationId);
                $stmt_update->execute();
                $stmt_update->close();
            } catch (Exception $e) {
                $this->logger->error("Gagal update terakhir_interaksi_pada for convo ID {$conversationId}: " . $e->getMessage());
            }
        }

        $stmt = $this->conn->prepare("SELECT id, pengirim, isi_pesan, tipe_pesan, timestamp, status_baca, wamid, status_pengiriman, dikirim_oleh_bot FROM wa_pesan WHERE id = ?");
        $stmt->bind_param("i", $newId);
        $stmt->execute();
        $result = $stmt->get_result();
        $newMessage = $result->fetch_assoc();
        $stmt->close();
        return $newMessage;
    }
    public function closeConversation($phoneNumber)
    {
        $stmt = $this->conn->prepare("UPDATE wa_percakapan SET status_percakapan = 'closed', menu_utama_terkirim = 0 WHERE nomor_telepon = ?");
        $stmt->bind_param("s", $phoneNumber);
        $stmt->execute();
        $stmt->close();
    }
    public function getTotalUnreadCount()
    {
        $sql = "SELECT COUNT(id) AS total_unread FROM wa_pesan WHERE pengirim = 'user' AND status_baca = 0";
        $result = $this->conn->query($sql);
        if ($result) {
            $row = $result->fetch_assoc();
            return (int) $row['total_unread'];
        }
        return 0;
    }
    public function shouldSendGiveaway($phoneNumber)
    {
        $stmt = $this->conn->prepare("SELECT terakhir_giveaway_tgl FROM wa_percakapan WHERE nomor_telepon = ?");
        $stmt->bind_param("s", $phoneNumber);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        $stmt->close();

        $today = date('Y-m-d');

        if (!$data || is_null($data['terakhir_giveaway_tgl']) || $data['terakhir_giveaway_tgl'] < $today) {
            return true;
        }

        return false;
    }

    public function markGiveawaySent($phoneNumber)
    {
        $today = date('Y-m-d');
        $stmt = $this->conn->prepare("UPDATE wa_percakapan SET terakhir_giveaway_tgl = ? WHERE nomor_telepon = ?");
        $stmt->bind_param("ss", $today, $phoneNumber);
        $stmt->execute();
        $stmt->close();
    }
}