<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$result = $conn->query("
    SELECT GROUP_CONCAT(COLUMN_NAME SEPARATOR ', ') AS kolom 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = 'dbasokaonline' AND TABLE_NAME = 'pembayaran_b'
");

$row = $result->fetch_assoc();
$columns = $row['kolom'];

$insertQuery = "

    INSERT IGNORE INTO pembayaran_b ($columns)
    SELECT $columns FROM pembayaran WHERE tanggal >= CURDATE() - INTERVAL 3 DAY  AND kd_cust NOT IN ('', '898989',  '999999999') 

";

if ($conn->query($insertQuery)) {
    echo date('Y-m-d H:i:s') . " INSERT DATA PEMBAYARAN TO PEMBAYARAN_B SUKSES\n";
} else {
    echo date('Y-m-d H:i:s') . " Gagal: " . $conn->error;
}
