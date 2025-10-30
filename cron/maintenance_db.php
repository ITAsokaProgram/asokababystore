<?php


require_once __DIR__ . "/../aa_kon_sett.php";
require_once __DIR__ . "/../src/utils/Logger.php";

$logger = new AppLogger('cron_maintenance_db.log');

$sql_delete = "DELETE FROM master_backup WHERE DATE(TGL_BACKUP) <= DATE_SUB(CURDATE(), INTERVAL 2 DAY)";

if (mysqli_query($conn, $sql_delete)) {
    $deleted_rows = mysqli_affected_rows($conn);
    if ($deleted_rows > 0) {
        $logger->info("Berhasil menghapus " . $deleted_rows . " baris lama dari master_backup.");
    }
} else {
    $logger->error("Gagal menghapus data master_backup: " . mysqli_error($conn));
}

$tables_to_optimize = [
    'master_backup',
    'trans_b',
    'receipt',
    'koreksi',
    'pembayaran_b'
];

foreach ($tables_to_optimize as $table) {
    if (mysqli_query($conn, "OPTIMIZE TABLE $table")) {
    } else {
        $logger->error("Gagal optimasi tabel " . $table . ": " . mysqli_error($conn));
    }
}

mysqli_close($conn);