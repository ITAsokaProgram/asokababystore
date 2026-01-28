<?php
require_once __DIR__ . '/../../../aa_kon_sett.php';
require_once __DIR__ . '/../../auth/middleware_login.php';

header('Content-Type: application/json');

$decoded = authenticate_request();

try {
    $stmt = $conn->prepare("SELECT id, nama_label, warna FROM wa_labels ORDER BY nama_label ASC");
    $stmt->execute();
    $result = $stmt->get_result();
    $labels = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    
    echo json_encode($labels);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>