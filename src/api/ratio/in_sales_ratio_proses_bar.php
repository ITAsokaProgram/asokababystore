<?php
require_once "../../../aa_kon_sett.php";
header("Content-Type: application/json");
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax'])) {
    $kd_store = filter_input(INPUT_POST, 'kd_store', FILTER_SANITIZE_STRING);
    $kode_supp1 = isset($_POST['kode_supp1']) ? $_POST['kode_supp1'] : '';
    $kode_supp2 = isset($_POST['kode_supp2']) ? $_POST['kode_supp2'] : '';
    $kode_supp3 = isset($_POST['kode_supp3']) ? $_POST['kode_supp3'] : '';
    $kode_supp4 = isset($_POST['kode_supp4']) ? $_POST['kode_supp4'] : '';
    $kode_supp5 = isset($_POST['kode_supp5']) ? $_POST['kode_supp5'] : '';
    $allowedOrderColumns = ['Total', 'Qty'];
    $filter = isset($_GET['filter']) && in_array($_GET['filter'], $allowedOrderColumns) ? $_GET['filter'] : 'Qty';
    $ratio = filter_input(INPUT_POST, 'ratio', FILTER_SANITIZE_STRING);
    $startDate = DateTime::createFromFormat('d-m-Y', $_POST['start_date']) ? date('Y-m-d', strtotime($_POST['start_date'])) : null;
    $endDate = DateTime::createFromFormat('d-m-Y', $_POST['end_date']) ? date('Y-m-d', strtotime($_POST['end_date'])) : null;

    $page = filter_input(INPUT_POST, 'page', FILTER_VALIDATE_INT) ?: 1;
    $limit = 10;
    $offset = max(($page - 1) * $limit, 0);
    try {
        if (!is_array($kd_store)) {
            $kd_store = explode(',', $kd_store);
        }
        $placeholdersKD = implode(',', array_fill(0, count($kd_store), '?'));
        if ($ratio === 'None') {
            echo json_encode(['status' => 'error', 'message' => 'Ratio Tidak Boleh None']);
        }
        if (!is_array($kode_supp1)) {
            $kode_supp1 = explode(',', $kode_supp1); // Jika dikirim sebagai string, ubah jadi array
        }
        if (!is_array($kode_supp2)) {
            $kode_supp2 = explode(',', $kode_supp2); // Jika dikirim sebagai string, ubah jadi array
        }
        if (!is_array($kode_supp3)) {
            $kode_supp3 = explode(',', $kode_supp3); // Jika dikirim sebagai string, ubah jadi array
        }
        if (!is_array($kode_supp4)) {
            $kode_supp4 = explode(',', $kode_supp4); // Jika dikirim sebagai string, ubah jadi array
        }
        if (!is_array($kode_supp5)) {
            $kode_supp5 = explode(',', $kode_supp5); // Jika dikirim sebagai string, ubah jadi array
        }


        $all_array_kd = array_merge(
            array_filter($kode_supp1),
            array_filter($kode_supp2),
            array_filter($kode_supp3),
            array_filter($kode_supp4),
            array_filter($kode_supp5)
        );
        if (empty($all_array_kd)) {
            $all_array_kd = ['NONE'];  // Pastikan "NONE" tidak ada di database
        }

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
                (
                    SELECT SUM(qty) 
                    FROM trans_b 
                    WHERE kode_supp IN ($placeholders) 
                    AND kd_store IN ($placeholdersKD) 
                    AND tgl_trans BETWEEN ? AND ?
                ), 
                2
            ), 
            '%'
        ) AS Percentage,
        ROUND(
            SUM(hrg_promo * qty) * 100.0 / 
            (
                SELECT SUM(hrg_promo * qty)
                FROM trans_b         
                WHERE kd_store IN ($placeholdersKD) AND kode_supp IN ($placeholders) 
                AND tgl_trans BETWEEN ? AND ?
            ), 
            2
        ) AS persentase_rp
    FROM trans_b
    WHERE kd_store IN ($placeholdersKD) 
    AND tgl_trans BETWEEN ? AND ?
    AND kode_supp IN ($placeholders)
    GROUP BY periode, kode_supp
    ORDER BY periode ASC, $filter ASC ";
        $params = array_merge(
            [$endDate, $startDate, $endDate, $startDate],
            $all_array_kd,
            $kd_store,
            [$startDate, $endDate],
            $kd_store, $all_array_kd,
            [$startDate, $endDate],
            $kd_store, [$startDate, $endDate],
            $all_array_kd
        );
        $paramTypes = str_repeat('s', count($params));
        $stmt = $conn->prepare($sql);

        // Bind parameter dengan jumlah yang sesuai
        $stmt->bind_param($paramTypes, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = [];
        $tableData = [];
        $labels = [];

        while ($row = $result->fetch_assoc()) {
            $labels[] = htmlspecialchars($row['kode_supp'] ?? "");
            $data[] = $row['Qty'];
            $tableData[] = $row;
        }
        echo json_encode([
            'status' => 'success',
            'labels' => $labels,
            'data' => $data,
            'tableData' => $tableData,
        ]);
        $stmt->close();
        $conn->close();
    } catch (Exception $e) {
        error_log("❌ Error: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Terjadi kesalahan pada server.']);
    }
}
