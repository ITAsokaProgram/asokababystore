<?php
require_once __DIR__ . '/../utils/Logger.php';

function sendWhatsAppMessage($data) {
    $env = parse_ini_file(__DIR__ . '/../../.env');
    $accessToken = $env['WHATSAPP_ACCESS_TOKEN'];
    $phoneNumberId = $env['WHATSAPP_PHONE_NUMBER_ID'];
    $url = "https://graph.facebook.com/v24.0/{$phoneNumberId}/messages";

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $accessToken, 'Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return ['httpcode' => $httpcode, 'response' => $response];
}

function normalizePhoneNumber($nomor) {
    if (substr($nomor, 0, 1) === '0') {
        return '62' . substr($nomor, 1);
    }
    return $nomor;
}

function kirimLinkKonfirmasiWA($nomorPenerima, $linkKonfirmasi) {
    $logger = new AppLogger('whatsapp_link_verification.log');
    $nomorPenerima = normalizePhoneNumber($nomorPenerima);
    
    $pesanBody = "Satu langkah lagi! Klik link di bawah ini untuk menyelesaikan proses penggantian nomor HP Anda. Link ini hanya berlaku selama 15 menit.\n\n" . $linkKonfirmasi;

    $data = [
        'messaging_product' => 'whatsapp',
        'to' => $nomorPenerima,
        'type' => 'text',
        'text' => ['body' => $pesanBody]
    ];

    $result = sendWhatsAppMessage($data);

    if ($result['httpcode'] >= 200 && $result['httpcode'] < 300) {
        // $logger->success("Link konfirmasi berhasil dikirim ke {$nomorPenerima}.");
        return ['success' => true];
    } else {
        $logger->error("Gagal kirim link konfirmasi ke {$nomorPenerima}. HTTP: {$result['httpcode']}. Response: {$result['response']}");
        return ['success' => false];
    }
}

function kirimPesanTeks($nomorPenerima, $pesanBody) {
    $logger = new AppLogger('whatsapp_text_message.log');
    $nomorPenerima = normalizePhoneNumber($nomorPenerima);

    $data = [
        'messaging_product' => 'whatsapp',
        'to' => $nomorPenerima,
        'type' => 'text',
        'text' => ['body' => $pesanBody]
    ];

    $result = sendWhatsAppMessage($data);

    if ($result['httpcode'] >= 200 && $result['httpcode'] < 300) {
        // $logger->success("Pesan teks berhasil dikirim ke {$nomorPenerima}.");
        return ['success' => true];
    } else {
        $logger->error("Gagal kirim pesan teks ke {$nomorPenerima}. HTTP: {$result['httpcode']}. Response: {$result['response']}");
        return ['success' => false];
    }
}

function kirimPesanKontak($nomorPenerima, $namaKontak, $nomorTeleponKontak) {
    $logger = new AppLogger('whatsapp_contact_message.log');
    $nomorPenerima = normalizePhoneNumber($nomorPenerima);
    $nomorTeleponBersih = preg_replace('/[^0-9]/', '', $nomorTeleponKontak);

    $data = [
        'messaging_product' => 'whatsapp',
        'to' => $nomorPenerima,
        'type' => 'contacts',
        'contacts' => [[
            'name' => [
                'formatted_name' => $namaKontak,
                'first_name' => $namaKontak
            ],
            'phones' => [[
                'phone' => $nomorTeleponBersih,
                'type' => 'Mobile', 
                'wa_id' => $nomorTeleponBersih 
            ]]
        ]]
    ];

    $result = sendWhatsAppMessage($data);

    if ($result['httpcode'] >= 200 && $result['httpcode'] < 300) {
        // $logger->success("Pesan kontak '{$namaKontak}' berhasil dikirim ke {$nomorPenerima}.");
        return ['success' => true];
    } else {
        $logger->error("Gagal kirim pesan kontak ke {$nomorPenerima}. HTTP: {$result['httpcode']}. Response: {$result['response']}");
        return ['success' => false];
    }
}

function kirimPesanList($nomorPenerima, $judulHeader, $pesanBody, $pesanFooter, $namaTombol, $sections) {
    $logger = new AppLogger('whatsapp_list_message.log');
    $nomorPenerima = normalizePhoneNumber($nomorPenerima);

    $data = [
        'messaging_product' => 'whatsapp',
        'to' => $nomorPenerima,
        'type' => 'interactive',
        'interactive' => [
            'type' => 'list',
            'header' => ['type' => 'text', 'text' => $judulHeader],
            'body' => ['text' => $pesanBody],
            'footer' => ['text' => $pesanFooter],
            'action' => [
                'button' => $namaTombol,
                'sections' => $sections
            ]
        ]
    ];

    $result = sendWhatsAppMessage($data);

    if ($result['httpcode'] >= 200 && $result['httpcode'] < 300) {
        // $logger->success("Pesan List berhasil dikirim ke {$nomorPenerima}.");
        return ['success' => true];
    } else {
        $logger->error("Gagal kirim pesan List ke {$nomorPenerima}. HTTP: {$result['httpcode']}. Response: {$result['response']}");
        return ['success' => false];
    }
}


