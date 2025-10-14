<?php

require_once __DIR__ . "/../aa_kon_sett.php";
require_once __DIR__ . "/../src/helpers/whatsapp_helper_link.php";
require_once __DIR__ . "/../src/utils/Logger.php";

$logger = new AppLogger('tutup_percakapan_otomatis.log');
$logger->info("Cron job dimulai...");

$timeoutDuration = '10 SECOND'; 
// $timeoutDuration = '30 MINUTE'; 

$sql = "SELECT nomor_telepon FROM percakapan_whatsapp 
        WHERE status_percakapan = 'open' 
        AND terakhir_interaksi_pada < NOW() - INTERVAL $timeoutDuration";

$result = mysqli_query($conn, $sql);

if (!$result) {
    $logger->error("Error query: " . mysqli_error($conn));
    exit;
}

$pesanPenutup = "Mohon maaf Bapak/Ibu karena tidak ada respon yang Kami terima sampai dengan saat ini, maka chat ini akan kami akhiri. Jika ada hal lain yang ingin ditanyakan, Bapak/Ibu dapat menghubungi kami kembali. Dengan senang hati kami akan membantu. Terima kasih";

$count = 0;
while ($row = mysqli_fetch_assoc($result)) {
    $nomorTelepon = $row['nomor_telepon'];
    
    $logger->info("Menutup sesi untuk nomor: " . $nomorTelepon);

    kirimPesanTeks($nomorTelepon, $pesanPenutup);

    $updateStmt = $conn->prepare("UPDATE percakapan_whatsapp SET status_percakapan = 'closed', menu_utama_terkirim = 0 WHERE nomor_telepon = ?");
    $updateStmt->bind_param("s", $nomorTelepon);
    $updateStmt->execute();
    $updateStmt->close();
    
    $count++;
}

$logger->info("Cron job selesai. Menutup " . $count . " sesi.");

mysqli_close($conn);