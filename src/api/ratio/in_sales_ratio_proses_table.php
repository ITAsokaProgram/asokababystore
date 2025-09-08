<?php
require_once "../../../aa_kon_sett.php";
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax'])) {
    $selectedKode = $_POST['selectKode'];
    $kd_store = $_POST['kd_store'];
    $allowedOrderColumns = ['Total', 'Qty'];
    $filter = isset($_GET['filter']) && in_array($_GET['filter'], $allowedOrderColumns) ? $_GET['filter'] : 'Qty';
    $startDate = DateTime::createFromFormat('d-m-Y', $_POST['start_date']) ? date('Y-m-d', strtotime($_POST['start_date'])) : null;
    $endDate = DateTime::createFromFormat('d-m-Y', $_POST['end_date']) ? date('Y-m-d', strtotime($_POST['end_date'])) : null;
    $page = filter_input(INPUT_POST, 'page', FILTER_VALIDATE_INT) ?: 1;
    if (!is_array($kd_store)) {
        $kd_store = explode(',', $kd_store);
    }
    $placeholders = implode(',', array_fill(0, count($kd_store), '?'));
    $sql = "SELECT descp AS promo,SUM(qty) AS Qty, SUM(qty * hrg_promo) AS Total FROM trans_b
                WHERE kd_store IN ($placeholders) AND tgl_trans BETWEEN ?
                 AND ? AND kode_supp=? 
                  GROUP BY plu ORDER BY $filter DESC";
    $stmt = $conn->prepare($sql);
    // Gabungkan parameter (kd_store harus dipecah satu per satu)
    $params = array_merge($kd_store, [$startDate, $endDate, $selectedKode]);

    // **Tentukan tipe parameter**
    $paramTypes = str_repeat('s', count($kd_store)) . "sss";
    // Bind parameter dengan cara yang benar
    $stmt->bind_param($paramTypes, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    $labels = [];
    $data = [];
    $tableData = [];

    while ($row = $result->fetch_assoc()) {
        $labels[] = htmlspecialchars($row['promo']);
        $data[] = $row['Qty'];
        $tableData[] = $row;
    }

    // **Query untuk menghitung total data*
    $countSql = "SELECT COUNT(*) AS total_data
            FROM (
                SELECT descp
                FROM trans_b
                WHERE kd_store IN ($placeholders) 
                AND tgl_trans BETWEEN ? AND ?
                AND kode_supp=? 
                GROUP BY plu
            ) AS subquery";
    $countStmt = $conn->prepare($countSql);
    $countParams = array_merge($kd_store, [$startDate, $endDate, $selectedKode]);
    $countParamTypes = str_repeat('s', count($kd_store)) . "sss";

    $countStmt->bind_param($countParamTypes, ...$countParams);
    // **Eksekusi query total data**
    $countStmt->execute();
    $countResult = $countStmt->get_result();
    $rowCount = $countResult->fetch_assoc()['total_data'];


    $stmt->close();
    $countStmt->close();
    $conn->close();
    echo json_encode([
        'labels' => $labels,
        'data' => $data,
        'tableData' => $tableData,
        'currentPage' => $page,
        'totalData' => $rowCount
    ], JSON_PRETTY_PRINT);
    exit;
}