<?php
session_start();
ini_set('display_errors', 0);
header('Content-Type: application/json');
require_once __DIR__ . '/../../../aa_kon_sett.php';
try {
    if (!$conn)
        throw new Exception("Koneksi Database Gagal");
    $limit = 40;
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $filterDate = isset($_GET['date']) ? trim($_GET['date']) : '';
    $sortOption = isset($_GET['sort']) ? $_GET['sort'] : 'created';
    $orderClause = "dibuat_pada DESC";
    if ($sortOption === 'date') {
        $orderClause = "tgl_nota DESC, dibuat_pada DESC";
    }
    $whereClauses = ["visibilitas = 'Aktif'"];
    $params = [];
    $types = "";
    if (!empty($filterDate)) {
        $whereClauses[] = "tgl_nota = ?";
        $params[] = $filterDate;
        $types .= "s";
    }
    if (!empty($search)) {
        $searchLike = "%" . $search . "%";
        $whereClauses[] = "(
            nama_supplier LIKE ? OR 
            no_faktur LIKE ? OR 
            no_faktur_format LIKE ? 
        )";
        for ($i = 0; $i < 3; $i++) {
            $params[] = $searchLike;
            $types .= "s";
        }
    }
    $sqlWhere = implode(" AND ", $whereClauses);
    $query = "SELECT * FROM serah_terima_nota WHERE $sqlWhere ORDER BY $orderClause LIMIT ?";
    $params[] = $limit;
    $types .= "i";
    $stmt = $conn->prepare($query);
    if (count($params) > 0) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $row['nominal'] = (float) $row['nominal'];
        $data[] = $row;
    }
    echo json_encode([
        'success' => true,
        'data' => $data,
        'has_more' => false
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} finally {
    if (isset($conn))
        $conn->close();
}
?>