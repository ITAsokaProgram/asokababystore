<?php

require_once __DIR__ . "/../aa_kon_sett.php";
require_once __DIR__ . "/../src/utils/Logger.php";

$logger = new AppLogger('cron_update_gppn.log');
$logger->info("Mulai cron job update GPPN receipt_head (3 hari terakhir).");

$sql_update = "
    UPDATE receipt_head 
    SET gppn = 0 
    WHERE ppn = 0 
    AND tgl_tiba >= DATE_SUB(CURRENT_DATE, INTERVAL 3 DAY)
";

if (mysqli_query($conn, $sql_update)) {
    $updated_rows = mysqli_affected_rows($conn);
    if ($updated_rows > 0) {
        $logger->info("Berhasil update GPPN. Jumlah baris terpengaruh: " . $updated_rows);
    } else {
        $logger->info("Query berjalan sukses, tidak ada data yang perlu di-update (0 rows).");
    }
} else {
    $logger->error("Gagal update GPPN: " . mysqli_error($conn));
}

$logger->info("Selesai cron job update GPPN.");

?>