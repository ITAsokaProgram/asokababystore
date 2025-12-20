<?php
ini_set('display_errors', 0);
ini_set('log_errors', 1);

try {
    $rootDir = realpath(__DIR__ . '/..');
    require_once $rootDir . '/src/utils/Logger.php';
    $logger = new AppLogger('adms_machine.log');

    $method = $_SERVER['REQUEST_METHOD'];
    $queryString = $_SERVER['QUERY_STRING'] ?? ''; // ?SN=BWXP...
    $rawData = file_get_contents('php://input'); // Isi seperti ID=12345&Return=0

    if ($method === 'POST') {
        parse_str($queryString, $params);
        $sn = $params['SN'] ?? 'unknown';

        $logger->info("🔄 DEVICECMD Received from SN=$sn | Raw Data: $rawData");

        // Parse status command (misal multiple ID=xxx&Return=0)
        if (!empty($rawData)) {
            parse_str($rawData, $statusData);
            $logger->success("Command Status from SN=$sn: " . print_r($statusData, true));

            // Jika semua command success, bisa hapus pending atau update DB
        }

        // Selalu balas OK biar mesin anggap success dan lanjut upload data
        echo "OK";
        http_response_code(200);
        exit;
    }

    // Jika GET atau lain
    $logger->info("🔗 DEVICECMD Other Method | Query: $queryString");
    echo "OK";

} catch (Exception $e) {
    error_log("DEVICECMD Error: " . $e->getMessage());
    echo "Error";
}
?>