<?php
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('memory_limit', '512M');
set_time_limit(0);
try {
    $rootDir = realpath(__DIR__ . '/..');
    require_once $rootDir . '/src/utils/Logger.php';
    $logger = new AppLogger('adms_machine.log');
    $method = $_SERVER['REQUEST_METHOD'];
    $queryString = $_SERVER['QUERY_STRING'] ?? '';
    if ($method === 'POST') {
        $rawData = file_get_contents('php://input');
        parse_str($queryString, $params);
        $table = strtoupper($params['table'] ?? '');
        $rows = explode("\n", trim($rawData));
        $logger->info("📥 Data Received [POST] Table: $table | Rows: " . count($rows));
        foreach ($rows as $line) {
            $line = trim($line);
            if (empty($line))
                continue;
            $data = explode("\t", $line);
            if ($table === 'ATTLOG' || $table === 'TRANSACTION') {
                if (count($data) >= 2) {
                    $pin = trim($data[0]);
                    $time = trim($data[1]);
                    $status = trim($data[2] ?? '0');
                    $verify = trim($data[3] ?? '');
                    $logger->success("✅ ABSEN: PIN $pin pada $time (Status: $status)");
                }
            } elseif ($table === 'USERINFO' || strpos($line, 'USER PIN=') === 0 || strpos($line, 'PIN=') === 0) {
                if (strpos($line, 'USER PIN=') === 0 || strpos($line, 'PIN=') !== false) {
                    parse_str(str_replace(" ", "&", $line), $userData);
                    $pin = $userData['PIN'] ?? $userData['UserPIN'] ?? '';
                    $name = $userData['Name'] ?? '';
                } else {
                    $pin = $data[0] ?? '';
                    $name = $data[1] ?? '';
                }
                $logger->info("👤 USERINFO: PIN $pin - Nama: $name");
            } elseif ($table === 'FINGERTMP' || strpos($line, 'FPPIN=') === 0) {
                $logger->info("🤚 FINGERTMP Received for PIN: " . ($data[0] ?? 'unknown'));
            } else {
                $logger->info("📋 Other Table: $table | Line: $line");
            }
            /*
            try {
                $db = new PDO("odbc:Driver={Microsoft Access Driver (*.mdb, *.accdb)};Dbq=C:/path/to/database.mdb;");
            } catch (Exception $e) {
                $logger->error("DB Error: " . $e->getMessage());
            }
            */
        }
        echo "OK";
        exit;
    }
    if ($method === 'GET') {
        $logger->info("🔗 ADMS Handshake [GET]");
        echo "GET OPTION FROM: 1\r\n";
        echo "Stamp=0\r\n";
        echo "OpStamp=0\r\n";
        echo "ErrorDelay=30\r\n";
        echo "Delay=10\r\n";
        echo "TransFlag=1111000000\r\n";
        exit;
    }
} catch (Exception $e) {
    error_log($e->getMessage());
}