<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$result = $conn->query("
    SELECT GROUP_CONCAT(COLUMN_NAME SEPARATOR ', ') AS kolom 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = 'appsasokaol' AND TABLE_NAME = 'trans_b'
");

$row = $result->fetch_assoc();
$columns = $row['kolom'];

$insertQuery = "
    
    INSERT IGNORE INTO trans_b ($columns)
    SELECT $columns FROM trans WHERE tgl_trans >= CURDATE() - INTERVAL 3 DAY 
";

if ($conn->query($insertQuery)) {
    echo date('Y-m-d H:i:s') . " INSERT DATA trans TO trans_B SUKSES\n";
} else {
    echo date('Y-m-d H:i:s') . " Gagal: " . $conn->error;
}


