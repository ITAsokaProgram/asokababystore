<?php
session_start();
include '../../../aa_kon_sett.php';
header('Content-Type: application/json');
$selected_date = $_GET['tanggal'] ?? date('Y-m-d');
$log_stats = [];
$tabel_data = [];
$total_cabang = 0;
$total_belum_sinkron = 0;
$response = [
    'summary' => [
        'total_cabang' => 0,
        'total_sudah_sinkron' => 0,
        'total_belum_sinkron' => 0,
    ],
    'tabel_data' => []
];
try {
    $sql_agg = "
        SELECT
            TRIM(REPLACE(SUBSTRING_INDEX(SUBSTRING_INDEX(pesan, '\n', 2), '\n', -1), '\r', '')) AS nm_alias,
            COUNT(id) AS total_sinkron,
            SUM(CASE WHEN pesan LIKE '%ERROR%' THEN 1 ELSE 0 END) AS total_error
        FROM log_backup
        WHERE DATE(tanggal) = ?
        GROUP BY nm_alias
    ";
    if ($stmt_agg = $conn->prepare($sql_agg)) {
        $stmt_agg->bind_param("s", $selected_date);
        $stmt_agg->execute();
        $result_agg = $stmt_agg->get_result();
        while ($row = $result_agg->fetch_assoc()) {
            if (!empty($row['nm_alias'])) {
                $log_stats[trim($row['nm_alias'])] = $row;
            }
        }
        $stmt_agg->close();
    } else {
        throw new Exception("Error preparing statement (log_agg): " . $conn->error);
    }
    $sql_cabang = "SELECT Nm_Alias FROM kode_store WHERE Nm_Alias IS NOT NULL AND Nm_Alias != '' AND display = 'on' ORDER BY Nm_Alias ASC";
    $result_cabang = $conn->query($sql_cabang);
    if ($result_cabang === false) {
        throw new Exception("Error querying kode_store: " . $conn->error);
    }
    while ($row_cabang = $result_cabang->fetch_assoc()) {
        $nm_alias = trim($row_cabang['Nm_Alias']);

        $total_cabang++;
        $stats = $log_stats[$nm_alias] ?? null;
        $total_sinkron = $stats ? (int) $stats['total_sinkron'] : 0;
        $total_error = $stats ? (int) $stats['total_error'] : 0;
        $status = 'Belum Sinkron';
        $status_class = 'badge-danger';
        if ($total_sinkron > 0) {
            $status = 'Sinkron';
            $status_class = 'badge-success';
            if ($total_error > 0) {
                $status = 'Error';
                $status_class = 'badge-warning';
            }
        } else {
            $total_belum_sinkron++;
        }
        $response['tabel_data'][] = [
            'nama_cabang' => $nm_alias,
            'total_sinkron' => $total_sinkron,
            'total_error' => $total_error,
            'status' => $status,
            'status_class' => $status_class
        ];
    }
    $conn->close();
    $response['summary'] = [
        'total_cabang' => $total_cabang,
        'total_sudah_sinkron' => $total_cabang - $total_belum_sinkron,
        'total_belum_sinkron' => $total_belum_sinkron,
    ];
    echo json_encode($response);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>