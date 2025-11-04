<?php
session_start();
include '../../../aa_kon_sett.php';
header('Content-Type: application/json');

$alias = $_GET['alias'] ?? '';
$tanggal = $_GET['tanggal'] ?? '';

if (empty($alias) || empty($tanggal)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Parameter alias dan tanggal wajib diisi.']);
    exit;
}

$log_entries = [];
$full_log = '';

$sql = "SELECT pesan, tanggal FROM log_backup 
        WHERE DATE(tanggal) = ? 
        AND SUBSTRING_INDEX(
                TRIM(
                    REPLACE(REPLACE(REPLACE(pesan, '\n', ' '), '\r', ' '), '\t', ' ')
                ), 
            ' ', 1) = ?
        ORDER BY tanggal ASC";

if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("ss", $tanggal, $alias);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $log_header = "[Log Time: " . $row['tanggal'] . "]";
        $log_entries[] = $log_header . "\n" . $row['pesan'];
    }

    $stmt->close();

    if (empty($log_entries)) {
        $full_log = "Tidak ada detail log ditemukan untuk cabang '{$alias}' pada tanggal '{$tanggal}'.";
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