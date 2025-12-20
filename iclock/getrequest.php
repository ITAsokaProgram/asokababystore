<?php
require_once __DIR__ . "/../src/utils/Logger.php";
$logger = new AppLogger('absence_get_request.log');

$cmdId = time();

// Kirim multiple commands sekaligus (mesin akan proses satu per satu)
$commands = [
    "C:$cmdId:DATA QUERY USERINFO",      // Tarik semua info user (PIN, Name, dll.)
    "C:" . ($cmdId + 1) . ":DATA QUERY ATTLOG",  // Tarik semua log absensi existing
    "C:" . ($cmdId + 2) . ":DATA QUERY FINGERTMP" // Tarik semua template sidik jari
    // Tambahkan yang lain jika perlu, misal BIOphoto dll jika support
];

$response = implode("\n", $commands);

$logger->info("Sending Multiple Commands to Machine: \n$response");

echo $response;
?>