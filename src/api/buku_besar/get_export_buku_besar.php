<?php
session_start();
ini_set('display_errors', 0);
header('Content-Type: application/json');
require_once __DIR__ . '/../../../aa_kon_sett.php';

try {
    if (!$conn)
        throw new Exception("Koneksi Database Gagal");

    $search = isset($_GET['search']) ? trim($_GET['search']) : '';

    $whereClauses = ["1=1"];
    $params = [];
    $types = "";

    // Logika pencarian sama persis dengan get_latest_buku_besar.php
    if (!empty($search)) {
        $cleanNumber = str_replace(['.', ','], '', $search);
        $isNumeric = is_numeric($cleanNumber) && $cleanNumber != '';
        $searchLike = "%" . $search . "%";

        if ($isNumeric) {
            $whereClauses[] = "(
                bb.no_faktur LIKE ? OR 
                bb.nama_supplier LIKE ? OR 
                bb.ket LIKE ? OR
                bb.total_bayar = ? OR
                bb.potongan = ?
            )";
            $params[] = $searchLike;
            $params[] = $searchLike;
            $params[] = $searchLike;
            $params[] = $cleanNumber;
            $params[] = $cleanNumber;
            $types .= "sssdd";
        } else {
            $whereClauses[] = "(
                bb.no_faktur LIKE ? OR 
                bb.nama_supplier LIKE ? OR 
                bb.ket LIKE ?
            )";
            $params[] = $searchLike;
            $params[] = $searchLike;
            $params[] = $searchLike;
            $types .= "sss";
        }
    }

    $sqlWhere = implode(" AND ", $whereClauses);

    // Ambil semua data tanpa LIMIT
    $query = "SELECT bb.*, 
                     ks.Nm_Alias as nm_alias,
                     ks_bayar.Nm_Alias as nm_alias_bayar
              FROM buku_besar bb
              LEFT JOIN kode_store ks ON bb.kode_store = ks.Kd_Store
              LEFT JOIN kode_store ks_bayar ON bb.store_bayar = ks_bayar.Kd_Store
              WHERE $sqlWhere
              ORDER BY bb.tanggal_bayar DESC, bb.id DESC";

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