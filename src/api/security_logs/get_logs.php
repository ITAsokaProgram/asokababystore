<?php
session_start();
include '../../../aa_kon_sett.php';
header('Content-Type: application/json');

// Default: Hari ini
$end_date = $_GET['end_date'] ?? date('Y-m-d');
// Default: Kemarin
$start_date = $_GET['start_date'] ?? date('Y-m-d', strtotime('-1 day'));

$response = [
    'summary' => [
        'total_events' => 0,
        'unique_ips' => 0,
        'top_service' => '-'
    ],
    'logs' => []
];

try {
    // Ubah Query menggunakan BETWEEN
    $sql = "SELECT id, ip_address, service_name, country_code, log_date 
            FROM security_logs 
            WHERE DATE(log_date) BETWEEN ? AND ? 
            ORDER BY log_date DESC";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ss", $start_date, $end_date);
        $stmt->execute();
        $result = $stmt->get_result();

        $ips = [];
        $services = [];

        while ($row = $result->fetch_assoc()) {
            $response['logs'][] = $row;

            // Collect data for summary
            $ips[$row['ip_address']] = true;

            if (!isset($services[$row['service_name']])) {
                $services[$row['service_name']] = 0;
            }
            $services[$row['service_name']]++;
        }
        $stmt->close();

        // Hitung Summary
        $response['summary']['total_events'] = count($response['logs']);
        $response['summary']['unique_ips'] = count($ips);

        if (!empty($services)) {
            $response['summary']['top_service'] = array_search(max($services), $services);
        }
    } else {
        throw new Exception("Database error: " . $conn->error);
    }

    echo json_encode($response);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
} finally {
    $conn->close();
}
?>