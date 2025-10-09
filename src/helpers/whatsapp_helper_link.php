<?php
require_once __DIR__ . '/../utils/Logger.php';

function kirimLinkKonfirmasiWA($nomorPenerima, $linkKonfirmasi) {
    $logger = new AppLogger('whatsapp_link_verification.log');
    $env = parse_ini_file(__DIR__ . '/../../.env');
    $accessToken = $env['WHATSAPP_ACCESS_TOKEN'];
    $phoneNumberId = $env['WHATSAPP_PHONE_NUMBER_ID'];

    // Ubah format nomor 08xx -> 628xx
    if (substr($nomorPenerima, 0, 1) === '0') {
        $nomorPenerima = '62' . substr($nomorPenerima, 1);
    }

    $url = "https://graph.facebook.com/v18.0/{$phoneNumberId}/messages";
    
    $pesanBody = "Satu langkah lagi! Klik link di bawah ini untuk menyelesaikan proses penggantian nomor HP Anda. Link ini hanya berlaku selama 15 menit.\n\n" . $linkKonfirmasi;

    $data = [
        'messaging_product' => 'whatsapp',
        'to' => $nomorPenerima,
        'type' => 'text',
        'text' => [
            'body' => $pesanBody
        ]
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $accessToken, 'Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpcode >= 200 && $httpcode < 300) {
        $logger->success("Link konfirmasi berhasil dikirim ke {$nomorPenerima}.");
        return ['success' => true];
    } else {
        $logger->error("Gagal kirim link konfirmasi ke {$nomorPenerima}. HTTP: {$httpcode}. Response: {$response}");
        return ['success' => false];
    }
}