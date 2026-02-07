<?php
require_once __DIR__ . "/../../../config.php";
header("Content-Type: application/json");

try {
    $sql = "SELECT kode_Area, COUNT(*) as total 
            FROM kode_store 
            WHERE kode_Area IS NOT NULL AND kode_Area != ''
            GROUP BY kode_Area 
            ORDER BY kode_Area ASC";
            
    $result = $conn->query($sql);
    $data = [];
    
    while ($row = $result->fetch_assoc()) {
        $data[] = [
            'id' => $row['kode_Area'],
            'text' => $row['kode_Area'] . " (" . $row['total'] . " Cabang)"
        ];
    }
    
    echo json_encode(['success' => true, 'data' => $data]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>