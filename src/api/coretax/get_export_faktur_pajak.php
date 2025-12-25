<?php
session_start();
ini_set('display_errors', 0);
header('Content-Type: application/json');
require_once __DIR__ . '/../../../aa_kon_sett.php';

try {
    if (!$conn)
        throw new Exception("Koneksi Database Gagal");

    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $filterDate = isset($_GET['date']) ? trim($_GET['date']) : '';
    $sortOption = isset($_GET['sort']) ? $_GET['sort'] : 'created';

    // Sorting Logic
    $orderClause = "COALESCE(fp.edit_pada, fp.dibuat_pada) DESC";
    if ($sortOption === 'date') {
        $orderClause = "fp.tgl_faktur DESC, fp.dibuat_pada DESC";
    }

    $whereClauses = ["1=1"];
    $params = [];
    $types = "";

    // Filter Tanggal
    if (!empty($filterDate)) {
        $whereClauses[] = "fp.tgl_faktur = ?";
        $params[] = $filterDate;
        $types .= "s";
    }

    // Filter Search
    if (!empty($search)) {
        $cleanNumber = str_replace(['.', ','], '', $search);
        $isNumeric = is_numeric($cleanNumber);
        $searchLike = "%" . $search . "%";

        $textClause = "(
            fp.nsfp LIKE ? OR 
            fp.no_invoice LIKE ? OR 
            fp.nama_supplier LIKE ? OR 
            ks.nm_alias LIKE ? 
        )";
        $params[] = $searchLike;
        $params[] = $searchLike;
        $params[] = $searchLike;
        $params[] = $searchLike;
        $types .= "ssss";

        if ($isNumeric && $cleanNumber != '') {
            $whereClauses[] = "(" . $textClause . " OR fp.dpp = ? OR fp.ppn = ? OR fp.total = ? )";
            $params[] = $cleanNumber;
            $params[] = $cleanNumber;
            $params[] = $cleanNumber;
            $types .= "ddd";
        } else {
            $whereClauses[] = $textClause;
        }
    }

    $sqlWhere = implode(" AND ", $whereClauses);

    $query = "SELECT 
                fp.tgl_faktur, 
                fp.no_invoice, 
                fp.nsfp,
                fp.nama_supplier, 
                fp.kode_store, 
                fp.dpp, 
                fp.dpp_nilai_lain, 
                fp.ppn, 
                fp.total,
                ks.Nm_Alias as nm_alias 
              FROM ff_faktur_pajak as fp
              LEFT JOIN kode_store as ks on fp.kode_store = ks.Kd_Store
              WHERE $sqlWhere
              ORDER BY $orderClause";

    $stmt = $conn->prepare($query);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $data = [];

    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    echo json_encode(['success' => true, 'data' => $data]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} finally {
    if (isset($conn))
        $conn->close();
}
?>