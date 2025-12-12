<?php
require_once __DIR__ . '/src/config/Config.php';
$accessToken = Config::get('WHATSAPP_ACCESS_TOKEN');
$phoneNumberId = Config::get('WHATSAPP_PHONE_NUMBER_ID');
$publicKeyPath = __DIR__ . '/' . Config::get('WHATSAPP_PUBLIC_KEY_PATH');
if (!file_exists($publicKeyPath)) {
    die("Error: File Public Key tidak ditemukan di: " . $publicKeyPath);
}
$publicKeyContent = file_get_contents($publicKeyPath);
$url = "https://graph.facebook.com/v21.0/{$phoneNumberId}/whatsapp_business_encryption";
$data = [
    'business_public_key' => $publicKeyContent
];
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $accessToken,
    'Content-Type: application/x-www-form-urlencoded'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);
echo "<h1>Status Upload Key</h1>";
if ($httpCode == 200) {
    echo "<h3 style='color:green'>SUKSES! Key berhasil diupload.</h3>";
} else {
    echo "<h3 style='color:red'>GAGAL! (HTTP $httpCode)</h3>";
    echo "Response: <pre>" . htmlspecialchars($response) . "</pre>";
}
?>