<?php
session_start();
ini_set('display_errors', 0);
header('Content-Type: application/json');
require_once __DIR__ . '/../../../aa_kon_sett.php';

try {
    if (!$conn)
        throw new Exception("Koneksi Database Gagal");

    // Ambil Parameter Filter
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $filterDate = isset($_GET['date']) ? trim($_GET['date']) : '';
    $sortOption = isset($_GET['sort']) ? $_GET['sort'] : 'created';

    // Logic Sorting
    $orderClause = "fp.dibuat_pada DESC";
    if ($sortOption === 'date') {
        $orderClause = "fp.tgl_nota DESC, fp.dibuat_pada DESC";
    }

    // Logic Filtering
    $whereClauses = ["1=1"];
    $params = [];
    $types = "";

    if (!empty($filterDate)) {
        $whereClauses[] = "fp.tgl_nota = ?";
        $params[] = $filterDate;
        $types .= "s";
    }

    if (!empty($search)) {
        $cleanNumber = str_replace(['.', ','], '', $search);
        $isNumeric = is_numeric($cleanNumber);
        $searchLike = "%" . $search . "%";

        $textClause = "(
            fp.no_invoice LIKE ? OR 
            fp.nama_supplier LIKE ? OR 
            ks.nm_alias LIKE ? OR
            fp.status LIKE ?
        )";
        $params[] = $searchLike;
        $params[] = $searchLike;
        $params[] = $searchLike;
        $params[] = $searchLike;
        $types .= "ssss";

        if ($isNumeric && $cleanNumber != '') {
            $whereClauses[] = "(" . $textClause . " OR fp.dpp = ? OR fp.dpp_nilai_lain = ? OR fp.ppn = ? OR fp.total_terima_fp = ? )";
            $params[] = $cleanNumber;
            $params[] = $cleanNumber;
            $params[] = $cleanNumber;
            $params[] = $cleanNumber;
            $types .= "dddd";
        } else {
            $whereClauses[] = $textClause;
        }
    }

    $sqlWhere = implode(" AND ", $whereClauses);

    // Query TANPA LIMIT & OFFSET
    $query = "SELECT 
                fp.tgl_nota, 
                fp.no_invoice, 
                fp.nama_supplier, 
                fp.kode_store, 
                fp.status, 
                fp.dpp, 
                fp.dpp_nilai_lain, 
                fp.ppn, 
                fp.total_terima_fp,
                ks.nm_alias 
              FROM ff_pembelian as fp
              LEFT JOIN kode_store as ks on fp.kode_store = ks.kd_store
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