function kirimPesanButton($nomorPenerima, $pesanBody, $buttons, $pesanHeader = null, $pesanFooter = null) {
    $logger = new AppLogger('whatsapp_button_message.log');
    $nomorPenerima = normalizePhoneNumber($nomorPenerima);

    $actionButtons = [];
    foreach ($buttons as $button) {
        $actionButtons[] = ['type' => 'reply', 'reply' => ['id' => $button['id'], 'title' => $button['title']]];
    }

    $interactiveData = [
        'type' => 'button',
        'body' => ['text' => $pesanBody],
        'action' => ['buttons' => $actionButtons]
    ];

    if ($pesanHeader) {
        $interactiveData['header'] = ['type' => 'text', 'text' => $pesanHeader];
    }
    if ($pesanFooter) {
        $interactiveData['footer'] = ['text' => $pesanFooter];
    }
    
    $data = [
        'messaging_product' => 'whatsapp',
        'to' => $nomorPenerima,
        'type' => 'interactive',
        'interactive' => $interactiveData
    ];

    $result = sendWhatsAppMessage($data);

    if ($result['httpcode'] >= 200 && $result['httpcode'] < 300) {
        // $logger->success("Pesan Button berhasil dikirim ke {$nomorPenerima}.");
        return ['success' => true];
    } else {
        $logger->error("Gagal kirim pesan Button ke {$nomorPenerima}. HTTP: {$result['httpcode']}. Response: {$result['response']}");
        return ['success' => false];
    }
}

function kirimPesanLokasi($nomorPenerima, $latitude, $longitude, $namaLokasi, $alamatLokasi) {
    $logger = new AppLogger('whatsapp_location_message.log');
    $nomorPenerima = normalizePhoneNumber($nomorPenerima);

    $data = [
        'messaging_product' => 'whatsapp',
        'to' => $nomorPenerima,
        'type' => 'location',
        'location' => [
            'latitude' => $latitude,
            'longitude' => $longitude,
            'name' => $namaLokasi,
            'address' => $alamatLokasi
        ]
    ];

    $result = sendWhatsAppMessage($data);

    if ($result['httpcode'] >= 200 && $result['httpcode'] < 300) {
        // $logger->success("Pesan lokasi '{$namaLokasi}' berhasil dikirim ke {$nomorPenerima}.");
        return ['success' => true];
    } else {
        $logger->error("Gagal kirim pesan lokasi ke {$nomorPenerima}. HTTP: {$result['httpcode']}. Response: {$result['response']}");
        return ['success' => false];
    }
}

function kirimPesanMedia($nomorPenerima, $mediaUrl, $mediaType, $caption = null) {
    $logger = new AppLogger('whatsapp_media_message.log');
    $nomorPenerima = normalizePhoneNumber($nomorPenerima);

    $mediaObject = ['link' => $mediaUrl];
    if ($caption) {
        $mediaObject['caption'] = $caption;
    }

    $data = [
        'messaging_product' => 'whatsapp',
        'to' => $nomorPenerima,
        'type' => $mediaType, 
        $mediaType => $mediaObject
    ];

    $result = sendWhatsAppMessage($data);

    if ($result['httpcode'] >= 200 && $result['httpcode'] < 300) {
        // $logger->success("Pesan media ({$mediaType}) berhasil dikirim ke {$nomorPenerima}.");
        return ['success' => true];
    } else {
        $logger->error("Gagal kirim pesan media ({$mediaType}) ke {$nomorPenerima}. HTTP: {$result['httpcode']}. Response: {$result['response']}");
        return ['success' => false];
    }
}

function kirimPesanCtaUrl($nomorPenerima, $pesanBody, $displayText, $url, $pesanHeader = null, $pesanFooter = null) {
    $logger = new AppLogger('whatsapp_cta_url_message.log');
    $nomorPenerima = normalizePhoneNumber($nomorPenerima);

    $interactiveData = [
        'type' => 'cta_url',
        'body' => ['text' => $pesanBody],
        'action' => [
            'name' => 'cta_url',
            'parameters' => [
                'display_text' => substr($displayText, 0, 20), 
                'url' => $url
            ]
        ]
    ];

    if ($pesanHeader) {
        $interactiveData['header'] = ['type' => 'text', 'text' => substr($pesanHeader, 0, 60)];
    }
    if ($pesanFooter) {
        $interactiveData['footer'] = ['text' => substr($pesanFooter, 0, 60)];
    }
    
    $data = [
        'messaging_product' => 'whatsapp',
        'to' => $nomorPenerima,
        'type' => 'interactive',
        'interactive' => $interactiveData
    ];

    $result = sendWhatsAppMessage($data);

    if ($result['httpcode'] >= 200 && $result['httpcode'] < 300) {
        // $logger->success("Pesan CTA URL '{$displayText}' berhasil dikirim ke {$nomorPenerima}.");
        return ['success' => true];
    } else {
        $logger->error("Gagal kirim pesan CTA URL ke {$nomorPenerima}. HTTP: {$result['httpcode']}. Response: {$result['response']}");
        return ['success' => false];
    }
}