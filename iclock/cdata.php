<?php
/**
 * ADMS Receiver for Solution X100-C
 * Path: /var/www/asokababystore.com/iclock/cdata.php
 */
ini_set('display_errors', 0);
ini_set('log_errors', 1);
try {
    $rootDir = realpath(__DIR__ . '/..');
    $loggerPath = $rootDir . '/src/utils/Logger.php';
    if (!file_exists($loggerPath)) {
        throw new Exception("Logger file not found at: " . $loggerPath);
    }
    require_once $loggerPath;
    $logger = new AppLogger('adms_machine.log');
    $method = $_SERVER['REQUEST_METHOD'];
    $queryString = $_SERVER['QUERY_STRING'] ?? '';
    if ($method === 'GET') {
        $logger->info("🔗 ADMS Handshake [GET] - Query: " . $queryString);
        echo "GET OPTION FROM: 1\r\n";
        echo "Stamp=9999\r\n";
        echo "OpStamp=1\r\n";
        echo "ErrorDelay=30\r\n";
        echo "Delay=10\r\n";
        echo "TransTimes=00:00;14:00\r\n";
        echo "TransInterval=1\r\n";
        echo "TransFlag=1111000000\r\n";
        exit;
    }
    if ($method === 'POST') {
        $rawData = file_get_contents('php://input');
        $logger->logWithContext('info', "📥 Data Received [POST]", [
            'query' => $queryString,
            'raw_body' => $rawData
        ]);
        echo "OK";
        exit;
    }
    http_response_code(405);
    echo "Method Not Allowed";
} catch (Exception $e) {
    error_log("ADMS Critical Error: " . $e->getMessage());
    http_response_code(500);
    echo "Internal Server Error";
}