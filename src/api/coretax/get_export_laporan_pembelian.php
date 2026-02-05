<?php
session_start();
ini_set('display_errors', 0);
header('Content-Type: application/json');
require_once __DIR__ . '/../../../aa_kon_sett.php';

try {
    if (!$conn)
        throw new Exception("Koneksi Database Gagal");

    $tanggal_kemarin = date('Y-m-d', strtotime('-1 day'));
    $filter_type = $_GET['filter_type'] ?? 'month';
    $bulan = $_GET['bulan'] ?? date('m');
    $tahun = $_GET['tahun'] ?? date('Y');
    $tgl_mulai = $_GET['tgl_mulai'] ?? $tanggal_kemarin;
    $tgl_selesai = $_GET['tgl_selesai'] ?? $tanggal_kemarin;
    $kd_store = $_GET['kd_store'] ?? 'all';
    $status_data = $_GET['status_data'] ?? 'all';
    $filter_tipe_pembelian = $_GET['filter_tipe_pembelian'] ?? 'semua';
    $search_supplier = $_GET['search_supplier'] ?? '';

    $where_conditions = "1=1";
    $bind_types = "";
    $bind_params = [];

    if ($filter_type === 'month') {
        $where_conditions .= " AND MONTH(p.tgl_nota) = ? AND YEAR(p.tgl_nota) = ?";
        $bind_types .= 'ss';
        $bind_params[] = $bulan;
        $bind_params[] = $tahun;
    } else {
        $where_conditions .= " AND DATE(p.tgl_nota) BETWEEN ? AND ?";
        $bind_types .= 'ss';
        $bind_params[] = $tgl_mulai;
        $bind_params[] = $tgl_selesai;
    }

    if ($kd_store != 'all') {
        $where_conditions .= " AND p.kode_store = ?";
        $bind_types .= 's';
        $bind_params[] = $kd_store;
    }

    switch ($filter_tipe_pembelian) {
        case 'PKP':
            $where_conditions .= " AND p.status = 'PKP'";
            break;
        case 'NON PKP':
            $where_conditions .= " AND p.status = 'NON PKP'";
            break;
        case 'BTKP':
            $where_conditions .= " AND p.status = 'BTKP'";
            break;
    }

    if ($status_data != 'all') {
        if ($status_data == 'unlinked') {
            $where_conditions .= " AND (p.ada_di_coretax = 0 OR p.ada_di_coretax IS NULL)";
        } elseif ($status_data == 'linked_any') {
            $where_conditions .= " AND p.ada_di_coretax = 1";
        } elseif ($status_data == 'linked_coretax') {
            $where_conditions .= " AND p.ada_di_coretax = 1 AND p.tipe_nsfp LIKE '%coretax%'";
        } elseif ($status_data == 'linked_fisik') {
            $where_conditions .= " AND p.ada_di_coretax = 1 AND p.tipe_nsfp LIKE '%fisik%'";
        } elseif ($status_data == 'linked_both') {
            $where_conditions .= " AND p.ada_di_coretax = 1 AND p.tipe_nsfp LIKE '%coretax%' AND p.tipe_nsfp LIKE '%fisik%'";
        } elseif ($status_data == 'need_selection') {
            $where_conditions .= " AND (p.ada_di_coretax = 0 OR p.ada_di_coretax IS NULL) 
                                   AND (c.nsfp IS NOT NULL OR f.nsfp IS NOT NULL)";
        }
    }

    if (!empty($search_supplier)) {
        $search_raw = trim($search_supplier);
        $search_numeric = str_replace('.', '', $search_raw);
        $where_conditions .= " AND (
            p.nama_supplier LIKE ? 
            OR p.kode_supplier LIKE ? 
            OR p.catatan LIKE ? 
            OR p.nsfp LIKE ? 
            OR p.no_invoice LIKE ?  
            OR CAST(p.dpp AS CHAR) LIKE ? 
            OR CAST(p.dpp_nilai_lain AS CHAR) LIKE ? 
            OR CAST(p.ppn AS CHAR) LIKE ?
            OR CAST(p.total_terima_fp AS CHAR) LIKE ?
        )";
        $bind_types .= 'sssssssss';
        $termRaw = '%' . $search_raw . '%';
        $termNumeric = '%' . $search_numeric . '%';
        
        $bind_params[] = $termRaw;
        $bind_params[] = $termRaw;
        $bind_params[] = $termRaw;
        $bind_params[] = $termRaw;
        $bind_params[] = $termRaw;
        $bind_params[] = $termNumeric;
        $bind_params[] = $termNumeric;
        $bind_params[] = $termNumeric;
        $bind_params[] = $termNumeric;
    }

    // UPDATE QUERY: Memastikan kolom ada_di_coretax, tipe_nsfp, dan nsfp terpilih
    $query = "SELECT 
                p.tgl_nota, 
                p.no_invoice, 
                p.nama_supplier, 
                p.catatan, 
                p.kode_store, 
                p.status, 
                p.dpp, 
                p.dpp_nilai_lain, 
                p.ppn, 
                p.total_terima_fp,
                ks.nm_alias,
                p.ada_di_coretax,
                p.tipe_nsfp,
                p.nsfp
              FROM ff_pembelian as p
              LEFT JOIN kode_store as ks on p.kode_store = ks.kd_store
              LEFT JOIN ff_coretax c ON p.dpp = c.harga_jual AND p.ppn = c.ppn 
              LEFT JOIN ff_faktur_pajak f ON (
                  p.no_invoice = f.no_invoice 
                  OR 
                  (p.dpp = f.dpp AND p.ppn = f.ppn)
              )
              WHERE $where_conditions
              GROUP BY p.id 
              ORDER BY p.tgl_nota DESC, p.dibuat_pada DESC";

    $stmt = $conn->prepare($query);
    if (!empty($bind_params)) {
        $stmt->bind_param($bind_types, ...$bind_params);
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