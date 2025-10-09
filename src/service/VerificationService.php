<?php

class VerificationService {
    private $conn;
    private $logger;

    public function __construct($dbConnection, $logger) {
        $this->conn = $dbConnection;
        $this->logger = $logger;
    }

    
    public function processToken($token) {
        $sql = "SELECT id, id_user, nomor_hp_baru FROM verifikasi_nomor_hp WHERE token_kirim_user = ? AND status = 'menunggu_user' AND kedaluwarsa_pada > NOW()";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $this->handleValidToken($result->fetch_assoc());
        } else {
            $this->logger->warning("Invalid or expired token received: {$token}");
        }
        $stmt->close();
    }

    private function handleValidToken($verifikasiData) {
        $verifikasiId = $verifikasiData['id'];
        $nomorHpBaruUser = $verifikasiData['nomor_hp_baru'];
        
        $tokenKonfirmasi = bin2hex(random_bytes(32));

        $sqlUpdate = "UPDATE verifikasi_nomor_hp SET status = 'menunggu_konfirmasi', token_konfirmasi = ? WHERE id = ?";
        $stmtUpdate = $this->conn->prepare($sqlUpdate);
        $stmtUpdate->bind_param("si", $tokenKonfirmasi, $verifikasiId);
        $stmtUpdate->execute();
        
        if ($stmtUpdate->affected_rows > 0) {
            $this->logger->success("Token valid. Request ID: {$verifikasiId}. Sending confirmation link.");
            $linkFinal = "https://asokababystore.com/customer/verify_update.php?token=" . $tokenKonfirmasi;
            kirimLinkKonfirmasiWA($nomorHpBaruUser, $linkFinal); 
        } else {
            $this->logger->error("Failed to update verification status for ID: {$verifikasiId}");
        }
        $stmtUpdate->close();
    }
}