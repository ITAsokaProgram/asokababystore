<?php

require_once __DIR__ . "/../aa_kon_sett.php";
require_once __DIR__ . "/../src/utils/Logger.php";

$logger = new AppLogger('cron_update_last_trans.log');
$logger->info("Mulai cron job update Last_Trans customer.");


$sql_update = "
    UPDATE customers c
    JOIN (
        SELECT 
            kd_cust, 
            MAX(tgl_trans) AS max_trans_date
        FROM 
            trans_b
        WHERE 
            kd_cust IS NOT NULL AND kd_cust != ''
        GROUP BY 
            kd_cust
    ) AS t ON c.kd_cust = t.kd_cust
    SET 
        c.Last_Trans = DATE(t.max_trans_date)
    WHERE
        c.Last_Trans IS NULL OR c.Last_Trans != DATE(t.max_trans_date);
";

if (mysqli_query($conn, $sql_update)) {
    $updated_rows = mysqli_affected_rows($conn);
    $logger->info("Berhasil update Last_Trans. Jumlah baris terpengaruh: " . $updated_rows);
} else {
    $logger->error("Gagal update Last_Trans: " . mysqli_error($conn));
}


$logger->info("Selesai cron job update Last_Trans customer.");

?>