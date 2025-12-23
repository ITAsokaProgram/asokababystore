<?php
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('memory_limit', '512M');
set_time_limit(0);
try {
    $rootDir = realpath(__DIR__ . '/..');
    require_once $rootDir . '/src/utils/Logger.php';
    $logger = new AppLogger('adms_machine.log');
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    $queryString = $_SERVER['QUERY_STRING'] ?? '';
    if ($method === 'POST') {
        $rawData = file_get_contents('php://input');
        parse_str($queryString, $params);
        $table = strtoupper($params['table'] ?? $params['Table'] ?? '');
        if (empty(trim($rawData))) {
            $logger->warning("POST diterima tapi data kosong. Table: $table");
            echo "OK";
            exit;
        }
        $rows = array_filter(array_map('trim', explode("\n", $rawData)));
        $logger->info("📥 Data Received [POST] Table: $table | Rows: " . count($rows));
        foreach ($rows as $line) {
            if (empty($line))
                continue;
            $data = explode("\t", $line);
            if ($table === 'ATTLOG' || $table === 'TRANSACTION' || strpos($table, 'ATT') !== false) {
                if (count($data) >= 4) {
                    $pin = trim($data[0] ?? '');
                    $time = trim($data[1] ?? '');
                    $status = trim($data[2] ?? '1');
                    if (empty($pin) || empty($time) || strtoupper($pin) === 'FP' || strtoupper($pin) === 'USERPIC')
                        continue;
                    $logger->success("✅ ABSEN REALTIME: NIK=$pin | Time=$time | Status=$status");
                }
            } else {
                if (count($data) >= 2) {
                    $pin = trim($data[0] ?? '');
                    $name = trim($data[1] ?? '');
                } else {
                    $line_temp = str_replace(" ", "&", $line);
                    parse_str($line_temp, $userData);
                    $pin = $userData['PIN'] ?? '';
                    $name = $userData['Name'] ?? $userData['FileName'] ?? '';
                }
                $cleanPin = strtoupper(trim($pin));
                if (empty($cleanPin) || $cleanPin === 'FP' || $cleanPin === 'USERPIC') {
                    continue;
                }
                if ($table === 'USERINFO' || strpos($table, 'USER') !== false) {
                    $logger->info("👤 USERINFO: NIK=$pin | Nama=$name");
                } else {
                    $logger->info("📋 Other Table: $table | NIK=$pin | Nama=$name");
                }
            }
        }
        echo "OK";
        exit;
    }
    if ($method === 'GET') {
        $logger->info("🔗 ADMS Handshake [GET] dari mesin");
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
        exit;
    }
    http_response_code(405);
    echo "Method Not Allowed";
} catch (Exception $e) {
    error_log("CDATA Error: " . $e->getMessage() . " | Trace: " . $e->getTraceAsString());
    echo "ERROR";
}