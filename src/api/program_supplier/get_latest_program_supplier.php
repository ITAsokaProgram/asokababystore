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

    $whereClauses = ["1=1"];
    $params = [];
    $types = "";

    if (!empty($search)) {
        $term = '%' . $search . '%';
        // Cari di dokumen, supplier, pic, atau program
        $whereClauses[] = "(nomor_dokumen LIKE ? OR nama_supplier LIKE ? OR pic LIKE ? OR nama_program LIKE ?)";
        for ($i = 0; $i < 4; $i++) {
            $params[] = $term;
            $types .= "s";
        }
    }

    $sqlWhere = implode(" AND ", $whereClauses);

    // Order by created_at terbaru
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