<?php
require_once __DIR__ . "/../aa_kon_sett.php";
require_once __DIR__ . "/../src/helpers/whatsapp_helper_link.php";
require_once __DIR__ . "/../src/utils/Logger.php";
date_default_timezone_set('Asia/Jakarta');
$logger = new AppLogger('cron_live_chat_closer.log');
$logger->info("Cron job penutup live chat dimulai...");
$sql = "SELECT nomor_telepon FROM wa_percakapan WHERE status_percakapan = 'live_chat'";
$result = mysqli_query($conn, $sql);
if (!$result) {
    $logger->error("Error query: " . mysqli_error($conn));
    exit;
}
$pesanPenutup = "Sesi live chat Anda telah kami tutup secara otomatis karena telah melewati jam operasional. Jika ada hal lain yang ingin ditanyakan, silakan hubungi kami kembali esok hari. Terima kasih.";
$count = 0;
while ($row = mysqli_fetch_assoc($result)) {
    $nomorTelepon = $row['nomor_telepon'];
    $logger->info("Menutup sesi live chat untuk nomor: " . $nomorTelepon);
    kirimPesanTeks($nomorTelepon, $pesanPenutup);
    $updateStmt = $conn->prepare("UPDATE wa_percakapan SET status_percakapan = 'closed', menu_utama_terkirim = 0 WHERE nomor_telepon = ?");
    $updateStmt->bind_param("s", $nomorTelepon);
    $updateStmt->execute();
    $updateStmt->close();
    $count++;
}
$logger->info("Cron job selesai. Menutup " . $count . " sesi live chat.");
mysqli_close($conn);