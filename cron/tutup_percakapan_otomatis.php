<?php

require_once __DIR__ . "/../aa_kon_sett.php";
require_once __DIR__ . "/../src/helpers/whatsapp_helper_link.php";
require_once __DIR__ . "/../src/utils/Logger.php";

$logger = new AppLogger('tutup_percakapan_otomatis.log');

$lockFile = __DIR__ . '/tutup_percakapan.lock';
$lockHandle = fopen($lockFile, 'w');

if (!$lockHandle) {
    $logger->error("Tidak bisa membuat lock file.");
    exit;
}

if (!flock($lockHandle, LOCK_EX | LOCK_NB)) {
    $logger->info("Cron job 'tutup_percakapan' sudah berjalan. Melewatkan eksekusi ini.");
    exit;
}


$timeoutDuration = '5 MINUTE';

$sql = "SELECT nomor_telepon FROM wa_percakapan
        WHERE status_percakapan = 'open'
        AND terakhir_interaksi_pada < NOW() - INTERVAL $timeoutDuration";

$result = mysqli_query($conn, $sql);

if (!$result) {
    $logger->error("Error query: " . mysqli_error($conn));

    flock($lockHandle, LOCK_UN);
    fclose($lockHandle);
    exit;
}

$pesanPenutup = "Mohon maaf Ayah/Bunda karena tidak ada respon yang Kami terima sampai dengan saat ini, maka chat ini akan kami akhiri. Jika ada hal lain yang ingin ditanyakan, Ayah/Bunda dapat menghubungi kami kembali. Dengan senang hati kami akan membantu. Terima kasih";

$count = 0;
while ($row = mysqli_fetch_assoc($result)) {
    $nomorTelepon = $row['nomor_telepon'];


    kirimPesanTeks($nomorTelepon, $pesanPenutup);

    $updateStmt = $conn->prepare("UPDATE wa_percakapan SET status_percakapan = 'closed', menu_utama_terkirim = 0 WHERE nomor_telepon = ?");
    $updateStmt->bind_param("s", $nomorTelepon);
    $updateStmt->execute();
    $updateStmt->close();

    $count++;
}

mysqli_close($conn);

flock($lockHandle, LOCK_UN);
fclose($lockHandle);