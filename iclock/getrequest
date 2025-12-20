<?php
require_once __DIR__ . "/../src/utils/Logger.php";
$logger = new AppLogger('absence_get_request.log');
parse_str($_SERVER['QUERY_STRING'], $params);
$sn = $params['SN'] ?? 'unknown';
$statusDir = __DIR__ . "/command_status";
if (!is_dir($statusDir)) {
    mkdir($statusDir, 0755, true);
}
$statusFile = $statusDir . "/" . $sn . ".json";
$status = file_exists($statusFile) ? json_decode(file_get_contents($statusFile), true) : [];
$needSync = $status['need_sync'] ?? true;
$commands = [];
$cmdId = time();
if ($needSync) {
    $commands[] = "C:$cmdId:DATA QUERY USERINFO";
    $commands[] = "C:" . ($cmdId + 1) . ":DATA QUERY ATTLOG\tStartTime=2024-01-01 00:00:00";
    file_put_contents($statusFile, json_encode(['need_sync' => false, 'last_sync' => date('Y-m-d H:i:s')]));
    $logger->info("🚀 Mengirim command SYNC AWAL ke mesin SN=$sn");
} else {
    $logger->debug("❤️ Heartbeat normal dari mesin SN=$sn (tidak ada command)");
}
header('Content-Type: text/plain');
echo "GET OPTION FROM: 1\r\n";
echo "Stamp=9999999999\r\n";
echo "OpStamp=9999\r\n";
echo "PhotoStamp=0\r\n";
echo "ErrorDelay=60\r\n";
echo "Delay=30\r\n";
echo "TransTimes=00:00;23:59\r\n";
echo "TransInterval=1\r\n";
echo "TransFlag=1111111111\r\n";
echo "Realtime=1\r\n";
echo "Encrypt=0\r\n";
if (!empty($commands)) {
    $response = implode("\n", $commands);
    echo $response . "\n";
    $logger->info("Command dikirim ke SN=$sn:\n$response");
}
?>