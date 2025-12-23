<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

try {
    $result = $conn->query("
      DELETE FROM pembayaran WHERE tanggal <= CURDATE() - INTERVAL 4 DAY;
    ");

    if ($result) {
        echo date('Y-m-d H:i:s') . " DELETE DATA PEMBAYARAN SUCCESS\n";
    } else {
        echo date('Y-m-d H:i:s') . " ERROR: Gagal delete data pembayaran: " . $conn->error . "\n";
    }

} catch (Exception $e) {
    echo date('Y-m-d H:i:s') . " ERROR: " . $e->getMessage() . "\n";
}