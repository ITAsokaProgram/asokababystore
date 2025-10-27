<?php
require_once __DIR__ . '/../constant/BranchConstants.php';
require_once __DIR__ . '/../constant/WhatsappConstants.php'; 
require_once __DIR__ . '/../service/ConversationService.php';
require_once __DIR__ . '/../service/MediaService.php';
require_once __DIR__ . '/../config/Config.php';

use Asoka\Constant\BranchConstants;
use Asoka\Constant\WhatsappConstants; 

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
        $contact = $body['entry'][0]['changes'][0]['value']['contacts'][0] ?? null;
        $namaPengirim = $contact['profile']['name'] ?? 'Pelanggan'; 
        $nomorPengirim = $message['from'];
        $messageType = $message['type'];

        // $this->logger->info("Pesan diterima dari: {$namaPengirim} ({$nomorPengirim})");

        if ($messageType === 'text') {
            $textBody = $message['text']['body'];

            if (preg_match(WhatsappConstants::REGEX_CHANGE_PHONE, $textBody, $matches)) {
                $token = $matches[1];
                // $this->logger->info("Link verifikasi ganti nomor HP terdeteksi. Token: {$token}");
                $this->verificationService->processToken($token);
                return;
            }

            if (preg_match(WhatsappConstants::REGEX_RESET_PASSWORD, $textBody, $matches)) {
                $token = $matches[1];
                // $this->logger->info("Token reset password terdeteksi. Token: {$token}");
                $this->verificationService->processPasswordResetToken($token, $nomorPengirim);
                return;
            }
        }

        $conversation = $this->conversationService->getOrCreateConversation($nomorPengirim, $namaPengirim);

        if ($conversation['status_percakapan'] === 'live_chat') {
            if ($messageType === 'interactive' &&
                isset($message['interactive']['type']) &&
                $message['interactive']['type'] === 'button_reply' &&
                $message['interactive']['button_reply']['id'] === 'END_LIVE_CHAT') {

                $buttonTitle = $message['interactive']['button_reply']['title'];
                $this->conversationService->saveMessage($conversation['id'], 'user', 'text', $buttonTitle);
                $this->conversationService->closeConversation($nomorPengirim);
                $this->sendWelcomeMessage($nomorPengirim, $conversation['id'], $namaPengirim);
                $this->conversationService->openConversation($nomorPengirim);
                return;
            }

            if ($messageType === 'interactive') {
                // $this->logger->info("User {$nomorPengirim} mencoba memilih menu saat live chat. Mengirim pesan untuk mengakhiri chat.");
                $buttons = [['id' => 'END_LIVE_CHAT', 'title' => 'Akhiri Chat']];
                kirimPesanButton($nomorPengirim, WhatsappConstants::LIVE_CHAT_MENU_PROMPT, $buttons);
                $this->saveAdminReply($conversation['id'], $nomorPengirim, WhatsappConstants::LIVE_CHAT_MENU_PROMPT);
                return;
            }

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
                    // $this->logger->info("Menerima pesan media ({$messageType}) dari {$nomorPengirim} dalam sesi live chat.");
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
                        kirimPesanTeks($nomorPengirim, sprintf(WhatsappConstants::MEDIA_SIZE_EXCEEDED, $mediaName, $limit));
                        return;
                    }
                    break;

                default:
                    // $this->logger->info("Menerima tipe pesan '{$messageType}' yang belum didukung saat live chat.");
                    kirimPesanTeks($nomorPengirim, WhatsappConstants::MEDIA_UNSUPPORTED_LIVE_CHAT);
                    return;
            }

            if ($savedMessage) {
                $totalUnread = $this->conversationService->getTotalUnreadCount(); 
                $this->notifyWebSocketServer([
                    'event' => 'new_message',
                    'conversation_id' => $conversation['id'],
                    'phone' => $nomorPengirim,
                    'message' => $savedMessage,
                    'total_unread_count' => $totalUnread 
                ]);
            }
            return; 
        }

        if ($messageType === 'text') {
            $textBody = $message['text']['body'];
            if (preg_match('/(lowongan|loker)/i', $textBody)) {

                $savedUserMessage = $this->conversationService->saveMessage($conversation['id'], 'user', 'text', $textBody);

                if ($savedUserMessage) {
                    $totalUnread = $this->conversationService->getTotalUnreadCount(); 
                    $this->notifyWebSocketServer([
                        'event' => 'new_message',
                        'conversation_id' => $conversation['id'],
                        'phone' => $nomorPengirim,
                        'message' => $savedUserMessage,
                        'total_unread_count' => $totalUnread 
                    ]);
                }

                kirimPesanTeks($nomorPengirim, WhatsappConstants::LOKER_MESSAGE);

                $this->saveAdminReply($conversation['id'], $nomorPengirim, WhatsappConstants::LOKER_MESSAGE);
                
                return;
            }
        }


        if ($message['type'] === 'interactive' && isset($message['interactive']['type']) && $message['interactive']['type'] === 'button_reply') {
            $buttonId = $message['interactive']['button_reply']['id'];
            $buttonTitle = $message['interactive']['button_reply']['title'];

            $this->conversationService->saveMessage($conversation['id'], 'user', 'text', $buttonTitle);

            switch ($buttonId) {
                case 'BUKA_MENU_UTAMA':
                    $this->sendMainMenuAsList($nomorPengirim, $conversation['id']);
                    return;
                case 'REQ_LIVE_CHAT': 
                    $this->triggerLiveChat($nomorPengirim, $conversation);
                    return;
                
                case 'CHAT_CS':
                    $this->triggerLiveChat($nomorPengirim, $conversation);
                    return;
                case 'DAFTAR_JABODETABEK':
                    $this->sendBranchListByRegion($nomorPengirim, 'jabodetabek', 1, 'kontak', $namaPengirim);
                    return;

                case 'DAFTAR_BELITUNG':
                    $this->sendBranchListByRegion($nomorPengirim, 'belitung', 1, 'kontak', $namaPengirim);
                    return;

                case 'LOKASI_DAFTAR_JABODETABEK':
                    $this->sendBranchListByRegion($nomorPengirim, 'jabodetabek', 1, 'lokasi', $namaPengirim);
                    return;

                case 'LOKASI_DAFTAR_BELITUNG':
                    $this->sendBranchListByRegion($nomorPengirim, 'belitung', 1, 'lokasi', $namaPengirim);
                    return;
            }
        }

        if ($conversation['status_percakapan'] === 'closed') {
            $savedUserMessage = null;
            $messageContent = ''; 

            if ($messageType === 'text') {
                $messageContent = $message['text']['body']; 
                $savedUserMessage = $this->conversationService->saveMessage($conversation['id'], 'user', 'text', $messageContent);
            } else {
                kirimPesanTeks($nomorPengirim, WhatsappConstants::TEXT_ONLY_TO_START);
                return;
            }

            if ($savedUserMessage) {
                $totalUnread = $this->conversationService->getTotalUnreadCount(); 
                $this->notifyWebSocketServer([
                    'event' => 'new_message',
                    'conversation_id' => $conversation['id'],
                    'phone' => $nomorPengirim,
                    'message' => $savedUserMessage,
                    'total_unread_count' => $totalUnread 
                ]);
            }

            kirimPesanTeks($nomorPengirim, $messageContent);

            $this->sendWelcomeMessage($nomorPengirim, $conversation['id'], $namaPengirim);
            $this->conversationService->openConversation($nomorPengirim);
        }
        else {
            if ($message['type'] === 'text') {
                $messageContent = $message['text']['body'];
                $savedUserMessage = $this->conversationService->saveMessage($conversation['id'], 'user', 'text', $messageContent);
                if ($savedUserMessage) {
                    $totalUnread = $this->conversationService->getTotalUnreadCount();
                    $this->notifyWebSocketServer([
                        'event' => 'new_message',
                        'conversation_id' => $conversation['id'],
                        'phone' => $nomorPengirim,
                        'message' => $savedUserMessage,
                        'total_unread_count' => $totalUnread 
                    ]);
                }
                if ($conversation['menu_utama_terkirim'] == 0) {
                    $this->processTextMessage($message, $conversation); 
                    $this->conversationService->setMenuSent($nomorPengirim);
                } else {
                    // $this->logger->info("Mengabaikan pesan teks dari {$nomorPengirim} karena menu utama sudah terkirim (bukan link verifikasi).");
                }
            } elseif ($message['type'] === 'interactive' && $message['interactive']['type'] === 'list_reply') {
                $this->processListReplyMessage($message, $conversation, $namaPengirim);
            }
        }
    }
    
    private function sendWelcomeMessage($nomorPengirim, $conversationId, $namaPengirim) {
        // $this->logger->info("User {$nomorPengirim} memulai percakapan baru, mengirim Welcome Menu.");
        
        kirimPesanList(
            $nomorPengirim,
            "Hai " . $namaPengirim,
            WhatsappConstants::WELCOME_BODY,
            "",
            WhatsappConstants::WELCOME_BUTTON_TEXT,
            BranchConstants::MAIN_MENU_SECTIONS
        );

        $this->saveAdminReply($conversationId, $nomorPengirim, WhatsappConstants::WELCOME_BODY);
    }

    private function processTextMessage($message, $conversation) { 
        $nomorPengirim = $message['from'];
        // $this->logger->info("User {$nomorPengirim} mengirim pesan teks dalam sesi aktif, memicu Main Menu (Button).");

        $buttons = [
            ['id' => 'BUKA_MENU_UTAMA', 'title' => 'Buka Menu Utama'],
            ['id' => 'CHAT_CS', 'title' => 'Live Chat CS']
        ];
        
        $pesanBody = "Silakan pilih lagi dari menu di bawah ini.";
        $pesanHeader = "Menu Utama";

        kirimPesanButton(
            $nomorPengirim,
            $pesanBody,
            $buttons,
            $pesanHeader
        );

        $this->saveAdminReply($conversation['id'], $nomorPengirim, $pesanBody);
    }

    private function processListReplyMessage($message, $conversation, $namaPengirim) {
        $nomorPengirim = $message['from'];
        $selectedId = $message['interactive']['list_reply']['id'];
        $selectedTitle = $message['interactive']['list_reply']['title'];

        $messageToSave = $selectedTitle;
        if ($selectedId === 'CHAT_CS') {
            $messageToSave = 'Chat dengan customer service:';
        }

        $savedUserMessage = $this->conversationService->saveMessage($conversation['id'], 'user', 'text', $messageToSave);
        
        $totalUnread = $this->conversationService->getTotalUnreadCount(); 

        $this->notifyWebSocketServer([
            'event' => 'new_message',
            'conversation_id' => $conversation['id'],
            'phone' => $nomorPengirim,
            'message' => $savedUserMessage,
            'total_unread_count' => $totalUnread 
        ]);
        
        // $this->logger->info("Received List reply from {$nomorPengirim}. Selected ID: {$selectedId}, Title: {$selectedTitle}");
        if (preg_match('/^(JABODETABEK|BELITUNG|LOKASI_JABODETABEK|LOKASI_BELITUNG)_PAGE_(\d+)$/', $selectedId, $matches)) {
            $region = (strpos($matches[1], 'JABODETABEK') !== false) ? 'jabodetabek' : 'belitung';
            $type = (strpos($matches[1], 'LOKASI') !== false) ? 'lokasi' : 'kontak';
            $page = (int)$matches[2];
            $this->sendBranchListByRegion($nomorPengirim, $region, $page, $type, $namaPengirim);
            return;
        }

        $pesanBody = '';
        switch ($selectedId) {
            case 'DAFTAR_NOMOR':
                $pesanBody = WhatsappConstants::CHOOSE_BRANCH_REGION_PROMPT;
                $this->saveAdminReply($conversation['id'], $nomorPengirim, $pesanBody);
                kirimPesanButton($nomorPengirim, $pesanBody, BranchConstants::REGION_SELECTION_BUTTONS, "Pilih Wilayah Cabang", "");
                break;
            case 'DAFTAR_LOKASI':
                $pesanBody = WhatsappConstants::CHOOSE_LOCATION_REGION_PROMPT;
                $this->saveAdminReply($conversation['id'], $nomorPengirim, $pesanBody);
                kirimPesanButton($nomorPengirim, $pesanBody, BranchConstants::REGION_SELECTION_BUTTONS_LOKASI, "Pilih Wilayah Toko", "");
                break;
            case 'ORDER_VIA_WA':
                $pesanHeader = WhatsappConstants::HOW_TO_ORDER_WA_HEADER;
                $pesanBody = WhatsappConstants::HOW_TO_ORDER_WA_BODY;
                $this->saveAdminReply($conversation['id'], $nomorPengirim, $pesanBody);
                kirimPesanCtaUrl(
                    $nomorPengirim,
                    $pesanBody,
                    WhatsappConstants::HOW_TO_ORDER_WA_BUTTON,
                    WhatsappConstants::HOW_TO_ORDER_WA_URL,
                    $pesanHeader
                );
                break;

            case 'PROMO':
                $pesanHeader = WhatsappConstants::PROMO_INFO_HEADER;
                $pesanBody = WhatsappConstants::PROMO_INFO_BODY;
                $this->saveAdminReply($conversation['id'], $nomorPengirim, $pesanBody);
                kirimPesanCtaUrl(
                    $nomorPengirim,
                    $pesanBody,
                    WhatsappConstants::PROMO_INFO_BUTTON,
                    WhatsappConstants::PROMO_INFO_URL,
                    $pesanHeader
                );
                break;

            case 'KRITIK_SARAN':
                $pesanHeader = WhatsappConstants::FEEDBACK_INFO_HEADER;
                $pesanBody = WhatsappConstants::FEEDBACK_INFO_BODY;
                $this->saveAdminReply($conversation['id'], $nomorPengirim, $pesanBody);
                kirimPesanCtaUrl(
                    $nomorPengirim,
                    $pesanBody,
                    WhatsappConstants::FEEDBACK_INFO_BUTTON,
                    WhatsappConstants::FEEDBACK_INFO_URL,
                    $pesanHeader
                );
                break;

            case 'RIWAYAT_POIN':
                $pesanHeader = WhatsappConstants::POINT_HISTORY_INFO_HEADER;
                $pesanBody = WhatsappConstants::POINT_HISTORY_INFO_BODY;
                $this->saveAdminReply($conversation['id'], $nomorPengirim, $pesanBody);
                kirimPesanCtaUrl(
                    $nomorPengirim,
                    $pesanBody,
                    WhatsappConstants::POINT_HISTORY_INFO_BUTTON,
                    WhatsappConstants::POINT_HISTORY_INFO_URL,
                    $pesanHeader
                );
                break;
            case 'INFO_JAM_BUKA':
                $pesanBody = WhatsappConstants::OPERATIONAL_HOURS_INFO;
                $this->saveAdminReply($conversation['id'], $nomorPengirim, $pesanBody);
                kirimPesanTeks($nomorPengirim, $pesanBody);
                break;
            case 'CHAT_CS':
                    $this->triggerLiveChat($nomorPengirim, $conversation);
                 break;
            default:
                if (strpos($selectedId, 'LOKASI_') === 0) {
                    $branchKey = substr($selectedId, 7);
                    if (isset(BranchConstants::ALL_LOKASI_CABANG[$branchKey])) {
                        $lokasi = BranchConstants::ALL_LOKASI_CABANG[$branchKey];
                        $pesanLokasi = sprintf(WhatsappConstants::SENDING_LOCATION_NOTICE, $lokasi['name']);
                        $this->saveAdminReply($conversation['id'], $nomorPengirim, $pesanLokasi);
                        kirimPesanLokasi($nomorPengirim, $lokasi['latitude'], $lokasi['longitude'], $lokasi['name'], $lokasi['address']);
                    } else {
                        $pesanError = sprintf(WhatsappConstants::INVALID_LOCATION_DATA, $branchKey);
                        $this->saveAdminReply($conversation['id'], $nomorPengirim, $pesanError);
                        kirimPesanTeks($nomorPengirim, $pesanError);
                    }
                } elseif (array_key_exists($selectedId, BranchConstants::ALL_NOMOR_TELEPON)) {
                    $namaKontak = "Asoka Baby Store " . $selectedId;
                    $nomorUntukDikirim = BranchConstants::ALL_NOMOR_TELEPON[$selectedId];
                    $pesanKontak = sprintf(WhatsappConstants::SENDING_CONTACT_NOTICE, $namaKontak, $nomorUntukDikirim);
                    $this->saveAdminReply($conversation['id'], $nomorPengirim, $pesanKontak);
                    kirimPesanKontak($nomorPengirim, $namaKontak, $nomorUntukDikirim);
                } else {
                    $pesanError = WhatsappConstants::INVALID_OPTION;
                    $this->saveAdminReply($conversation['id'], $nomorPengirim, $pesanError);
                    kirimPesanTeks($nomorPengirim, $pesanError);
                }
                break;
        }
    }

    private function sendBranchListByRegion($nomorPengirim, $region, $page = 1, $type = 'kontak', $namaPengirim) {
        // $this->logger->info("Sending {$type} list for region {$region} page {$page} to {$nomorPengirim}.");
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
            $cities_to_show[] = ['id' => "{$page_prefix}_PAGE_{$next_page}", 'title' => WhatsappConstants::NEXT_PAGE_TEXT];
        }
        if ($page > 1) {
            $prev_page = $page - 1;
            $cities_to_show[] = ['id' => "{$page_prefix}_PAGE_{$prev_page}", 'title' => WhatsappConstants::PREV_PAGE_TEXT];
        }
        $title = sprintf(WhatsappConstants::BRANCH_LIST_TITLE, $page);
        $header = ($region === 'jabodetabek') ? WhatsappConstants::BRANCH_LIST_HEADER_JABODETABEK : WhatsappConstants::BRANCH_LIST_HEADER_BELITUNG;
        $sections = [['title' => $title, 'rows' => $cities_to_show]];
        $pesanBody = WhatsappConstants::BRANCH_LIST_PROMPT;
        $conversation = $this->conversationService->getOrCreateConversation($nomorPengirim, $namaPengirim);
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
        
        curl_setopt($ch, CURLOPT_TIMEOUT_MS, 1000);
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
                'message' => $savedMessage
            ]);
        }
    }
    private function sendMainMenuAsList($nomorPengirim, $conversationId) {
        // $this->logger->info("Sending Main Menu (List) to {$nomorPengirim}.");
        
        $pesanBody = WhatsappConstants::WELCOME_BODY;
        $pesanHeader = WhatsappConstants::WELCOME_HEADER;
        
        kirimPesanList(
            $nomorPengirim,
            $pesanHeader,
            $pesanBody,
            "", 
            WhatsappConstants::WELCOME_BUTTON_TEXT,
            BranchConstants::MAIN_MENU_SECTIONS
        );

        $this->saveAdminReply($conversationId, $nomorPengirim, $pesanBody);
    }

    private function triggerLiveChat($nomorPengirim, $conversation) {
        // $this->logger->info("Triggering live chat for {$nomorPengirim}.");
        date_default_timezone_set('Asia/Jakarta');
        $currentHour = (int)date('H');
        $currentMinute = (int)date('i');
        $isOutsideOperationalHours = ($currentHour < 9 || $currentHour > 16 || ($currentHour == 16 && $currentMinute > 30));

        if ($isOutsideOperationalHours) {
            $pesanBody = WhatsappConstants::CS_OUTSIDE_HOURS;
        } else {
            $pesanBody = WhatsappConstants::CS_CONNECT_SUCCESS;
        }
        
        $this->conversationService->startLiveChat($nomorPengirim);
            kirimPesanTeks($nomorPengirim, $pesanBody);
            $this->saveAdminReply($conversation['id'], $nomorPengirim, $pesanBody);
            
            $totalUnread = $this->conversationService->getTotalUnreadCount(); 
            
            $this->notifyWebSocketServer([
                'event' => 'new_live_chat', 
                'phone' => $nomorPengirim, 
                'conversation_id' => $conversation['id'],
                'total_unread_count' => $totalUnread 
            ]);
    }
}