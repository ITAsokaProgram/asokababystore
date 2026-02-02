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

    $whereClauses = ["1=1"];
    $params = [];
    $types = "";

    if (!empty($search)) {
        // Logika pencarian mirip input_pembelian (support teks dan angka)
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

    $query = "SELECT bb.*, 
                 ks.Nm_Alias as nm_alias
          FROM buku_besar bb
          LEFT JOIN kode_store ks ON bb.kode_store = ks.Kd_Store
          WHERE $sqlWhere
          ORDER BY bb.id DESC 
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
    $ids = []; // Array untuk menampung ID buku_besar yang terpilih

    while ($row = $result->fetch_assoc()) {
        $row['id'] = (int) $row['id'];
        $row['total_bayar'] = (float) $row['total_bayar'];
        $row['potongan'] = (float) $row['potongan'];
        $row['nilai_tambahan'] = (float) ($row['nilai_tambahan'] ?? 0);
        
        // Inisialisasi details_potongan sebagai array kosong (default)
        $row['details_potongan'] = [];
        
        $data[] = $row;
        $ids[] = $row['id']; // Simpan ID untuk query kedua
    }

    // --- LOGIKA BARU START: AMBIL RINCIAN POTONGAN ---
    if (!empty($ids)) {
        // Ambil semua rincian potongan yang ID induknya ada di list $ids
        // Menggunakan implode aman karena $ids berasal dari database dan sudah dicasting (int)
        $idsStr = implode(",", $ids);
        
        $sqlPot = "SELECT buku_besar_id, nominal, keterangan 
                   FROM buku_besar_potongan 
                   WHERE buku_besar_id IN ($idsStr)";
        
        $resPot = $conn->query($sqlPot);
        
        // Grouping potongan berdasarkan buku_besar_id
        $potonganMap = [];
        if ($resPot) {
            while ($p = $resPot->fetch_assoc()) {
                $potonganMap[$p['buku_besar_id']][] = [
                    'nominal' => (float) $p['nominal'],
                    'keterangan' => $p['keterangan']
                ];
            }
        }

        // Masukkan rincian potongan ke dalam data utama
        foreach ($data as &$item) {
            if (isset($potonganMap[$item['id']])) {
                $item['details_potongan'] = $potonganMap[$item['id']];
            }
        }
        unset($item); // Putus referensi
    }
    // --- LOGIKA BARU END ---

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