<?php

require_once __DIR__ . '/../../../aa_kon_sett.php';
require_once __DIR__ . '/../../auth/middleware_login.php';
header("Content-Type:application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");

try {
    $input = json_decode(file_get_contents('php://input'), true);

    // Validate required parameters
    if (!$input || !isset($input['kd_store'])) {
        throw new Exception("Invalid or missing store code");
    }

    $type_kategory = $input['type_kategori'] ?? '';
    // Convert kd_store to array if it's a string
    if (isset($input['kd_store'])) {
        if (is_string($input['kd_store'])) {
            // pecah berdasarkan koma dan trim spasi
            $kd_store = array_map('trim', explode(',', $input['kd_store']));
        } elseif (is_array($input['kd_store'])) {
            $kd_store = $input['kd_store'];
        } else {
            $kd_store = [];
        }
    } else {
        $kd_store = [];
    }
    $startDateObj = DateTime::createFromFormat('d-m-Y', $input['start_date']);
    $endDateObj = DateTime::createFromFormat('d-m-Y', $input['end_date']);
    $tgl_awal = $startDateObj ? $startDateObj->format('Y-m-d') : null;
    $tgl_akhir = $endDateObj ? $endDateObj->format('Y-m-d') : null;
    $kode_supp = $input['kode_supp'] ?? '';

    if (!$tgl_awal || !$tgl_akhir) {
        throw new Exception("Invalid date format. Use d-m-Y format");
    }

    // Debug logging
    error_log("Excel Export Debug - Store codes: " . json_encode($kd_store));
    error_log("Excel Export Debug - Date range: $tgl_awal to $tgl_akhir");
    error_log("Excel Export Debug - Category: $type_kategory");
    error_log("Excel Export Debug - Supplier: $kode_supp");

    // Create placeholders for IN clause
    $placeholders = implode(',', array_fill(0, count($kd_store), '?'));

    // Set session for GROUP_CONCAT
    $conn->query("SET SESSION group_concat_max_len = 10000");

    // First query to get dynamic columns
    $sqlCols = "
   SELECT GROUP_CONCAT(
        DISTINCT CONCAT(
            'SUM(CASE WHEN t.kd_store = ''', t.kd_store, 
            ''' THEN t.qty ELSE 0 END) AS `', ks.nm_alias, '`'
        ) ORDER BY ks.nm_alias
    ) AS cols
    FROM trans_b t
    JOIN kode_store ks ON ks.kd_store = t.kd_store
    WHERE t.kd_store IN ($placeholders)
";

    // Prepare and execute first query
    $stmtCols = $conn->prepare($sqlCols);
    if (!$stmtCols) {
        throw new Exception("Failed to prepare columns query: " . $conn->error);
    }
    $stmtCols->bind_param(str_repeat('s', count($kd_store)), ...$kd_store);
    if (!$stmtCols->execute()) {
        throw new Exception("Failed to execute columns query: " . $stmtCols->error);
    }
    $resCols = $stmtCols->get_result()->fetch_assoc();
    $colsPivot = $resCols['cols'];
    $stmtCols->close();

    // Debug: Log the generated columns
    error_log("Excel Export Debug - Generated columns: " . ($colsPivot ?: "NULL"));

    // Check if we have valid columns
    if (!$colsPivot) {
        // Cek jumlah data yang cocok untuk filter
        $sqlCount = "SELECT COUNT(*) as cnt FROM trans_b t WHERE t.kd_store IN ($placeholders) AND t.type_kategori = ? AND t.tgl_trans BETWEEN ? AND ? AND t.kode_supp = ?";
        $stmtCount = $conn->prepare($sqlCount);
        if ($stmtCount) {
            $paramsCount = array_merge($kd_store, [$type_kategory, $tgl_awal, $tgl_akhir, $kode_supp]);
            $typesCount = str_repeat('s', count($kd_store)) . 'ssss';
            $stmtCount->bind_param($typesCount, ...$paramsCount);
            $stmtCount->execute();
            $resCount = $stmtCount->get_result()->fetch_assoc();
            $rowCount = $resCount ? $resCount['cnt'] : 0;
            $stmtCount->close();
            error_log("Excel Export Debug - Data count for filter: $rowCount");
        } else {
            error_log("Excel Export Debug - Failed to prepare count query: " . $conn->error);
        }
        throw new Exception("No dynamic columns generated. Kemungkinan tidak ada data yang cocok dengan filter (store, kategori, supplier, tanggal). Silakan cek data sumber.");
    }

    // Main query with dynamic columns
    $sql = "
    SELECT 
        t.barcode, 
        t.descp AS nama_barang, 
        t.type_kategori, 
        $colsPivot,
        SUM(t.qty) AS total_qty,
        SUM(t.hrg_promo * t.qty) AS total
    FROM trans_b t
    JOIN kode_store ks ON ks.kd_store = t.kd_store
    WHERE t.kd_store IN ($placeholders)
        AND t.type_kategori = ?
        AND t.tgl_trans BETWEEN ? AND ?
        AND t.kode_supp = ?
    GROUP BY t.barcode
    ORDER BY total DESC
";

    // Debug: Log the final SQL query (with placeholders)
    error_log("Excel Export Debug - Main SQL: " . $sql);

    // Prepare and execute main query
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Failed to prepare main query: " . $conn->error);
    }
    $params = array_merge($kd_store, [$type_kategory, $tgl_awal, $tgl_akhir, $kode_supp]);
    $types = str_repeat('s', count($kd_store)) . 'ssss';
    if (!$stmt->bind_param($types, ...$params)) {
        throw new Exception("Failed to bind parameters: " . $stmt->error);
    }
    if (!$stmt->execute()) {
        throw new Exception("Failed to execute main query: " . $stmt->error);
    }
    $result = $stmt->get_result();
    if (!$result) {
        throw new Exception("Failed to get result: " . $stmt->error);
    }

    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    // Debug: Log sample data structure
    if (!empty($data)) {
        error_log("Excel Export Debug - Sample row: " . json_encode($data[0]));
        error_log("Excel Export Debug - Total rows: " . count($data));
    } else {
        error_log("Excel Export Debug - No data returned from main query");
    }

    // Close statement safely
    if ($stmt) {
        $stmt->close();
    }

    echo json_encode(["status" => "success", "message" => "Data retrieved successfully", "data" => $data]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Failed to retrieve data: " . $e->getMessage(),
        "data" => []
    ]);
} catch (Error $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "System error: " . $e->getMessage(),
        "data" => []
    ]);
}
