<?php
session_start();
ini_set('display_errors', 0);
header('Content-Type: application/json');
require_once __DIR__ . '/../../../aa_kon_sett.php';
try {
    if (!$conn)
        throw new Exception("Koneksi Database Gagal");
    $search_query = isset($_GET['search_query']) ? trim($_GET['search_query']) : '';
    $kd_store = isset($_GET['kd_store']) ? $_GET['kd_store'] : 'all';
    $status_bukpot = isset($_GET['status_bukpot']) ? $_GET['status_bukpot'] : 'all';
    $whereClauses = ["ps.visibilitas = 'On'"];
    $params = [];
    $types = "";
    if ($kd_store != 'all') {
        $whereClauses[] = "ps.kode_cabang = ?";
        $params[] = $kd_store;
        $types .= "s";
    }
    if ($status_bukpot === 'sudah') {
        $whereClauses[] = "(ps.nomor_bukpot IS NOT NULL AND ps.nomor_bukpot != '')";
    } elseif ($status_bukpot === 'belum') {
        $whereClauses[] = "(ps.nomor_bukpot IS NULL OR ps.nomor_bukpot = '')";
    }
    if (!empty($search_query)) {
        $search_raw = $search_query;
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
            $orConditions[] = "ps.$col LIKE ?";
            $params[] = $term_text;
            $types .= "s";
        }
        if ($is_numeric_search) {
            $numericColumns = ['nilai_program', 'nilai_transfer', 'dpp', 'ppn', 'pph'];
            foreach ($numericColumns as $col) {
                $orConditions[] = "ps.$col LIKE ?";
                $params[] = $term_numeric;
                $types .= "s";
            }
        }
        if (!empty($orConditions)) {
            $whereClauses[] = "(" . implode(" OR ", $orConditions) . ")";
        }
    }
    $sqlWhere = implode(" AND ", $whereClauses);
    $sql = "SELECT ps.* FROM program_supplier ps 
            WHERE $sqlWhere 
            ORDER BY ps.dibuat_pada DESC";
    $stmt = $conn->prepare($sql);
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
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} finally {
    if (isset($conn))
        $conn->close();
}
?>