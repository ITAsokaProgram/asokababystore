<?php
require_once __DIR__ . "/../../../config.php";
header("Content-Type: application/json");

try {
    $sql = "SELECT Kd_Store, Nm_Alias FROM kode_store WHERE display = 'on' ORDER BY Nm_Alias ASC";
    $result = $conn->query($sql);

    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    echo json_encode([
        "success" => true,
        "data" => $data
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
?>