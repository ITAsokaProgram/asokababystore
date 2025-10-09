<?php
// Pastikan path ini benar
require_once __DIR__ . "/aa_kon_sett.php"; 
require_once __DIR__ . "/src/helpers/whatsapp_helper_link.php";
require_once __DIR__ . "/src/utils/Logger.php";

$verify_token = "asoka123hooktoken";
$logger = new AppLogger('webhook_handler.log');

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['hub_mode'])) {
    // Verifikasi webhook dari Facebook
    $hub_verify_token = $_GET['hub_verify_token'];
    $hub_challenge = $_GET['hub_challenge'];
    $hub_mode = $_GET['hub_mode'];

    if ($hub_mode === 'subscribe' && $hub_verify_token === $verify_token) {
        echo $hub_challenge;
        http_response_code(200);
        $logger->info("Webhook verified successfully.");
    } else {
        http_response_code(403);
        $logger->warning("Webhook verification failed. Invalid token.");
        echo "Token salah.";
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = file_get_contents('php://input');
    $logger->info("Webhook received data: " . $data);
    $body = json_decode($data, true);

    // Pastikan ini adalah notifikasi pesan dari WhatsApp
    if (isset($body['object']) && $body['object'] === 'whatsapp_business_account') {
        if (isset($body['entry'][0]['changes'][0]['value']['messages'][0])) {
            $message = $body['entry'][0]['changes'][0]['value']['messages'][0];
            
            // Hanya proses pesan tipe 'text'
            if ($message['type'] === 'text') {
                $nomorPengirim = $message['from']; // Nomor user, format '628...'
                $pesanMasuk = $message['text']['body'];

                // Cari URL di dalam pesan
                preg_match('#\bhttps?://[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/))#', $pesanMasuk, $matches);

                if (!empty($matches[0])) {
                    $urlDiterima = $matches[0];
                    $logger->info("URL found: {$urlDiterima} from {$nomorPengirim}");

                    // Parsing URL untuk mendapatkan token
                    $queryParams = [];
                    parse_str(parse_url($urlDiterima, PHP_URL_QUERY), $queryParams);
                    
                    if (isset($queryParams['token'])) {
                        $tokenDariUser = $queryParams['token'];
                        
                        // Proses token ini
                        handleVerificationRequest($conn, $tokenDariUser, $logger);
                    } else {
                        $logger->warning("URL received but no token found in query: {$urlDiterima}");
                    }
                }
            }
        }
    }

    http_response_code(200);
    exit;
}

function handleVerificationRequest($conn, $token, $logger) {
    // Cari token di database yang statusnya 'menunggu_user' dan belum kadaluwarsa
    $sql = "SELECT id, id_user, nomor_hp_baru FROM verifikasi_nomor_hp WHERE token_kirim_user = ? AND status = 'menunggu_user' AND kedaluwarsa_pada > NOW()";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $verifikasiData = $result->fetch_assoc();
        $verifikasiId = $verifikasiData['id'];
        $nomorHpBaruUser = $verifikasiData['nomor_hp_baru'];
        
        // Buat token konfirmasi final
        $tokenKonfirmasi = bin2hex(random_bytes(32));

        // Update status dan simpan token konfirmasi
        $sqlUpdate = "UPDATE verifikasi_nomor_hp SET status = 'menunggu_konfirmasi', token_konfirmasi = ? WHERE id = ?";
        $stmtUpdate = $conn->prepare($sqlUpdate);
        $stmtUpdate->bind_param("si", $tokenKonfirmasi, $verifikasiId);
        $stmtUpdate->execute();
        
        if ($stmtUpdate->affected_rows > 0) {
            $logger->success("Token valid. Request ID: {$verifikasiId}. Sending confirmation link.");
            // Kirim link konfirmasi ke nomor HP BARU user
            $linkFinal = "https://asokababystore.com/customer/verify_update.php?token=" . $tokenKonfirmasi;
            kirimLinkKonfirmasiWA($nomorHpBaruUser, $linkFinal);
        } else {
            $logger->error("Failed to update verification status for ID: {$verifikasiId}");
        }
        $stmtUpdate->close();

    } else {
        $logger->warning("Invalid or expired token received: {$token}");
        // Opsional: Kirim pesan error ke user? Sebaiknya tidak untuk keamanan.
    }
    $stmt->close();
}
?>