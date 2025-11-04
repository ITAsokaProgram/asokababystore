<?php
session_start();
include '../../../aa_kon_sett.php';
header('Content-Type: application/json');

$tanggal = $_GET['tanggal'] ?? date('Y-m-d');

if (empty($tanggal)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Parameter tanggal wajib diisi.']);
    exit;
}

$log_entries = [];
$full_log = '';

$sql = "SELECT pesan, tanggal FROM log_backup 
        WHERE DATE(tanggal) = ? 
        ORDER BY tanggal ASC, id ASC";

if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("s", $tanggal);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $log_header = "[Log Time: " . $row['tanggal'] . "]";
        $log_entries[] = $log_header . "\n" . $row['pesan'];
    }

    $stmt->close();

    if (empty($log_entries)) {
        $full_log = "Tidak ada log ditemukan pada tanggal '{$tanggal}'.";
    } else {
        $full_log = implode("\n\n========================================\n\n", $log_entries);
    }

    echo json_encode(['success' => true, 'log_content' => $full_log]);

} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Gagal mempersiapkan query database: ' . $conn->error]);
}

$conn->close();
?>