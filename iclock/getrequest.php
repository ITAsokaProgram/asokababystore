<?php
require_once __DIR__ . "/../src/utils/Logger.php";
$logger = new AppLogger('absence_get_request.log');

$cmdId = time();

/** * Pilih salah satu perintah di bawah dengan menghilangkan komentar (uncomment):
 */

// A. Tarik SEMUA info user (Nama, PIN, Group)
$command = "C:$cmdId:DATA QUERY USERINFO";

// B. Tarik SEMUA data absensi yang tersimpan di memori mesin
// $command = "C:$cmdId:DATA QUERY ATTLOG";

// C. Tarik SEMUA sidik jari (Fingerprint template)
// $command = "C:$cmdId:DATA QUERY FINGERTMP";

$logger->info("Sending Command to Machine: $command");

echo $command;