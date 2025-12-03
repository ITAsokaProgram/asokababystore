<?php
session_start();
ini_set('display_errors', 0);
header('Content-Type: application/json');
require_once __DIR__ . '/../../../aa_kon_sett.php';
try {
    if (!$conn) {
        throw new Exception("Koneksi Database Gagal: " . mysqli_connect_error());
    }
    $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
    $limit = 20;
    $offset = ($page - 1) * $limit;
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $filterDate = isset($_GET['date']) ? trim($_GET['date']) : '';
    $sortOption = isset($_GET['sort']) ? $_GET['sort'] : 'created';
    $orderClause = "COALESCE(fp.edit_pada, fp.dibuat_pada) DESC, fp.id DESC";
    if ($sortOption === 'date') {
        $orderClause = "fp.tgl_faktur DESC, fp.dibuat_pada DESC";
    }
    $whereClauses = ["1=1"];
    $params = [];
    $types = "";
    if (!empty($filterDate)) {
        $whereClauses[] = "fp.tgl_faktur = ?";
        $params[] = $filterDate;
        $types .= "s";
    }
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
                fp.id, 
                fp.nsfp, 
                fp.no_invoice,  
                fp.tgl_faktur, 
                fp.nama_supplier, 
                fp.dpp, 
                fp.dpp_nilai_lain, 
                fp.ppn, 
                fp.total,
                fp.kode_store,
                fp.edit_pada,
                ks.Nm_Alias as nm_alias 
              FROM ff_faktur_pajak fp
              LEFT JOIN kode_store ks ON fp.kode_store = ks.Kd_Store
              WHERE $sqlWhere
              ORDER BY $orderClause 
              LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    $types .= "ii";
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception("SQL Prepare Error: " . $conn->error);
    }
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $row['id'] = (int) $row['id'];
        $row['dpp'] = (float) $row['dpp'];
        $row['dpp_nilai_lain'] = (float) $row['dpp_nilai_lain'];
        $row['ppn'] = (float) $row['ppn'];
        $row['total'] = (float) $row['total'];
        $data[] = $row;
    }
    $hasMore = count($data) === $limit;
    echo json_encode([
        'success' => true,
        'data' => $data,
        'page' => $page,
        'has_more' => $hasMore
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>