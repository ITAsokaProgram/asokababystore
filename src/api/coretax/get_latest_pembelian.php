<?php
session_start();
ini_set('display_errors', 0);
header('Content-Type: application/json');
require_once __DIR__ . '/../../../aa_kon_sett.php';
require_once __DIR__ . "/../../utils/Logger.php";
$logger = new AppLogger('pembelian_api.log');
try {
    if (!$conn) {
        throw new Exception("Koneksi Database Gagal");
    }
    $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
    $limit = 20;
    $offset = ($page - 1) * $limit;
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $filterDate = isset($_GET['date']) ? trim($_GET['date']) : '';
    $sortOption = isset($_GET['sort']) ? $_GET['sort'] : 'created';
    $orderClause = "fp.dibuat_pada DESC";
    if ($sortOption === 'date') {
        $orderClause = "fp.tgl_nota DESC, fp.dibuat_pada DESC";
    }
    $whereClauses = ["1=1"];
    $params = [];
    $types = "";
    if (!empty($filterDate)) {
        $whereClauses[] = "fp.tgl_nota = ?";
        $params[] = $filterDate;
        $types .= "s";
    }
    if (!empty($search)) {
        $searchLike = "%" . $search . "%";
        $cleanNumber = str_replace(['.', ','], '', $search);
        $isNumeric = is_numeric($cleanNumber) && $cleanNumber != '';
        if ($isNumeric) {
            $whereClauses[] = "(
                fp.no_invoice LIKE ? OR 
                fp.nama_supplier LIKE ? OR 
                fp.catatan LIKE ? OR 
                ks.nm_alias LIKE ? OR
                fp.status LIKE ? OR
                fp.dpp = ? OR 
                fp.dpp_nilai_lain = ? OR 
                fp.ppn = ? OR 
                fp.total_terima_fp = ?
            )";
            for ($i = 0; $i < 5; $i++) {
                $params[] = $searchLike;
                $types .= "s";
            }
            for ($i = 0; $i < 4; $i++) {
                $params[] = $cleanNumber;
                $types .= "d";
            }
        } else {
            $whereClauses[] = "(
                fp.no_invoice LIKE ? OR 
                fp.nama_supplier LIKE ? OR 
                fp.catatan LIKE ? OR 
                ks.nm_alias LIKE ? OR
                fp.status LIKE ?
            )";
            for ($i = 0; $i < 5; $i++) {
                $params[] = $searchLike;
                $types .= "s";
            }
        }
    }
    $sqlWhere = implode(" AND ", $whereClauses);
    $query = "SELECT 
                fp.id, fp.nama_supplier, fp.kode_supplier, fp.kode_store, 
                fp.tgl_nota, fp.no_invoice, fp.no_faktur, fp.catatan,
                fp.dpp, fp.dpp_nilai_lain, fp.ppn, fp.total_terima_fp,
                fp.edit_pada, fp.status, fp.nsfp, ks.nm_alias 
              FROM ff_pembelian as fp
              LEFT JOIN kode_store as ks on fp.kode_store = ks.kd_store  
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
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $row['id'] = (int) $row['id'];
        $row['dpp'] = (float) $row['dpp'];
        $row['dpp_nilai_lain'] = (float) ($row['dpp_nilai_lain'] ?? 0);
        $row['ppn'] = (float) $row['ppn'];
        $row['total_terima_fp'] = (float) ($row['total_terima_fp'] ?? 0);
        $data[] = $row;
    }
    echo json_encode([
        'success' => true,
        'data' => $data,
        'page' => $page,
        'has_more' => count($data) === $limit
    ]);
} catch (Exception $e) {
    $logger->error("Error di get_latest_pembelian.php: " . $e->getMessage());
    $logger->error("Params: " . json_encode($_GET));
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => "Terjadi kesalahan internal sistem."]);
} finally {
    if (isset($conn))
        $conn->close();
}