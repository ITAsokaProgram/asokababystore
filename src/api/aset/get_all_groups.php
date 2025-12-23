<?php
require_once __DIR__ . '/../../../aa_kon_sett.php';
header('Content-Type: application/json');

$response = [
    'status' => false,
    'message' => 'Gagal mengambil data',
    'data' => []
];

try {
    $sql = "SELECT DISTINCT group_aset FROM history_aset 
            WHERE group_aset IS NOT NULL AND group_aset != '' 
            ORDER BY group_aset ASC";

    $result = $conn->query($sql);

    if ($result) {
        $groups = [];
        while ($row = $result->fetch_assoc()) {
            $groups[] = $row['group_aset'];
        }
        $response['status'] = true;
        $response['message'] = 'Data group berhasil diambil';
        $response['data'] = $groups;
    } else {
        throw new Exception("Query gagal: " . $conn->error);
    }

    $conn->close();

} catch (Exception $e) {
    http_response_code(500);
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>