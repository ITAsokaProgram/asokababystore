<?php
require_once __DIR__ . '/../../../config.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
try {
    $result = $conn->query("
      DELETE FROM trans WHERE tgl_trans <= CURDATE() - INTERVAL 4 DAY;
    ");
    echo  date('Y-m-d H:i:s') . " DELETE DATA trans SUCCESS\n";
} catch (Exception $e) {
    echo date('Y-m-d H:i:s') . " ERROR: " . $e->getMessage() . "\n";
}
$conn->close();

