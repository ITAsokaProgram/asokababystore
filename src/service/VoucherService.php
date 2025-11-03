<?php
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../config/Config.php';
require_once __DIR__ . '/../constant/VoucherConstants.php';
require_once __DIR__ . '/../constant/BranchConstants.php';
use Asoka\Constant\VoucherConstants;
use Asoka\Constant\BranchConstants;
use Cloudinary\Cloudinary;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;
class VoucherService
{
    private $conn;
    private $logger;
    private $cloudinary;
    public function __construct($dbConnection, $logger)
    {
        $this->conn = $dbConnection;
        $this->logger = $logger;
        Config::load();
        $env = Config::getMultiple(['CLOUDINARY_NAME', 'CLOUDINARY_KEY', 'CLOUDINARY_SECRET']);
        $this->cloudinary = new Cloudinary([
            'cloud' => [
                'cloud_name' => $env['CLOUDINARY_NAME'],
                'api_key' => $env['CLOUDINARY_KEY'],
                'api_secret' => $env['CLOUDINARY_SECRET'],
            ],
        ]);
    }
    public function getAnyActiveSession($nomorPengirim)
    {
        $stmt = $this->conn->prepare("SELECT * FROM wa_promo_sessions WHERE nomor_telepon = ? AND status_flow NOT IN ('redeemed', 'cancelled')");
        $stmt->bind_param("s", $nomorPengirim);
        $stmt->execute();
        $result = $stmt->get_result();
        $session = $result->fetch_assoc();
        $stmt->close();
        return $session;
    }
    public function handlePromoMessage($conversation, $message, $activePromoSession)
    {
        $status = $activePromoSession['status_flow'];
        $messageType = $message['type'];
        $this->logger->info("Handling promo message for {$activePromoSession['nomor_telepon']}. Status: {$status}. Type: {$messageType}");
        if ($messageType === 'text') {
            $textBody = strtolower(trim($message['text']['body']));
            if ($textBody === 'batal' || $textBody === 'cancel') {
                $this->saveMessage($conversation['id'], 'user', 'text', $message['text']['body']);
                $this->cancelSession($conversation, $activePromoSession);
                return;
            }
        }
        switch ($status) {
            case 'awaiting_confirmation':
                if ($messageType === 'interactive' && $message['interactive']['type'] === 'button_reply') {
                    $this->processConfirmationReply($conversation, $message['interactive']['button_reply'], $activePromoSession);
                } else {
                    $messageContent = $message['text']['body'] ?? "[{$messageType}]";
                    $this->saveMessage($conversation['id'], 'user', 'text', $messageContent);
                    $this->sendDefaultReply($conversation, "Maaf, kami menunggu jawaban 'Ya' atau 'Tidak'. Silakan tekan salah satu tombol di atas, atau ketik 'batal'.");
                }
                break;
            case 'awaiting_location':
                if ($messageType === 'location') {
                    $this->processLocation($conversation, $message['location'], $activePromoSession);
                } else {
                    $messageContent = $message['text']['body'] ?? "[{$messageType}]";
                    $this->saveMessage($conversation['id'], 'user', 'text', $messageContent);
                    $this->sendDefaultReply($conversation, "Kami masih menunggu Ayah/Bunda untuk 'share location' ya. Atau ketik 'batal' untuk membatalkan.");
                }
                break;
            case 'awaiting_branch':
                if ($messageType === 'interactive' && $message['interactive']['type'] === 'list_reply') {
                    $this->processBranchReply($conversation, $message['interactive']['list_reply'], $activePromoSession);
                } else {
                    $messageContent = $message['text']['body'] ?? "[{$messageType}]";
                    $this->saveMessage($conversation['id'], 'user', 'text', $messageContent);
                    $this->sendDefaultReply($conversation, "Maaf, kami menunggu Ayah/Bunda memilih cabang di atas. Atau ketik 'batal' untuk membatalkan.");
                }
                break;
            case 'awaiting_product':
                if ($messageType === 'interactive' && $message['interactive']['type'] === 'list_reply') {
                    $this->processProductReply($conversation, $message['interactive']['list_reply'], $activePromoSession);
                } else {
                    $messageContent = $message['text']['body'] ?? "[{$messageType}]";
                    $this->saveMessage($conversation['id'], 'user', 'text', $messageContent);
                    $this->sendDefaultReply($conversation, "Maaf, kami menunggu Ayah/Bunda memilih produk di atas. Atau ketik 'batal' untuk membatalkan.");
                }
                break;
            default:
                $this->logger->warning("Promo session in unknown state: {$status}");
                $this->sendDefaultReply($conversation, "Maaf, terjadi kesalahan pada sesi promo Anda. Silakan ketik 'batal' dan coba lagi.");
                break;
        }
    }
    public function handleVoucherCode($conversation, $message, $namaPengirim)
    {
        $nomorPengirim = $conversation['nomor_telepon'];
        $percakapanId = $conversation['id'];
        $kode_voucher = trim($message['text']['body']);
        $voucherDetails = VoucherConstants::VOUCHERS[$kode_voucher] ?? null;
        if (!$voucherDetails) {
            $this->logger->warning("Voucher code not found: {$kode_voucher}");
            return;
        }
        $this->saveMessage($percakapanId, 'user', 'text', $kode_voucher);
        $stmtCheck = $this->conn->prepare("SELECT status_flow FROM wa_promo_sessions WHERE nomor_telepon = ? AND kode_voucher = ?");
        $stmtCheck->bind_param("ss", $nomorPengirim, $kode_voucher);
        $stmtCheck->execute();
        $resultCheck = $stmtCheck->get_result();
        $existingSession = $resultCheck->fetch_assoc();
        $stmtCheck->close();
        if ($existingSession && $existingSession['status_flow'] === 'redeemed') {
            $this->sendDefaultReply($conversation, "Maaf Ayah/Bunda, kode voucher {$kode_voucher} sudah pernah Anda gunakan sebelumnya.");
            return;
        }
        $status_flow = 'awaiting_confirmation';
        $stmtUpsert = $this->conn->prepare("
            INSERT INTO wa_promo_sessions (percakapan_id, nomor_telepon, kode_voucher, status_flow)
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
            percakapan_id = VALUES(percakapan_id),
            status_flow = VALUES(status_flow),
            cabang_terpilih = NULL,
            plu_terpilih = NULL,
            nama_produk_terpilih = NULL
        ");
        $stmtUpsert->bind_param("isss", $percakapanId, $nomorPengirim, $kode_voucher, $status_flow);
        $stmtUpsert->execute();
        $stmtUpsert->close();
        $namaPromo = $voucherDetails['nama_promo'];
        $pesanBody = "Apakah Ayah/Bunda hendak melanjutkan untuk mendapatkan promo *{$namaPromo}*?";
        $buttons = [
            ['id' => 'VOUCHER_CONFIRM_YES', 'title' => 'Ya'],
            ['id' => 'VOUCHER_CONFIRM_NO', 'title' => 'Tidak']
        ];
        $sendResult = kirimPesanButton($nomorPengirim, $pesanBody, $buttons, "Konfirmasi Promo");
        $this->saveMessage($percakapanId, 'admin', 'text', $pesanBody, $sendResult['wamid'] ?? null, 1);
    }
    private function processConfirmationReply($conversation, $buttonReply, $session)
    {
        $nomorPengirim = $conversation['nomor_telepon'];
        $percakapanId = $conversation['id'];
        $buttonId = $buttonReply['id'];
        $buttonTitle = $buttonReply['title'];
        $this->saveMessage($percakapanId, 'user', 'text', $buttonTitle);
        if ($buttonId === 'VOUCHER_CONFIRM_YES') {
            $this->updateSessionStatus($session['id'], 'awaiting_location');
            $pesanBody = "Baik Ayah/Bunda. Silakan *Share Location* Anda (Gunakan fitur 'Location' di WhatsApp, bukan kirim alamat) untuk menentukan lokasi penukaran voucher.";
            $sendResult = kirimPesanTeks($nomorPengirim, $pesanBody);
            $this->saveMessage($percakapanId, 'admin', 'text', $pesanBody, $sendResult['wamid'] ?? null, 1);
        } else {
            $this->updateSessionStatus($session['id'], 'cancelled');
            $pesanBody = "Baik Ayah/Bunda. Promo dibatalkan. Terima kasih.";
            $sendResult = kirimPesanTeks($nomorPengirim, $pesanBody);
            $this->saveMessage($percakapanId, 'admin', 'text', $pesanBody, $sendResult['wamid'] ?? null, 1);
        }
    }
    private function processLocation($conversation, $location, $session)
    {
        $nomorPengirim = $conversation['nomor_telepon'];
        $percakapanId = $conversation['id'];
        $userLat = $location['latitude'];
        $userLon = $location['longitude'];
        $this->saveMessage($percakapanId, 'user', 'location', json_encode($location));
        $nearbyBranches = $this->findNearbyBranches($userLat, $userLon, 5);
        if (empty($nearbyBranches)) {
            $pesanBody = "Maaf Ayah/Bunda, kami tidak menemukan cabang terdekat dari lokasi Anda.";
            $this->sendDefaultReply($conversation, $pesanBody);
            $this->updateSessionStatus($session['id'], 'cancelled');
            return;
        }
        $rows = [];
        foreach ($nearbyBranches as $branch) {
            $rowTitle = substr($branch['name'], 0, 24);
            $rowDesc = substr(" (± " . number_format($branch['distance'], 1) . " km)", 0, 72);
            $rows[] = [
                'id' => 'VOUCHER_BRANCH_' . $branch['key'],
                'title' => $rowTitle,
                'description' => $rowDesc
            ];
        }
        $sections = [['title' => 'Cabang Terdekat', 'rows' => $rows]];
        $pesanBody = "Silakan pilih salah satu cabang Asoka terdekat Anda dari daftar di bawah ini.";
        $sendResult = kirimPesanList(
            $nomorPengirim,
            "Pilih Cabang",
            $pesanBody,
            "Asoka Baby Store",
            "Lihat Cabang",
            $sections
        );
        $this->saveMessage($percakapanId, 'admin', 'text', $pesanBody, $sendResult['wamid'] ?? null, 1);
        $this->updateSessionStatus($session['id'], 'awaiting_branch');
    }
    private function processBranchReply($conversation, $listReply, $session)
    {
        $nomorPengirim = $conversation['nomor_telepon'];
        $percakapanId = $conversation['id'];
        $selectedId = $listReply['id'];
        $selectedTitle = $listReply['title'];
        $cabangKey = str_replace('VOUCHER_BRANCH_', '', $selectedId);
        $namaCabangLengkap = BranchConstants::ALL_LOKASI_CABANG[$cabangKey]['name'] ?? $cabangKey;
        $this->saveMessage($percakapanId, 'user', 'text', $selectedTitle);
        $this->updateSessionData($session['id'], [
            'status_flow' => 'awaiting_product',
            'cabang_terpilih' => $namaCabangLengkap
        ]);
        $voucherDetails = VoucherConstants::VOUCHERS[$session['kode_voucher']] ?? null;
        if (!$voucherDetails || empty($voucherDetails['produk'])) {
            $this->sendDefaultReply($conversation, "Maaf, terjadi kesalahan data produk untuk voucher ini.");
            $this->updateSessionStatus($session['id'], 'cancelled');
            return;
        }
        $rows = [];
        foreach ($voucherDetails['produk'] as $plu => $namaProduk) {
            $rows[] = [
                'id' => 'VOUCHER_PRODUCT_' . $plu,
                'title' => substr($namaProduk, 0, 24)
            ];
        }
        $sections = [['title' => 'Produk Promo', 'rows' => $rows]];
        $pesanBody = "Ayah/Bunda telah memilih cabang *{$namaCabangLengkap}*. Silakan pilih produk yang ingin dibeli:";
        $sendResult = kirimPesanList(
            $nomorPengirim,
            "Pilih Produk",
            $pesanBody,
            "Promo: " . $voucherDetails['nama_promo'],
            "Lihat Produk",
            $sections
        );
        $this->saveMessage($percakapanId, 'admin', 'text', $pesanBody, $sendResult['wamid'] ?? null, 1);
    }
    private function processProductReply($conversation, $listReply, $session)
    {
        $nomorPengirim = $conversation['nomor_telepon'];
        $percakapanId = $conversation['id'];
        $selectedId = $listReply['id'];
        $selectedTitle = $listReply['title'];
        $plu = str_replace('VOUCHER_PRODUCT_', '', $selectedId);
        $this->saveMessage($percakapanId, 'user', 'text', $selectedTitle);
        $this->updateSessionData($session['id'], [
            'status_flow' => 'redeemed',
            'plu_terpilih' => $plu,
            'nama_produk_terpilih' => $selectedTitle
        ]);
        $voucherDetails = VoucherConstants::VOUCHERS[$session['kode_voucher']];
        $namaCabang = $session['cabang_terpilih'];
        $stmt = $this->conn->prepare("SELECT * FROM wa_promo_sessions WHERE id = ?");
        $stmt->bind_param("i", $session['id']);
        $stmt->execute();
        $finalSession = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        $qrData = json_encode([
            'session_id' => $finalSession['id'],
            'voucher' => $finalSession['kode_voucher'],
            'user' => $finalSession['nomor_telepon'],
            'plu' => $finalSession['plu_terpilih'],
            'cabang' => $finalSession['cabang_terpilih']
        ]);
        $qrUrl = $this->generateAndUploadQrCode($finalSession['id'], $qrData);
        if (!$qrUrl) {
            $this->sendDefaultReply($conversation, "Maaf, terjadi kesalahan saat membuat QR Code. Silakan hubungi CS.");
            $this->updateSessionStatus($session['id'], 'cancelled');
            return;
        }
        $potonganHarga = number_format($voucherDetails['potongan_harga'], 0, ',', '.');
        $caption = "🎉 *Voucher Promo Berhasil Dibuat* 🎉\n\n" .
            "Scan QR Code ini di kasir untuk mendapatkan potongan harga.\n\n" .
            "*- Detail Voucher -*\n" .
            "**Kode:** `{$finalSession['kode_voucher']}`\n" .
            "**Produk:** {$finalSession['nama_produk_terpilih']}\n" .
            "**Potongan:** Rp {$potonganHarga}\n" .
            "**Cabang:** {$finalSession['cabang_terpilih']}";
        $sendResult = kirimPesanMedia($nomorPengirim, $qrUrl, 'image', $caption);
        $this->saveMessage($percakapanId, 'admin', 'image', $qrUrl, $sendResult['wamid'] ?? null, 1);
        $pesanFinal = "Tunjukan QR ini ke kasir untuk mendapatkan potongan harga. Voucher hanya berlaku untuk 1 kali pemakaian.";
        $sendResultFinal = kirimPesanTeks($nomorPengirim, $pesanFinal);
        $this->saveMessage($percakapanId, 'admin', 'text', $pesanFinal, $sendResultFinal['wamid'] ?? null, 1);
    }
    private function generateAndUploadQrCode($sessionId, $qrData)
    {
        try {
            $result = Builder::create()
                ->writer(new PngWriter())
                ->data($qrData)
                ->size(300)
                ->margin(10)
                ->build();
            $qrString = $result->getString();
            $base64Data = 'data:image/png;base64,' . base64_encode($qrString);
            $uploadResult = $this->cloudinary->uploadApi()->upload($base64Data, [
                'folder' => 'whatsapp_vouchers',
                'public_id' => 'promo_qr_' . $sessionId . '_' . time(),
                'resource_type' => 'image'
            ]);
            return $uploadResult['secure_url'];
        } catch (Exception $e) {
            $this->logger->error("Gagal generate/upload QR: " . $e->getMessage());
            return null;
        }
    }
    private function findNearbyBranches($userLat, $userLon, $limit = 5)
    {
        $distances = [];
        foreach (BranchConstants::ALL_LOKASI_CABANG as $key => $branch) {
            $distance = $this->haversineDistance(
                $userLat,
                $userLon,
                $branch['latitude'],
                $branch['longitude']
            );
            $distances[$key] = [
                'key' => $key,
                'name' => $branch['name'],
                'address' => $branch['address'],
                'distance' => $distance
            ];
        }
        uasort($distances, function ($a, $b) {
            return $a['distance'] <=> $b['distance'];
        });
        return array_slice($distances, 0, $limit);
    }
    private function haversineDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earthRadius * $c;
    }
    private function updateSessionStatus($sessionId, $status)
    {
        $stmt = $this->conn->prepare("UPDATE wa_promo_sessions SET status_flow = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $sessionId);
        $stmt->execute();
        $stmt->close();
    }
    private function updateSessionData($sessionId, $data)
    {
        $query = "UPDATE wa_promo_sessions SET ";
        $params = [];
        $types = "";
        foreach ($data as $key => $value) {
            $query .= "{$key} = ?, ";
            $params[] = $value;
            $types .= "s";
        }
        $query = rtrim($query, ", ");
        $query .= " WHERE id = ?";
        $params[] = $sessionId;
        $types .= "i";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $stmt->close();
    }
    private function sendDefaultReply($conversation, $pesanBody)
    {
        $sendResult = kirimPesanTeks($conversation['nomor_telepon'], $pesanBody);
        $this->saveMessage($conversation['id'], 'admin', 'text', $pesanBody, $sendResult['wamid'] ?? null, 1);
    }
    private function saveMessage($conversationId, $senderType, $messageType, $messageContent, $wamid = null, $isBot = 0)
    {
        try {
            $statusPengiriman = ($senderType === 'admin' && $wamid !== null) ? 'sent' : null;
            if ($senderType === 'user')
                $isBot = 0;
            $stmt = $this->conn->prepare("INSERT INTO wa_pesan (percakapan_id, pengirim, tipe_pesan, isi_pesan, wamid, status_pengiriman, dikirim_oleh_bot, timestamp) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
            $stmt->bind_param("isssssi", $conversationId, $senderType, $messageType, $messageContent, $wamid, $statusPengiriman, $isBot);
            $stmt->execute();
            $stmt->close();
        } catch (Exception $e) {
            $this->logger->error("Gagal saveMessage di VoucherService: " . $e->getMessage());
        }
    }
    public function cancelSession($conversation, $session)
    {
        $this->updateSessionStatus($session['id'], 'cancelled');
        $pesanBody = "Sesi promo untuk kode `{$session['kode_voucher']}` telah dibatalkan. Anda dapat memulai ulang percakapan.";
        $sendResult = kirimPesanTeks($conversation['nomor_telepon'], $pesanBody);
        $this->saveMessage($conversation['id'], 'admin', 'text', $pesanBody, $sendResult['wamid'] ?? null, 1);
    }
}