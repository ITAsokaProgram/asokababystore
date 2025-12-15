<?php
require_once __DIR__ . '/../config/Config.php';
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../constant/BranchConstants.php';
use Asoka\Constant\BranchConstants;
use Cloudinary\Cloudinary;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;
class DynamicFlowService
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
    public function getActiveSession($nomorPengirim)
    {
        $stmt = $this->conn->prepare("SELECT * FROM wa_flow_sessions WHERE nomor_telepon = ? AND status = 'active'");
        $stmt->bind_param("s", $nomorPengirim);
        $stmt->execute();
        $result = $stmt->get_result();
        $session = $result->fetch_assoc();
        $stmt->close();
        return $session;
    }
    public function checkKeywordTrigger($keyword)
    {
        $stmt = $this->conn->prepare("SELECT * FROM wa_flows WHERE keyword = ? AND status_aktif = 1");
        $stmt->bind_param("s", $keyword);
        $stmt->execute();
        $result = $stmt->get_result();
        $flow = $result->fetch_assoc();
        $stmt->close();
        return $flow;
    }
    public function startFlow($conversation, $flow)
    {
        $nomor = $conversation['nomor_telepon'];
        $limitCheck = $this->checkFlowLimits($flow, $nomor);
        if ($limitCheck['allowed'] === false) {
            $this->sendDefaultReply($conversation, $limitCheck['reason']);
            return;
        }
        $this->conn->query("UPDATE wa_flow_sessions SET status = 'cancelled' WHERE nomor_telepon = '$nomor' AND status = 'active'");
        $stmt = $this->conn->prepare("INSERT INTO wa_flow_sessions (percakapan_id, nomor_telepon, flow_id, step_sekarang, data_terkumpul) VALUES (?, ?, ?, 1, '{}')");
        $stmt->bind_param("isi", $conversation['id'], $nomor, $flow['id']);
        $stmt->execute();
        $sessionId = $stmt->insert_id;
        $stmt->close();
        $this->executeStep($conversation, $flow['id'], 1, $sessionId);
        $this->checkAndAutoComplete($conversation, $flow['id'], 1, $sessionId);
    }
    public function handleFlowMessage($conversation, $message, $session)
    {
        $nomor = $conversation['nomor_telepon'];
        $flowId = $session['flow_id'];
        $currentStepOrder = $session['step_sekarang'];
        $currentStepConfig = $this->getStepConfig($flowId, $currentStepOrder);
        if (!$currentStepConfig) {
            $this->cancelSession($session['id'], $nomor, "Terjadi kesalahan sistem: Step tidak ditemukan.");
            return;
        }
        if (isset($message['text']['body'])) {
            $text = strtolower(trim($message['text']['body']));
            if ($text === 'batal' || $text === 'cancel') {
                $this->cancelSession($session['id'], $nomor, "Sesi dibatalkan atas permintaan Anda.");
                return;
            }
        }
        $validationResult = $this->validateAndExtractInput($message, $currentStepConfig['tipe_respon']);
        if ($validationResult['valid'] === false) {
            $this->sendDefaultReply($conversation, $validationResult['error_msg']);
            return;
        }
        $inputData = $validationResult['data'];
        if ($currentStepConfig['tipe_respon'] === 'location_request' && is_array($inputData)) {
            $stepConfig = json_decode($currentStepConfig['isi_pesan'], true);
            if (isset($stepConfig['calc_nearest']) && $stepConfig['calc_nearest'] === true) {
                $nearby = $this->findNearbyBranches($inputData['lat'], $inputData['long'], 5);
                if (!empty($nearby)) {
                    $nearest = $nearby[0];
                    $inputData['nearest_branch_name'] = $nearest['name'];
                    $inputData['nearest_branch_address'] = $nearest['address'];
                    $inputData['nearest_branch_distance'] = number_format($nearest['distance'], 1) . " km";
                    $inputData['nearby_branches_list'] = $nearby;
                } else {
                    $inputData['nearest_branch_name'] = "Tidak ditemukan";
                    $inputData['nearest_branch_distance'] = "-";
                    $inputData['nearby_branches_list'] = [];
                }
            }
        }
        if ($currentStepConfig['tipe_respon'] === 'button' && strtolower($inputData) === 'tidak') {
            $this->cancelSession($session['id'], $nomor, "Baik, proses dibatalkan.");
            return;
        }
        if (!empty($currentStepConfig['key_penyimpanan']) && $inputData !== 'next') {
            $currentData = json_decode($session['data_terkumpul'], true) ?? [];
            if (is_array($inputData)) {
                $currentData[$currentStepConfig['key_penyimpanan']] = $inputData;
                if (isset($inputData['nearest_branch_name'])) {
                    $currentData['nearest_branch_name'] = $inputData['nearest_branch_name'];
                    $currentData['nearest_branch_distance'] = $inputData['nearest_branch_distance'];
                }
            } else {
                $currentData[$currentStepConfig['key_penyimpanan']] = $inputData;
            }
            $this->updateSessionData($session['id'], $currentData);
        }
        $nextStepOrder = $currentStepOrder + 1;
        $nextStepConfig = $this->getStepConfig($flowId, $nextStepOrder);
        if ($nextStepConfig) {
            $this->updateSessionStep($session['id'], $nextStepOrder);
            $this->executeStep($conversation, $flowId, $nextStepOrder, $session['id']);
            $this->checkAndAutoComplete($conversation, $flowId, $nextStepOrder, $session['id']);
        } else {
            $this->completeSession($session['id']);
        }
    }
    private function checkAndAutoComplete($conversation, $flowId, $currentStep, $sessionId)
    {
        $stepConfig = $this->getStepConfig($flowId, $currentStep);
        $outputOnlyTypes = ['text', 'generated_qr', 'media', 'cta_url'];
        if ($stepConfig && in_array($stepConfig['tipe_respon'], $outputOnlyTypes)) {
            $nextStepOrder = $currentStep + 1;
            $nextStepConfig = $this->getStepConfig($flowId, $nextStepOrder);
            if ($nextStepConfig) {
                $this->updateSessionStep($sessionId, $nextStepOrder);
                $this->executeStep($conversation, $flowId, $nextStepOrder, $sessionId);
                $this->checkAndAutoComplete($conversation, $flowId, $nextStepOrder, $sessionId);
            } else {
                $this->completeSession($sessionId);
            }
        }
    }
    private function validateAndExtractInput($message, $expectedType)
    {
        $msgType = $message['type'];
        if ($expectedType === 'location_request') {
            if ($msgType === 'location') {
                return ['valid' => true, 'data' => ['lat' => $message['location']['latitude'], 'long' => $message['location']['longitude']]];
            }
            return ['valid' => false, 'error_msg' => "Kami menunggu *Share Location* Anda. Klik tombol kirim lokasi atau ketik 'batal'."];
        }
        if ($expectedType === 'button') {
            if ($msgType === 'interactive' && isset($message['interactive']['button_reply'])) {
                return ['valid' => true, 'data' => $message['interactive']['button_reply']['title']];
            }
            return ['valid' => false, 'error_msg' => "Silakan pilih salah satu *Tombol* di atas atau ketik 'batal'."];
        }
        if ($expectedType === 'list') {
            if ($msgType === 'interactive' && isset($message['interactive']['list_reply'])) {
                return ['valid' => true, 'data' => $message['interactive']['list_reply']['title']];
            }
            return ['valid' => false, 'error_msg' => "Silakan pilih opsi dari *Menu Daftar* di atas atau ketik 'batal'."];
        }
        if ($expectedType === 'save_input') {
            if ($msgType === 'text') {
                return ['valid' => true, 'data' => $message['text']['body']];
            }
            return ['valid' => false, 'error_msg' => "Mohon balas dengan pesan teks."];
        }
        if (in_array($expectedType, ['text', 'generated_qr', 'media', 'cta_url'])) {
            return ['valid' => true, 'data' => 'next'];
        }
        return ['valid' => false, 'error_msg' => "Input tidak dikenali."];
    }
    private function executeStep($conversation, $flowId, $stepOrder, $sessionId = null)
    {
        $step = $this->getStepConfig($flowId, $stepOrder);
        if (!$step)
            return;
        $nomor = $conversation['nomor_telepon'];
        $isiPesan = json_decode($step['isi_pesan'], true);
        if (!is_array($isiPesan)) {
            $this->logger->error("JSON Invalid pada Flow Step ID: " . $step['id']);
            $isiPesan = ['body' => 'Terjadi kesalahan konfigurasi pesan.'];
        }
        $wamid = null;
        $messageContent = '';
        $tipePesanDB = 'text';
        if ($sessionId) {
            $sessionData = $this->getSessionData($sessionId);
            $isiPesan = $this->replacePlaceholders($isiPesan, $sessionData);
            $userData = json_decode($sessionData['data_terkumpul'], true) ?? [];
            $nearbyBranches = [];
            if (!empty($userData['nearby_branches_list'])) {
                $nearbyBranches = $userData['nearby_branches_list'];
            } else {
                foreach ($userData as $key => $val) {
                    if (is_array($val) && !empty($val['nearby_branches_list'])) {
                        $nearbyBranches = $val['nearby_branches_list'];
                        break;
                    }
                }
            }
            if ($step['tipe_respon'] === 'list' && !empty($nearbyBranches)) {
                $dynamicRows = [];
                foreach ($nearbyBranches as $branch) {
                    $jarak = number_format($branch['distance'], 1) . " km";
                    $namaToko = substr($branch['name'], 0, 23);
                    $alamat = substr($branch['address'], 0, 60);
                    $dynamicRows[] = [
                        'id' => $branch['key'],
                        'title' => $namaToko,
                        'description' => "{$jarak} - {$alamat}"
                    ];
                }
                $isiPesan['sections'] = [
                    [
                        'title' => 'Cabang Terdekat',
                        'rows' => $dynamicRows
                    ]
                ];
                if (empty($isiPesan['btn_text']))
                    $isiPesan['btn_text'] = "Pilih Toko";
                if (empty($isiPesan['body']))
                    $isiPesan['body'] = "Silakan pilih lokasi di bawah:";
            }
        }
        switch ($step['tipe_respon']) {
            case 'text':
            case 'save_input':
                $body = is_array($isiPesan) ? ($isiPesan['body'] ?? '') : $isiPesan;
                $res = kirimPesanTeks($nomor, $body);
                $wamid = $res['wamid'] ?? null;
                $messageContent = $body;
                break;
            case 'button':
                $res = kirimPesanButton($nomor, $isiPesan['body'] ?? '', $isiPesan['buttons'] ?? [], $isiPesan['header'] ?? '', $isiPesan['footer'] ?? '');
                $wamid = $res['wamid'] ?? null;
                $messageContent = $isiPesan['body'] ?? '';
                break;
            case 'location_request':
                $body = $isiPesan['body'] ?? 'Mohon kirimkan lokasi Anda.';
                $res = kirimPesanRequestLokasi($nomor, $body);
                $wamid = $res['wamid'] ?? null;
                $messageContent = $body;
                break;
            case 'list':
                $headerText = $isiPesan['header'] ?? '';
                if (is_array($headerText) && isset($headerText['text'])) {
                    $headerText = $headerText['text'];
                } elseif (is_array($headerText)) {
                    $headerText = '';
                }
                $res = kirimPesanList(
                    $nomor,
                    $headerText,
                    $isiPesan['body'] ?? '',
                    $isiPesan['footer'] ?? '',
                    $isiPesan['btn_text'] ?? 'Menu',
                    $isiPesan['sections'] ?? []
                );
                $wamid = $res['wamid'] ?? null;
                $messageContent = $isiPesan['body'] ?? '';
                break;
            case 'generated_qr':
                $qrContent = $isiPesan['qr_data'] ?? 'No Data';
                $caption = $isiPesan['caption'] ?? '';
                $qrUrl = $this->generateAndUploadQrCode($sessionId ?? time(), $qrContent);
                if ($qrUrl) {
                    $res = kirimPesanMedia($nomor, $qrUrl, 'image', $caption);
                    $wamid = $res['wamid'] ?? null;
                    $messageContent = "QR Code: $qrContent";
                    $tipePesanDB = 'image';
                }
                break;
            case 'cta_url':
                $body = $isiPesan['body'] ?? '';
                $btnText = $isiPesan['display_text'] ?? 'Buka Link';
                $url = $isiPesan['url'] ?? 'https://google.com';
                $footer = $isiPesan['footer'] ?? '';

                // Siapkan Header
                $header = null;
                if (!empty($isiPesan['header_type'])) {
                    if ($isiPesan['header_type'] === 'text') {
                        $header = $isiPesan['header_content'] ?? '';
                    } elseif (in_array($isiPesan['header_type'], ['image', 'video'])) {
                        // Pastikan URL valid
                        $header = [
                            'type' => $isiPesan['header_type'],
                            'link' => $isiPesan['header_content'] ?? ''
                        ];
                    }
                }

                $res = kirimPesanCtaUrl($nomor, $body, $btnText, $url, $header, $footer);
                $wamid = $res['wamid'] ?? null;
                $messageContent = "CTA: $btnText | $url";
                $tipePesanDB = 'interactive';
                break;
            case 'media':
                $url = $isiPesan['url'] ?? '';
                $type = $isiPesan['type'] ?? 'image';
                $caption = $isiPesan['caption'] ?? '';

                if ($url) {
                    // ganti jadi jpg ext nya
                    if ($type === 'image' && strpos($url, 'cloudinary.com') !== false) {
                        $url = preg_replace('/\.(webp|png|jpeg)$/i', '.jpg', $url);
                    }

                    $res = kirimPesanMedia($nomor, $url, $type, $caption);
                    $wamid = $res['wamid'] ?? null;
                    $messageContent = $url;
                    $tipePesanDB = $type;
                }
                break;
        }
        if ($wamid) {
            $this->saveAdminMessage($conversation['id'], $tipePesanDB, $messageContent, $wamid);
        }
    }
    private function checkFlowLimits($flow, $nomorTelepon)
    {
        if (!empty($flow['expired_at'])) {
            if (new DateTime() > new DateTime($flow['expired_at']))
                return ['allowed' => false, 'reason' => "Maaf, periode promo berakhir."];
        }
        if ($flow['max_global_usage'] > 0 && $flow['current_global_usage'] >= $flow['max_global_usage'])
            return ['allowed' => false, 'reason' => $flow['pesan_habis'] ?? "Kuota habis."];
        if ($flow['max_user_usage'] > 0) {
            $stmt = $this->conn->prepare("SELECT COUNT(*) as cnt FROM wa_flow_sessions WHERE flow_id = ? AND nomor_telepon = ? AND status = 'completed'");
            $stmt->bind_param("is", $flow['id'], $nomorTelepon);
            $stmt->execute();
            $cnt = $stmt->get_result()->fetch_assoc()['cnt'];
            $stmt->close();
            if ($cnt >= $flow['max_user_usage'])
                return ['allowed' => false, 'reason' => $flow['pesan_sudah_klaim'] ?? "Sudah pernah klaim."];
        }
        return ['allowed' => true];
    }
    private function replacePlaceholders($isiPesan, $sessionData)
    { /* ... sama ... */
        if (empty($sessionData['data_terkumpul']))
            return $isiPesan;
        $userData = json_decode($sessionData['data_terkumpul'], true);
        if (!$userData)
            return $isiPesan;
        $replacer = function ($item) use ($userData) {
            if (is_string($item)) {
                foreach ($userData as $key => $val) {
                    if (is_string($val) || is_numeric($val))
                        $item = str_replace("{{" . $key . "}}", $val, $item);
                }
                return $item;
            }
            return $item;
        };
        if (is_array($isiPesan)) {
            array_walk_recursive($isiPesan, function (&$value) use ($replacer) {
                $value = $replacer($value);
            });
            return $isiPesan;
        }
        return $replacer($isiPesan);
    }
    private function generateAndUploadQrCode($sessionId, $qrData)
    { /* ... sama ... */
        try {
            $result = Builder::create()->writer(new PngWriter())->data($qrData)->size(300)->margin(10)->build();
            $base64Data = 'data:image/png;base64,' . base64_encode($result->getString());
            return $this->cloudinary->uploadApi()->upload($base64Data, ['folder' => 'whatsapp_dynamic_flows', 'public_id' => 'flow_qr_' . $sessionId . '_' . time(), 'resource_type' => 'image'])['secure_url'];
        } catch (Exception $e) {
            $this->logger->error("QR Gen Error: " . $e->getMessage());
            return null;
        }
    }
    private function getSessionData($sessionId)
    { /* ... sama ... */
        $stmt = $this->conn->prepare("SELECT data_terkumpul FROM wa_flow_sessions WHERE id = ?");
        $stmt->bind_param("i", $sessionId);
        $stmt->execute();
        $res = $stmt->get_result();
        $data = $res->fetch_assoc();
        $stmt->close();
        return $data;
    }
    private function getStepConfig($flowId, $order)
    { /* ... sama ... */
        $stmt = $this->conn->prepare("SELECT * FROM wa_flow_steps WHERE flow_id = ? AND urutan = ?");
        $stmt->bind_param("ii", $flowId, $order);
        $stmt->execute();
        $res = $stmt->get_result();
        $data = $res->fetch_assoc();
        $stmt->close();
        return $data;
    }
    private function updateSessionStep($sessionId, $newStep)
    { /* ... sama ... */
        $stmt = $this->conn->prepare("UPDATE wa_flow_sessions SET step_sekarang = ? WHERE id = ?");
        $stmt->bind_param("ii", $newStep, $sessionId);
        $stmt->execute();
        $stmt->close();
    }
    private function updateSessionData($sessionId, $dataArray)
    { /* ... sama ... */
        $json = json_encode($dataArray);
        $stmt = $this->conn->prepare("UPDATE wa_flow_sessions SET data_terkumpul = ? WHERE id = ?");
        $stmt->bind_param("si", $json, $sessionId);
        $stmt->execute();
        $stmt->close();
    }
    private function completeSession($sessionId)
    { /* ... sama ... */
        $this->conn->query("UPDATE wa_flow_sessions SET status = 'completed' WHERE id = $sessionId");
        $stmt = $this->conn->prepare("SELECT flow_id FROM wa_flow_sessions WHERE id = ?");
        $stmt->bind_param("i", $sessionId);
        $stmt->execute();
        $data = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if ($data)
            $this->conn->query("UPDATE wa_flows SET current_global_usage = current_global_usage + 1 WHERE id = {$data['flow_id']}");
    }
    private function cancelSession($sessionId, $nomor, $reason)
    { /* ... sama ... */
        $this->conn->query("UPDATE wa_flow_sessions SET status = 'cancelled' WHERE id = $sessionId");
        $this->sendDefaultReply(['nomor_telepon' => $nomor, 'id' => $sessionId], $reason);
    }
    private function sendDefaultReply($conversation, $pesanBody)
    { /* ... sama ... */
        $sendResult = kirimPesanTeks($conversation['nomor_telepon'], $pesanBody);
        $this->saveAdminMessage($conversation['id'] ?? 0, 'text', $pesanBody, $sendResult['wamid'] ?? null);
    }
    private function saveAdminMessage($conversationId, $type, $content, $wamid)
    { /* ... sama ... */
        $pengirim = 'admin';
        $status = $wamid ? 'sent' : null;
        $isBot = 1;
        $stmt = $this->conn->prepare("INSERT INTO wa_pesan (percakapan_id, pengirim, tipe_pesan, isi_pesan, wamid, status_pengiriman, dikirim_oleh_bot, timestamp) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("isssssi", $conversationId, $pengirim, $type, $content, $wamid, $status, $isBot);
        $stmt->execute();
        $stmt->close();
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
        return array_values(array_slice($distances, 0, $limit));
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
}