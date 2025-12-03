<?php
session_start();
ini_set('display_errors', 0);
header('Content-Type: application/json');
require_once __DIR__ . '/../../../aa_kon_sett.php';

try {
    if (!$conn) {
        throw new Exception("Koneksi Database Gagal");
    }

    // 1. Ambil Parameter Pagination & Filter
    $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
    $limit = 20; // Sesuai request: 20 item
    $offset = ($page - 1) * $limit;

    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $filterDate = isset($_GET['date']) ? trim($_GET['date']) : '';

    // 2. Base Query
    $whereClauses = ["1=1"];
    $params = [];
    $types = "";

    // 3. Logic Filter Tanggal (jika ada)
    if (!empty($filterDate)) {
        $whereClauses[] = "fp.tgl_nota = ?";
        $params[] = $filterDate;
        $types .= "s";
    }

    // 4. Logic Search (Menangani Text & Nominal dengan titik/koma)
    if (!empty($search)) {
        // Bersihkan format angka (hapus titik dan koma untuk pencarian nominal)
        $cleanNumber = str_replace(['.', ','], '', $search);
        $isNumeric = is_numeric($cleanNumber);

        $searchLike = "%" . $search . "%";

        $clause = "(
            fp.no_faktur LIKE ? OR 
            fp.nama_supplier LIKE ? OR 
            ks.nm_alias LIKE ? OR
            fp.status LIKE ?
        ";

        // Tambahkan parameter untuk text search
        $params[] = $searchLike;
        $params[] = $searchLike;
        $params[] = $searchLike;
        $params[] = $searchLike;
        $types .= "ssss";

        // Jika input terlihat seperti angka, cari juga di kolom nominal
        if ($isNumeric && $cleanNumber != '') {
            // CAST digunakan agar bisa mencari partial match pada angka, misal ketik "150" ketemu "150000"
            // Atau jika ingin exact match gunakan: fp.dpp = ?
            $clause .= " OR CAST(fp.dpp AS CHAR) LIKE ? 
                         OR CAST(fp.dpp_nilai_lain AS CHAR) LIKE ? 
                         OR CAST(fp.ppn AS CHAR) LIKE ? 
                         OR CAST(fp.total_terima_fp AS CHAR) LIKE ?";

            $numLike = "%" . $cleanNumber . "%";
            $params[] = $numLike;
            $params[] = $numLike;
            $params[] = $numLike;
            $params[] = $numLike;
            $types .= "ssss";
        }

        $clause .= ")";
        $whereClauses[] = $clause;
    }

    $sqlWhere = implode(" AND ", $whereClauses);

    // 5. Query Utama
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

    // Tambahkan limit & offset ke params
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

    // Cek apakah masih ada data untuk page selanjutnya (opsional, tapi bagus untuk UI)
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