<?php
require_once __DIR__ . '/../constant/BranchConstants.php';
require_once __DIR__ . '/../service/ConversationService.php'; 
require_once __DIR__ . '/../service/MediaService.php'; 
require_once __DIR__ . '/../config/Config.php';
use Asoka\Constant\BranchConstants;
class WebhookHandler {
    private $logger;
    private $verificationService;
    private $conversationService; 
    public function __construct(VerificationService $verificationService, ConversationService $conversationService, $logger) {
        $this->verificationService = $verificationService;
        $this->conversationService = $conversationService; 
        $this->logger = $logger;
    }
    public function handleVerification() {
        $verify_token = Config::get('WHATSAPP_VERIFY_TOKEN');
        if (isset($_GET['hub_mode']) && $_GET['hub_mode'] === 'subscribe' && $_GET['hub_verify_token'] === $verify_token) {
            echo $_GET['hub_challenge'];
            http_response_code(200);
            $this->logger->info("Webhook verified successfully.");
        } else {
            http_response_code(403);
            echo "Token salah.";
            $this->logger->warning("Webhook verification failed. Invalid token.");
        }
    }
    public function handleIncomingMessage($body) {
        $this->logger->info("Webhook received data: " . json_encode($body));
        $message = $body['entry'][0]['changes'][0]['value']['messages'][0] ?? null;
        if (!$message) {
            return;
        }
        
        $nomorPengirim = $message['from'];
        $messageType = $message['type'];

        
        if ($messageType === 'text') {
            $textBody = $message['text']['body'];

            $patternGantiHp = '/https:\/\/asokababystore\.com\/verifikasi-wa\?token=([a-f0-9]{64})/';
            if (preg_match($patternGantiHp, $textBody, $matches)) {
                $token = $matches[1];
                $this->logger->info("Link verifikasi ganti nomor HP terdeteksi. Token: {$token}");
                $this->verificationService->processToken($token); // Metode lama Anda
                return;
            }

            $patternResetPw = '/Token saya: (resetpw_[a-f0-9]{60})/';
            if (preg_match($patternResetPw, $textBody, $matches)) {
                $token = $matches[1];
                $this->logger->info("Token reset password terdeteksi. Token: {$token}");
                $this->verificationService->processPasswordResetToken($token, $nomorPengirim);
                return;
            }
        }

        $conversation = $this->conversationService->getOrCreateConversation($nomorPengirim);
        
        if ($message['type'] === 'interactive' && isset($message['interactive']['type']) && $message['interactive']['type'] === 'button_reply') {
            $buttonId = $message['interactive']['button_reply']['id'];
            $buttonTitle = $message['interactive']['button_reply']['title'];

            $this->conversationService->saveMessage($conversation['id'], 'user', 'text', $buttonTitle);

            switch ($buttonId) {
                case 'END_LIVE_CHAT':
                    $this->conversationService->closeConversation($nomorPengirim);
                    $this->sendWelcomeMessage($nomorPengirim, $conversation['id']); 
                    $this->conversationService->openConversation($nomorPengirim); 
                    return;

                case 'DAFTAR_JABODETABEK':
                    $this->sendBranchListByRegion($nomorPengirim, 'jabodetabek', 1, 'kontak');
                    return;

                case 'DAFTAR_BELITUNG':
                    $this->sendBranchListByRegion($nomorPengirim, 'belitung', 1, 'kontak');
                    return;

                case 'LOKASI_DAFTAR_JABODETABEK':
                    $this->sendBranchListByRegion($nomorPengirim, 'jabodetabek', 1, 'lokasi');
                    return;
                
                case 'LOKASI_DAFTAR_BELITUNG':
                    $this->sendBranchListByRegion($nomorPengirim, 'belitung', 1, 'lokasi');
                    return;
            }
        }
        
        if ($conversation['status_percakapan'] === 'live_chat') {
            $savedMessage = null; 

            switch ($messageType) {
                case 'text':
                    $messageContent = $message['text']['body'];
                    
                    $savedMessage = $this->conversationService->saveMessage($conversation['id'], 'user', 'text', $messageContent);
                    break;

                case 'image':
                case 'video':
                case 'audio':
                    $mediaService = new MediaService($this->logger);
                    $mediaId = $message[$messageType]['id'];
                    $this->logger->info("Menerima pesan media ({$messageType}) dari {$nomorPengirim} dalam sesi live chat.");
                    $result = $mediaService->downloadAndUpload($mediaId, $messageType);

                    if (isset($result['url'])) {
                        $messageContent = $result['url'];
                        
                        $savedMessage = $this->conversationService->saveMessage($conversation['id'], 'user', $messageType, $messageContent);
                    } else {
                        
                        $limit = $result['limit'] ?? 'yang ditentukan';
                        $mediaName = 'file';
                        switch ($messageType) {
                            case 'image': $mediaName = 'gambar'; break;
                            case 'video': $mediaName = 'video'; break;
                            case 'audio': $mediaName = 'pesan suara'; break;
                        }
                        kirimPesanTeks($nomorPengirim, "Maaf, {$mediaName} yang Anda kirim melebihi batas maksimal {$limit}.");
                        return;
                    }
                    break;

                default:
                    $this->logger->info("Menerima tipe pesan '{$messageType}' yang belum didukung saat live chat.");
                    kirimPesanTeks($nomorPengirim, "Maaf, saat ini kami hanya mendukung pesan teks, gambar, video, dan pesan suara dalam sesi live chat.");
                    return;
            }
            
            
            if ($savedMessage) {
                $this->notifyWebSocketServer([
                    'event' => 'new_message',
                    'conversation_id' => $conversation['id'],
                    'phone' => $nomorPengirim,
                    
                    'message' => $savedMessage 
                ]);
            }
            return;
        }


        if ($conversation['status_percakapan'] === 'closed') {
            $savedUserMessage = null;
            if ($messageType === 'text') {
                $messageContent = $message['text']['body'];
                $savedUserMessage = $this->conversationService->saveMessage($conversation['id'], 'user', 'text', $messageContent);
            } else {
                kirimPesanTeks($nomorPengirim, "Halo Kak! Untuk memulai percakapan, silakan kirim pesan dalam bentuk teks ya.");
                return;
            }

            if ($savedUserMessage) {
                $this->notifyWebSocketServer([
                    'event' => 'new_message',
                    'conversation_id' => $conversation['id'],
                    'phone' => $nomorPengirim,
                    'message' => $savedUserMessage
                ]);
            }

            $this->sendWelcomeMessage($nomorPengirim, $conversation['id']);
            
            $this->conversationService->openConversation($nomorPengirim);
        } else {
            if ($message['type'] === 'text') {
                if ($conversation['menu_utama_terkirim'] == 0) {
                    $this->processTextMessage($message);
                    $this->conversationService->setMenuSent($nomorPengirim);
                } else {
                    $this->logger->info("Mengabaikan pesan teks dari {$nomorPengirim} karena menu utama sudah terkirim (bukan link verifikasi).");
                } 
            } elseif ($message['type'] === 'interactive' && $message['interactive']['type'] === 'list_reply') {
                $this->processListReplyMessage($message, $conversation);
            }
        }
    }
    private function sendWelcomeMessage($nomorPengirim, $conversationId) {
        $this->logger->info("User {$nomorPengirim} memulai percakapan baru, mengirim Welcome Menu.");
        $sections = BranchConstants::MAIN_MENU_SECTIONS;
        $judulHeader = "Asoka Baby Store"; 

        $bodyText = "Terimakasih telah menghubungi Asoka Baby Store.\n\n" .
                    "Jam Operasional:\n" .
                    "- Senin - Sabtu: 08.30 - 16.30 WIB\n" .
                    "- Hari Minggu dan Tanggal Merah: Tutup\n" .
                    "- Pesan yang masuk setelah pukul 16.30 WIB akan dibalas pada hari kerja berikutnya.\n\n" .
                    "Untuk informasi lainnya bisa diakses di website kami:\n" .
                    "asokababystore.com\n\n" .
                    "Silakan pilih menu di bawah ini untuk melanjutkan.";

        kirimPesanList(
            $nomorPengirim,
            $judulHeader,
            $bodyText,
            "",
            "Lihat Pilihan Menu",
            $sections
        );

        $this->saveAdminReply($conversationId, $nomorPengirim, $bodyText);
    }
    private function processTextMessage($message) {
        $nomorPengirim = $message['from'];
        $this->logger->info("User {$nomorPengirim} mengirim pesan teks dalam sesi aktif, memicu Main Menu.");
        $sections = BranchConstants::MAIN_MENU_SECTIONS;
        kirimPesanList(
            $nomorPengirim,
            "Menu Utama", 
            "Silakan pilih lagi dari menu di bawah ini.", 
            "ASOKA Baby Store",
            "Lihat Pilihan Menu",
            $sections
        );
    }
    private function processListReplyMessage($message, $conversation) {
        $nomorPengirim = $message['from'];
        $selectedId = $message['interactive']['list_reply']['id'];
        $selectedTitle = $message['interactive']['list_reply']['title']; 

        $savedUserMessage = $this->conversationService->saveMessage($conversation['id'], 'user', 'text', $selectedTitle);

        $this->notifyWebSocketServer([
            'event' => 'new_message',
            'conversation_id' => $conversation['id'],
            'phone' => $nomorPengirim,
            'message' => [
                'pengirim' => $savedUserMessage['pengirim'],
                'tipe_pesan' => $savedUserMessage['tipe_pesan'],
                'isi_pesan' => $savedUserMessage['isi_pesan'],
                'timestamp' => $savedUserMessage['timestamp']
            ]
        ]);
        $this->logger->info("Received List reply from {$nomorPengirim}. Selected ID: {$selectedId}, Title: {$selectedTitle}"); 
        if (preg_match('/^(JABODETABEK|BELITUNG|LOKASI_JABODETABEK|LOKASI_BELITUNG)_PAGE_(\d+)$/', $selectedId, $matches)) {
            $region = (strpos($matches[1], 'JABODETABEK') !== false) ? 'jabodetabek' : 'belitung';
            $type = (strpos($matches[1], 'LOKASI') !== false) ? 'lokasi' : 'kontak';
            $page = (int)$matches[2];
            $this->sendBranchListByRegion($nomorPengirim, $region, $page, $type);
            return;
        }
        switch ($selectedId) {
            case 'DAFTAR_NOMOR':
                $pesanBody = "Silakan pilih wilayah cabang yang ingin Anda hubungi.";
                $this->saveAdminReply($conversation['id'], $nomorPengirim, $pesanBody); 
                
                kirimPesanButton(
                    $nomorPengirim,
                    $pesanBody,
                    BranchConstants::REGION_SELECTION_BUTTONS,
                    "Pilih Wilayah Cabang", 
                    ""     
                );
                break;
            case 'DAFTAR_LOKASI':
                $pesanBody = "Silakan pilih wilayah toko fisik yang ingin Anda lihat lokasinya.";
                $this->saveAdminReply($conversation['id'], $nomorPengirim, $pesanBody); 
                
                kirimPesanButton(
                    $nomorPengirim,
                    $pesanBody,
                    BranchConstants::REGION_SELECTION_BUTTONS_LOKASI,
                    "Pilih Wilayah Toko", 
                    ""     
                );
                break;
            case 'LOKASI_DAFTAR_JABODETABEK':
                $this->sendBranchListByRegion($nomorPengirim, 'jabodetabek', 1, 'lokasi');
                break;
            case 'LOKASI_DAFTAR_BELITUNG':
                $this->sendBranchListByRegion($nomorPengirim, 'belitung', 1, 'lokasi');
                break;
            case 'CHAT_CS':
                    date_default_timezone_set('Asia/Jakarta');
                    $currentHour = (int)date('H');
                    $currentMinute = (int)date('i');

                    $isOutsideOperationalHours = ($currentHour < 9 || $currentHour > 16 || ($currentHour == 16 && $currentMinute > 30));

                    if ($isOutsideOperationalHours) {
                        $pesanBody = "Maaf Ayah/Bunda, Customer Service kami sedang di luar jam operasional (Senin - Sabtu, 09:00 - 16:30).\n\nPesan Anda akan kami terima dan akan kami balas pada jam operasional berikutnya. Terima kasih.";
                        
                        $this->conversationService->startLiveChat($nomorPengirim);
                        
                        kirimPesanTeks($nomorPengirim, $pesanBody);
                        
                        $this->saveAdminReply($conversation['id'], $nomorPengirim, $pesanBody);

                        $this->notifyWebSocketServer([
                            'event' => 'new_live_chat',
                            'phone' => $nomorPengirim,
                            'conversation_id' => $conversation['id']
                        ]);

                    } else {
                        $this->conversationService->startLiveChat($nomorPengirim);
                        $pesanBody = "Anda sekarang terhubung dengan Customer Service kami. Silakan sampaikan pertanyaan Anda.";
                        
                        kirimPesanTeks($nomorPengirim, $pesanBody);
                        
                        $this->saveAdminReply($conversation['id'], $nomorPengirim, $pesanBody);

                        $this->notifyWebSocketServer([
                            'event' => 'new_live_chat',
                            'phone' => $nomorPengirim,
                            'conversation_id' => $conversation['id']
                        ]);
                    }
                    break;
            default:
                if (strpos($selectedId, 'LOKASI_') === 0) {
                    $branchKey = substr($selectedId, 7);
                    if (isset(BranchConstants::ALL_LOKASI_CABANG[$branchKey])) {
                        $lokasi = BranchConstants::ALL_LOKASI_CABANG[$branchKey];
                        $pesanLokasi = "Mengirimkan lokasi: {$lokasi['name']}";
                        $this->saveAdminReply($conversation['id'], $nomorPengirim, $pesanLokasi); 

                        kirimPesanLokasi($nomorPengirim, $lokasi['latitude'], $lokasi['longitude'], $lokasi['name'], $lokasi['address']);
                    } else {
                        $pesanError = "Maaf, data lokasi untuk cabang {$branchKey} saat ini belum tersedia.";
                        $this->saveAdminReply($conversation['id'], $nomorPengirim, $pesanError); 
                        kirimPesanTeks($nomorPengirim, $pesanError);
                    }
                } 
                elseif (array_key_exists($selectedId, BranchConstants::ALL_NOMOR_TELEPON)) {
                    $namaKontak = "Asoka Baby Store " . $selectedId;
                    $nomorUntukDikirim = BranchConstants::ALL_NOMOR_TELEPON[$selectedId];
                    
                    $pesanKontak = "Mengirimkan kontak: {$namaKontak} ({$nomorUntukDikirim})";
                    $this->saveAdminReply($conversation['id'], $pesanKontak, $pesanKontak); 
                    kirimPesanKontak($nomorPengirim, $namaKontak, $nomorUntukDikirim);
                } 
                else {
                    $pesanError = "Maaf, pilihan Anda tidak valid. Silakan coba lagi.";
                    $this->saveAdminReply($conversation['id'], $nomorPengirim, $pesanError);
                    kirimPesanTeks($nomorPengirim, $pesanError);
                }
                break;
        }
    }
    private function sendBranchListByRegion($nomorPengirim, $region, $page = 1, $type = 'kontak') {
        $this->logger->info("Sending {$type} list for region {$region} page {$page} to {$nomorPengirim}.");
        $all_cities = ($region === 'jabodetabek') ? BranchConstants::CITIES_JABODETABEK : BranchConstants::CITIES_BELITUNG;
        $all_locations = ($region === 'jabodetabek') ? BranchConstants::LOKASI_JABODETABEK : BranchConstants::LOKASI_BELITUNG;
        $items_per_page = 8;
        $offset = ($page - 1) * $items_per_page;
        $cities_to_show_raw = array_slice($all_cities, $offset, $items_per_page);
        $cities_to_show = [];
        foreach ($cities_to_show_raw as $city) {
            if ($type === 'lokasi') {
                if (isset($all_locations[$city['id']])) {
                    $cities_to_show[] = ['id' => 'LOKASI_' . $city['id'], 'title' => $city['title']];
                }
            } else {
                $cities_to_show[] = $city;
            }
        }
        $region_prefix = strtoupper($region); 
        $page_prefix = ($type === 'lokasi') ? "LOKASI_{$region_prefix}" : $region_prefix;
        if (count($all_cities) > $offset + $items_per_page) {
            $next_page = $page + 1;
            $cities_to_show[] = ['id' => "{$page_prefix}_PAGE_{$next_page}", 'title' => '➡️ Halaman Berikutnya'];
        }
        if ($page > 1) {
            $prev_page = $page - 1;
            $cities_to_show[] = ['id' => "{$page_prefix}_PAGE_{$prev_page}", 'title' => '⬅️ Halaman Sebelumnya'];
        }
        $title = "PILIH CABANG (Hal {$page})";
        $header = ($region === 'jabodetabek') ? "Asoka Baby Store Jabodetabek" : "Asoka Baby Store Bangka & Belitung";
        $sections = [['title' => $title, 'rows' => $cities_to_show]];
        $pesanBody = "Silakan pilih cabang yang Anda tuju.";
        $conversation = $this->conversationService->getOrCreateConversation($nomorPengirim);
        $this->saveAdminReply($conversation['id'], $nomorPengirim, $pesanBody); 
        kirimPesanList(
            $nomorPengirim,
            "Pilih Cabang",
            $pesanBody,
            $header,
            "Pilih Cabang",
            $sections
        );

    }

   private function notifyWebSocketServer($data) {
        $ws_url = 'http://127.0.0.1:8081/notify'; 
        $payload = json_encode($data);

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

        if (curl_errno($ch)) {
            $this->logger->error('cURL Error to WebSocket server: ' . curl_error($ch));
        }

        curl_close($ch);
    }

     private function saveAdminReply($conversationId, $nomorPengirim, $messageContent, $messageType = 'text') {
        $savedMessage = $this->conversationService->saveMessage($conversationId, 'admin', $messageType, $messageContent);
        
        if ($savedMessage) {
            $this->notifyWebSocketServer([
                'event' => 'new_message',
                'conversation_id' => $conversationId,
                'phone' => $nomorPengirim,
                'message' => [
                    'pengirim' => 'admin',
                    'tipe_pesan' => $savedMessage['tipe_pesan'],
                    'isi_pesan' => $savedMessage['isi_pesan'],
                    'timestamp' => $savedMessage['timestamp']
                ]
            ]);
        }
    }
}
