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
    public function processPasswordResetToken($token, $nomorPengirim) {
        try {
            $stmt = $this->conn->prepare("SELECT no_hp, kadaluarsa, used FROM reset_token WHERE token = ?");
            $stmt->bind_param("s", $token);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$result) {
                $this->logger->warning("Token reset password tidak valid diterima: {$token}");
                kirimPesanTeks($nomorPengirim, "Maaf, token untuk reset password tidak valid. Silakan ulangi permintaan dari website.");
                return;
            }

            if ($result['used'] == 1) {
                kirimPesanTeks($nomorPengirim, "Maaf, token ini sudah pernah digunakan.");
                return;
            }

            if (new DateTime() > new DateTime($result['kadaluarsa'])) {
                kirimPesanTeks($nomorPengirim, "Maaf, token Anda sudah kedaluwarsa. Silakan ajukan permintaan baru di website.");
                return;
            }
            
            $noHpTerdaftar = $result['no_hp'];
            
            $nomorPengirimNormalized = '0' . substr($nomorPengirim, 2);

            if ($noHpTerdaftar !== $nomorPengirimNormalized) {
                 $this->logger->error("SECURITY ALERT: Token reset untuk {$noHpTerdaftar} coba digunakan oleh {$nomorPengirimNormalized}.");
                 kirimPesanTeks($nomorPengirim, "Terjadi kesalahan keamanan. Permintaan dibatalkan.");
                 return;
            }

            $stmtUpdate = $this->conn->prepare("UPDATE reset_token SET used = 1 WHERE token = ?");
            $stmtUpdate->bind_param("s", $token);
            $stmtUpdate->execute();
            $stmtUpdate->close();

            $finalResetToken = bin2hex(random_bytes(32));
            $createdAt = date('Y-m-d H:i:s');
            $expiredAt = date('Y-m-d H:i:s', strtotime('+30 minutes'));

            $stmtInsert = $this->conn->prepare("INSERT INTO reset_token (no_hp, token, dibuat_tgl, kadaluarsa, used) VALUES (?, ?, ?, ?, 0)");
            $stmtInsert->bind_param("ssss", $noHpTerdaftar, $finalResetToken, $createdAt, $expiredAt);
            $stmtInsert->execute();
            $stmtInsert->close();
            
            $resetLink = "https://asokababystore.com/reset-password-final.php?token=" . $finalResetToken; 
            $pesanBalasan = "Verifikasi berhasil! 👍\n\nKlik link di bawah ini untuk membuat password baru Anda. Link ini hanya berlaku 30 menit.\n\n" . $resetLink;

            kirimPesanTeks($nomorPengirim, $pesanBalasan);

        } catch (Exception $e) {
            $this->logger->error("Error saat proses token reset password: " . $e->getMessage());
            kirimPesanTeks($nomorPengirim, "Terjadi kesalahan internal. Silakan coba lagi nanti.");
        }
    }
}