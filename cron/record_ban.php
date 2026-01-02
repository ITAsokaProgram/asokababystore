<?php
if (php_sapi_name() !== 'cli') {
    exit;
}
if (!isset($argv[1]) || !isset($argv[2])) {
    echo "Usage: php record_ban.php <ip> <service>\n";
    exit(1);
}
$ip_address = $argv[1];
$service_name = $argv[2];
require_once __DIR__ . "/../aa_kon_sett.php";
$stmt = $conn->prepare("INSERT INTO security_logs (ip_address, service_name) VALUES (?, ?)");
if ($stmt) {
    $stmt->bind_param("ss", $ip_address, $service_name);
    $stmt->execute();
    $stmt->close();
} else {
    file_put_contents(__DIR__ . '/db_error.log', date('Y-m-d H:i:s') . " - Gagal prepare stmt\n", FILE_APPEND);
}
mysqli_close($conn);
?>