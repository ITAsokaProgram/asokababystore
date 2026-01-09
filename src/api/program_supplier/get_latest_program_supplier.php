<?php
session_start();
ini_set('display_errors', 0);
header('Content-Type: application/json');
require_once __DIR__ . '/../../../aa_kon_sett.php';
try {
    if (!$conn)
        throw new Exception("Koneksi Database Gagal");
    $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
    $limit = 20;
    $offset = ($page - 1) * $limit;
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $whereClauses = ["visibilitas = 'On'"];
    $params = [];
    $types = "";
    if (!empty($search)) {
        $search_raw = $search;
        $term_text = '%' . $search_raw . '%';
        $search_clean_number = str_replace(['.', ','], '', $search_raw);
        $is_numeric_search = is_numeric($search_clean_number) && $search_clean_number != '';
        $term_numeric = '%' . $search_clean_number . '%';
        $orConditions = [];
        $stringColumns = [
            'nomor_dokumen',
            'pic',
            'nama_supplier',
            'kode_cabang',
            'nama_cabang',
            'periode_program',
            'nama_program',
            'mop',
            'nsfp',
            'nomor_bukpot',
            'kd_user'
        ];
        foreach ($stringColumns as $col) {
            $orConditions[] = "$col LIKE ?";
            $params[] = $term_text;
            $types .= "s";
        }
        if ($is_numeric_search) {
            $numericColumns = [
                'nilai_program',
                'nilai_transfer',
                'dpp',
                'ppn',
                'pph'
            ];
            foreach ($numericColumns as $col) {
                $orConditions[] = "$col LIKE ?";
                $params[] = $term_numeric;
                $types .= "s";
            }
        }
        if (!empty($orConditions)) {
            $whereClauses[] = "(" . implode(" OR ", $orConditions) . ")";
        }
    }
    $sqlWhere = implode(" AND ", $whereClauses);
    $query = "SELECT * FROM program_supplier 
              WHERE $sqlWhere 
              ORDER BY dibuat_pada DESC 
              LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    $types .= "ii";
    $stmt = $conn->prepare($query);
    if (!$stmt)
        throw new Exception("SQL Error: " . $conn->error);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    echo json_encode([
        'success' => true,
        'data' => $data,
        'page' => $page,
        'has_more' => count($data) === $limit
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} finally {
    if (isset($conn))
        $conn->close();
}
?>