<?php


require_once __DIR__ . '/../constant/BranchConstants.php';


use Asoka\Constant\BranchConstants;

class WebhookHandler {
    private $logger;
    private $verificationService;
    

    public function __construct(VerificationService $verificationService, $logger) {
        $this->verificationService = $verificationService;
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
        
        if ($message['type'] === 'interactive' && isset($message['interactive']['type']) && $message['interactive']['type'] === 'list_reply') {
            $this->processListReplyMessage($message);
        } elseif ($message['type'] === 'text') {
            $this->processTextMessage($message);
        }
    }
    
    private function processTextMessage($message) {
        $nomorPengirim = $message['from'];
        $this->logger->info("User {$nomorPengirim} sent a text message, triggering Main Menu List.");

        
        $sections = BranchConstants::MAIN_MENU_SECTIONS;

        kirimPesanList(
            $nomorPengirim,
            "Selamat Datang di Asoka!",
            "Halo Kak! Ada yang bisa kami bantu? Silakan pilih menu di bawah ini ya.",
            "ASOKA Baby Store",
            "Lihat Pilihan Menu",
            $sections
        );
    }
    
    private function processListReplyMessage($message) {
        $nomorPengirim = $message['from'];
        $selectedId = $message['interactive']['list_reply']['id'];
        $this->logger->info("Received List reply from {$nomorPengirim}. Selected ID: {$selectedId}");

        
        if (preg_match('/^(JABODETABEK|BELITUNG|LOKASI_JABODETABEK|LOKASI_BELITUNG)_PAGE_(\d+)$/', $selectedId, $matches)) {
            $region = (strpos($matches[1], 'JABODETABEK') !== false) ? 'jabodetabek' : 'belitung';
            $type = (strpos($matches[1], 'LOKASI') !== false) ? 'lokasi' : 'kontak';
            $page = (int)$matches[2];
            $this->sendBranchListByRegion($nomorPengirim, $region, $page, $type);
            return;
        }

        
        switch ($selectedId) {
            case 'DAFTAR_NOMOR':
                kirimPesanList(
                    $nomorPengirim,
                    "Pilih Wilayah Cabang",
                    "Silakan pilih wilayah cabang yang ingin Anda hubungi.",
                    "Pilihan Wilayah",
                    "Lihat Wilayah",
                    BranchConstants::REGION_SELECTION_MENU
                );
                break;

            case 'DAFTAR_JABODETABEK':
                $this->sendBranchListByRegion($nomorPengirim, 'jabodetabek', 1, 'kontak');
                break;

            case 'DAFTAR_BELITUNG':
                $this->sendBranchListByRegion($nomorPengirim, 'belitung', 1, 'kontak');
                break;

            case 'DAFTAR_LOKASI':
                kirimPesanList(
                    $nomorPengirim,
                    "Pilih Wilayah Toko",
                    "Silakan pilih wilayah toko fisik yang ingin Anda lihat lokasinya.",
                    "Pilihan Wilayah",
                    "Lihat Wilayah",
                    BranchConstants::REGION_SELECTION_MENU_LOKASI
                );
                break;

            case 'LOKASI_DAFTAR_JABODETABEK':
                $this->sendBranchListByRegion($nomorPengirim, 'jabodetabek', 1, 'lokasi');
                break;

            case 'LOKASI_DAFTAR_BELITUNG':
                $this->sendBranchListByRegion($nomorPengirim, 'belitung', 1, 'lokasi');
                break;

            default:
                
                if (strpos($selectedId, 'LOKASI_') === 0) {
                    $branchKey = substr($selectedId, 7);
                    if (isset(BranchConstants::ALL_LOKASI_CABANG[$branchKey])) {
                        $lokasi = BranchConstants::ALL_LOKASI_CABANG[$branchKey];
                        kirimPesanLokasi($nomorPengirim, $lokasi['latitude'], $lokasi['longitude'], $lokasi['name'], $lokasi['address']);
                    } else {
                        kirimPesanTeks($nomorPengirim, "Maaf, data lokasi untuk cabang {$branchKey} saat ini belum tersedia.");
                    }
                } 
                
                elseif (array_key_exists($selectedId, BranchConstants::ALL_NOMOR_TELEPON)) {
                    $namaKontak = "Asoka " . $selectedId;
                    $nomorUntukDikirim = BranchConstants::ALL_NOMOR_TELEPON[$selectedId];
                    kirimPesanKontak($nomorPengirim, $namaKontak, $nomorUntukDikirim);
                } 
                
                else {
                    $this->logger->warning("Received unhandled List reply ID: {$selectedId}");
                    kirimPesanTeks($nomorPengirim, "Maaf, pilihan Anda tidak valid. Silakan coba lagi.");
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

        kirimPesanList(
            $nomorPengirim,
            "Pilih Cabang",
            "Silakan pilih cabang yang Anda tuju.",
            $header,
            "Pilih Cabang",
            $sections
        );
    }

}