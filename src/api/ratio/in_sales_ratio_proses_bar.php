<?php
require_once "../../../aa_kon_sett.php";
header("Content-Type: application/json; charset=utf-8");
ini_set('display_errors', 0);
error_reporting(E_ALL);
ob_start();
try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['ajax'])) {
        throw new Exception("Invalid Request");
    }
    $kd_store_input = isset($_POST['kd_store']) ? $_POST['kd_store'] : '';
    if (empty($kd_store_input)) {
        throw new Exception("Data Cabang (kd_store) tidak terdeteksi. Silakan refresh halaman dan pilih cabang kembali.");
    }
    $kd_store = $kd_store_input;
    if (!is_array($kd_store)) {
        $kd_store = explode(',', (string) $kd_store);
    }
    $kode_supp1 = isset($_POST['kode_supp1']) ? $_POST['kode_supp1'] : '';
    $kode_supp2 = isset($_POST['kode_supp2']) ? $_POST['kode_supp2'] : '';
    $kode_supp3 = isset($_POST['kode_supp3']) ? $_POST['kode_supp3'] : '';
    $kode_supp4 = isset($_POST['kode_supp4']) ? $_POST['kode_supp4'] : '';
    $kode_supp5 = isset($_POST['kode_supp5']) ? $_POST['kode_supp5'] : '';
    $allowedOrderColumns = ['Total', 'Qty'];
    $filter = isset($_GET['filter']) && in_array($_GET['filter'], $allowedOrderColumns) ? $_GET['filter'] : 'Qty';
    $ratio = filter_input(INPUT_POST, 'ratio', FILTER_SANITIZE_STRING);
    if ($ratio === 'None' || empty($ratio)) {
        throw new Exception('Ratio Harus Dipilih');
    }
    $start_date_in = isset($_POST['start_date']) ? $_POST['start_date'] : '';
    $end_date_in = isset($_POST['end_date']) ? $_POST['end_date'] : '';
    $startDateObj = DateTime::createFromFormat('d-m-Y', $start_date_in);
    $endDateObj = DateTime::createFromFormat('d-m-Y', $end_date_in);
    if (!$startDateObj || !$endDateObj) {
        throw new Exception("Format tanggal tidak valid. Gunakan format dd-mm-yyyy.");
    }
    $startDate = $startDateObj->format('Y-m-d');
    $endDate = $endDateObj->format('Y-m-d');
    function ensureArray($input)
    {
        if (is_array($input))
            return $input;
        if (empty($input))
            return [];
        return explode(',', (string) $input);
    }
    $all_array_kd = array_merge(
        array_filter(ensureArray($kode_supp1)),
        array_filter(ensureArray($kode_supp2)),
        array_filter(ensureArray($kode_supp3)),
        array_filter(ensureArray($kode_supp4)),
        array_filter(ensureArray($kode_supp5))
    );
    if (empty($all_array_kd)) {
        $all_array_kd = ['__NO_DATA__'];
    }
    $placeholdersKD = implode(',', array_fill(0, count($kd_store), '?'));
    $placeholders = implode(',', array_fill(0, count($all_array_kd), '?'));
    $sql = "SELECT 
        kode_supp, 
        CASE 
            WHEN DATEDIFF(?, ?) BETWEEN 1 AND 31 THEN DATE_FORMAT(tgl_trans, '%d-%m')
            WHEN DATEDIFF(?, ?) BETWEEN 32 AND 365 THEN DATE_FORMAT(tgl_trans, '%m-%Y')
            ELSE DATE_FORMAT(tgl_trans, '%Y')
        END AS periode,
        SUM(qty) AS Qty, 
        SUM(qty * hrg_promo) AS Total,
        CONCAT(
            ROUND(
                SUM(qty) * 100.0 / 
                NULLIF((
                    SELECT SUM(qty) 
                    FROM trans_b 
                    WHERE kode_supp IN ($placeholders) 
                    AND kd_store IN ($placeholdersKD) 
                    AND tgl_trans BETWEEN ? AND ?
                ), 0), 
                2
            ), 
            '%'
        ) AS Percentage,
        ROUND(
            SUM(hrg_promo * qty) * 100.0 / 
            NULLIF((
                SELECT SUM(hrg_promo * qty)
                FROM trans_b          
                WHERE kd_store IN ($placeholdersKD) AND kode_supp IN ($placeholders) 
                AND tgl_trans BETWEEN ? AND ?
            ), 0), 
            2
        ) AS persentase_rp
    FROM trans_b
    WHERE kd_store IN ($placeholdersKD) 
    AND tgl_trans BETWEEN ? AND ?
    AND kode_supp IN ($placeholders)
    GROUP BY periode, kode_supp
    ORDER BY periode ASC, $filter ASC";
    $params = array_merge(
        [$endDate, $startDate, $endDate, $startDate],
        $all_array_kd,
        $kd_store,
        [$startDate, $endDate],
        $kd_store,
        $all_array_kd,
        [$startDate, $endDate],
        $kd_store,
        [$startDate, $endDate],
        $all_array_kd
    );
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("SQL Prepare Error: " . $conn->error);
    }
    $types = str_repeat('s', count($params));
    $stmt->bind_param($types, ...$params);
    if (!$stmt->execute()) {
        throw new Exception("SQL Execute Error: " . $stmt->error);
    }
    $result = $stmt->get_result();
    $data = [];
    $tableData = [];
    $labels = [];
    while ($row = $result->fetch_assoc()) {
        $labels[] = htmlspecialchars($row['kode_supp'] ?? "");
        $data[] = (float) $row['Qty'];
        $tableData[] = $row;
    }
    $response = [
        'status' => 'success',
        'labels' => $labels,
        'data' => $data,
        'tableData' => $tableData,
    ];
    ob_end_clean();
    echo json_encode($response);
    $stmt->close();
    $conn->close();
} catch (Throwable $e) {
    ob_end_clean();
    http_response_code(200);
    echo json_encode([
        'status' => 'error',
        'message' => 'Server Error: ' . $e->getMessage(),
        'debug' => 'File: ' . $e->getFile() . ' Line: ' . $e->getLine()
    ]);
    error_log("Ratio Error: " . $e->getMessage());
}
?>