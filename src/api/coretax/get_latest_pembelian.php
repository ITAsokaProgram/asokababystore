<?php
session_start();
ini_set('display_errors', 0);
header('Content-Type: application/json');
require_once __DIR__ . '/../../../aa_kon_sett.php';
try {
    if (!$conn) {
        throw new Exception("Koneksi Database Gagal");
    }
    $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
    $limit = 20;
    $offset = ($page - 1) * $limit;
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $filterDate = isset($_GET['date']) ? trim($_GET['date']) : '';
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
            fp.no_faktur LIKE ? OR 
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
            $whereClauses[] = "(" . $textClause . " OR fp.dpp = ? OR fp.total_terima_fp = ? )";
            $params[] = $cleanNumber;
            $params[] = $cleanNumber;
            $types .= "dd";
        } else {
            $whereClauses[] = $textClause;
        }
    }
    $sqlWhere = implode(" AND ", $whereClauses);
    $query = "SELECT 
                fp.id, 
                fp.nama_supplier, 
                fp.kode_supplier, 
                fp.kode_store, 
                fp.tgl_nota, 
                fp.no_faktur, 
                fp.dpp, 
                fp.dpp_nilai_lain, 
                fp.ppn, 
                fp.total_terima_fp,
                fp.edit_pada,
                fp.status, 
                fp.nsfp,
                ks.nm_alias 
              FROM ff_pembelian as fp
              INNER JOIN kode_store as ks on fp.kode_store = ks.kd_store
              WHERE $sqlWhere
              ORDER BY fp.tgl_nota DESC, fp.id DESC 
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
        $row['dpp_nilai_lain'] = (float) ($row['dpp_nilai_lain'] ?? 0);
        $row['ppn'] = (float) $row['ppn'];
        $row['total_terima_fp'] = (float) $row['total_terima_fp'];
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
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} finally {
    if (isset($conn))
        $conn->close();
}
?>