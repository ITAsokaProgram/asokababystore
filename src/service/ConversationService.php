<?php

class ConversationService {
    private $conn;
    private $logger;

    public function __construct($dbConnection, $logger) {
        $this->conn = $dbConnection;
        $this->logger = $logger;
    }

    public function getOrCreateConversation($phoneNumber) {
        $stmt = $this->conn->prepare("SELECT * FROM wa_percakapan WHERE nomor_telepon = ?");
        $stmt->bind_param("s", $phoneNumber);
        $stmt->execute();
        $result = $stmt->get_result();
        $conversation = $result->fetch_assoc();
        $stmt->close();

        if ($conversation) {
            if ($conversation['status_percakapan'] === 'open') {
                $this->updateInteractionTimestamp($phoneNumber);
            }
            return $conversation;
        } else {
            $stmt = $this->conn->prepare("INSERT INTO wa_percakapan (nomor_telepon, status_percakapan) VALUES (?, 'closed')");
            $stmt->bind_param("s", $phoneNumber);
            $stmt->execute();
            $stmt->close();
            
            $this->logger->info("Membuat record percakapan baru untuk: " . $phoneNumber);
            return $this->getOrCreateConversation($phoneNumber);
        }
    }

    public function openConversation($phoneNumber) {
        $stmt = $this->conn->prepare("UPDATE wa_percakapan SET status_percakapan = 'open', terakhir_interaksi_pada = NOW(), menu_utama_terkirim = 0 WHERE nomor_telepon = ?");
        $stmt->bind_param("s", $phoneNumber);
        $stmt->execute();
        $stmt->close();
        $this->logger->info("Percakapan dibuka untuk: " . $phoneNumber . ". Flag menu_utama_terkirim direset.");
    }

    public function setMenuSent($phoneNumber) {
        $stmt = $this->conn->prepare("UPDATE wa_percakapan SET menu_utama_terkirim = 1 WHERE nomor_telepon = ?");
        $stmt->bind_param("s", $phoneNumber);
        $stmt->execute();
        $stmt->close();
        $this->logger->info("Flag menu_utama_terkirim diatur ke 1 untuk: " . $phoneNumber);
    }
    
    private function updateInteractionTimestamp($phoneNumber) {
        $stmt = $this->conn->prepare("UPDATE wa_percakapan SET terakhir_interaksi_pada = NOW() WHERE nomor_telepon = ?");
        $stmt->bind_param("s", $phoneNumber);
        $stmt->execute();
        $stmt->close();
    }
    public function startLiveChat($phoneNumber) {
        $stmt = $this->conn->prepare("UPDATE wa_percakapan SET status_percakapan = 'live_chat', terakhir_interaksi_pada = NOW() WHERE nomor_telepon = ?");
        $stmt->bind_param("s", $phoneNumber);
        $stmt->execute();
        $stmt->close();
        $this->logger->info("Live chat dimulai untuk: " . $phoneNumber);
    }
    public function saveMessage($conversationId, $senderType, $messageType, $messageContent) {
        $stmt = $this->conn->prepare("INSERT INTO wa_pesan (percakapan_id, pengirim, tipe_pesan, isi_pesan) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $conversationId, $senderType, $messageType, $messageContent);
        $stmt->execute();
        $newId = $stmt->insert_id; 
        $stmt->close();

        $stmt = $this->conn->prepare("SELECT * FROM wa_pesan WHERE id = ?");
        $stmt->bind_param("i", $newId);
        $stmt->execute();
        $result = $stmt->get_result();
        $newMessage = $result->fetch_assoc();
        $stmt->close();
        
        return $newMessage; 
    }
     public function closeConversation($phoneNumber) {
        $stmt = $this->conn->prepare("UPDATE wa_percakapan SET status_percakapan = 'closed', menu_utama_terkirim = 0 WHERE nomor_telepon = ?");
        $stmt->bind_param("s", $phoneNumber);
        $stmt->execute();
        $stmt->close();
        $this->logger->info("Percakapan ditutup untuk: " . $phoneNumber);
    }
}