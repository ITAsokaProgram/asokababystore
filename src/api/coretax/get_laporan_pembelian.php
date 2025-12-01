<?php
session_start();
include '../../../aa_kon_sett.php';

register_shutdown_function(function () {
    $error = error_get_last();
    if ($error !== null && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR, E_RECOVERABLE_ERROR])) {
        if (!headers_sent()) {
            http_response_code(500);
            echo json_encode([
                'error' => 'Fatal Server Error. Pesan: ' . $error['message'] . ' in ' . $error['file'] . ' on line ' . $error['line']
            ]);
        }
    }
});

header('Content-Type: application/json');
$response = [
    'stores' => [],
    'tabel_data' => [],
    'pagination' => [
        'current_page' => 1,
        'total_pages' => 1,
        'total_rows' => 0,
        'offset' => 0,
        'limit' => 100,
    ],
    'error' => null,
];

try {
    $tanggal_kemarin = date('Y-m-d', strtotime('-1 day'));

    // Params Basic
    $filter_type = $_GET['filter_type'] ?? 'month';
    $bulan = $_GET['bulan'] ?? date('m');
    $tahun = $_GET['tahun'] ?? date('Y');
    $tgl_mulai = $_GET['tgl_mulai'] ?? $tanggal_kemarin;
    $tgl_selesai = $_GET['tgl_selesai'] ?? $tanggal_kemarin;
    $search_supplier = $_GET['search_supplier'] ?? '';
    $kd_store = $_GET['kd_store'] ?? 'all';
    $status_data = $_GET['status_data'] ?? 'all';

    // --- UPDATE: Parameter Baru ---
    $filter_tipe_pembelian = $_GET['filter_tipe_pembelian'] ?? 'all_pkp';

    $page = (int) ($_GET['page'] ?? 1);
    if ($page < 1)
        $page = 1;
    $limit = 100;

    $response['pagination']['limit'] = $limit;
    $response['pagination']['current_page'] = $page;
    $offset = ($page - 1) * $limit;
    $response['pagination']['offset'] = $offset;

    // Fetch Stores
    $sql_stores = "SELECT kd_store, nm_alias FROM kode_store WHERE display = 'on' ORDER BY Nm_Alias ASC";
    $result_stores = $conn->query($sql_stores);
    if ($result_stores) {
        while ($row = $result_stores->fetch_assoc()) {
            $response['stores'][] = $row;
        }
    }

    // Build Where Conditions
    if ($filter_type === 'month') {
        $where_conditions = "MONTH(p.tgl_nota) = ? AND YEAR(p.tgl_nota) = ?";
        $bind_types = 'ss';
        $bind_params = [$bulan, $tahun];
    } else {
        $where_conditions = "DATE(p.tgl_nota) BETWEEN ? AND ?";
        $bind_types = 'ss';
        $bind_params = [$tgl_mulai, $tgl_selesai];
    }

    if ($kd_store != 'all') {
        $where_conditions .= " AND p.kode_store = ?";
        $bind_types .= 's';
        $bind_params[] = $kd_store;
    }

    // --- UPDATE: Logic Filter Tipe Pembelian ---
    switch ($filter_tipe_pembelian) {
        case 'pkp':
            $where_conditions .= " AND p.ppn != 0";
            break;
        case 'non_pkp':
            $where_conditions .= " AND p.ppn = 0";
            break;
        case 'btkp':
            $where_conditions .= " AND p.is_btkp = 1";
            break;
        case 'non_btkp':
            $where_conditions .= " AND (p.is_btkp = 0 OR p.is_btkp IS NULL)";
            break;
        case 'all_pkp':
        case 'all_btkp':
        default:
            // Tampilkan semua (no filter)
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
            OR p.nsfp LIKE ? 
            OR p.no_faktur LIKE ? 
            OR CAST(p.dpp AS CHAR) LIKE ? 
            OR CAST(p.dpp_nilai_lain AS CHAR) LIKE ? 
            OR CAST(p.ppn AS CHAR) LIKE ?
            OR CAST(p.total_terima_fp AS CHAR) LIKE ?
        )";
        $bind_types .= 'ssssssss';
        $termRaw = '%' . $search_raw . '%';
        $termNumeric = '%' . $search_numeric . '%';
        $bind_params[] = $termRaw;
        $bind_params[] = $termRaw;
        $bind_params[] = $termRaw;
        $bind_params[] = $termRaw;
        $bind_params[] = $termNumeric;
        $bind_params[] = $termNumeric;
        $bind_params[] = $termNumeric;
        $bind_params[] = $termNumeric;
    }

    $sql_count = "SELECT COUNT(DISTINCT p.id) as total 
                  FROM ff_pembelian p 
                  LEFT JOIN ff_coretax c ON p.dpp = c.harga_jual AND p.ppn = c.ppn AND p.kode_store = c.kode_store
                  LEFT JOIN ff_faktur_pajak f ON (
                        p.no_faktur = f.no_faktur 
                        OR 
                        (p.dpp = f.dpp AND p.ppn = f.ppn AND p.kode_store = f.kode_store)
                  )
                  WHERE $where_conditions";

    $stmt_count = $conn->prepare($sql_count);
    if ($stmt_count === false)
        throw new Exception("Prepare failed (count): " . $conn->error);
    $stmt_count->bind_param($bind_types, ...$bind_params);
    $stmt_count->execute();
    $result_count = $stmt_count->get_result();
    $total_rows = $result_count->fetch_assoc()['total'] ?? 0;
    $stmt_count->close();

    $response['pagination']['total_rows'] = (int) $total_rows;
    $response['pagination']['total_pages'] = ceil($total_rows / $limit);

    $sql_data = "
        SELECT 
            p.id,
            p.nama_supplier, 
            p.tgl_nota, 
            p.no_faktur, 
            p.dpp_nilai_lain,
            p.dpp, 
            p.ppn, 
            p.total_terima_fp,
            p.ada_di_coretax,
            p.tipe_nsfp,
            p.is_btkp, 
            ks.Nm_Alias,
            p.nsfp, 
            CONCAT_WS(',', 
                GROUP_CONCAT(
                    DISTINCT
                    IF(c.nsfp IS NOT NULL,
                        CONCAT(
                            c.nsfp,
                            '|',
                            CASE 
                                WHEN p_used_c.id IS NOT NULL AND p_used_c.id != p.id THEN 'USED' 
                                ELSE 'AVAILABLE' 
                            END,
                            '|',
                            IFNULL(p_used_c.no_faktur, ''),
                            '|CORETAX|VALUE|',
                            REPLACE(IFNULL(c.nama_penjual, ''), '|', ' ')
                        ),
                        NULL
                    )
                    SEPARATOR ','
                ),
                GROUP_CONCAT(
                    DISTINCT
                    IF(f.nsfp IS NOT NULL,
                        CONCAT(
                            f.nsfp,
                            '|',
                            CASE 
                                WHEN p_used_f.id IS NOT NULL AND p_used_f.id != p.id THEN 'USED' 
                                ELSE 'AVAILABLE' 
                            END,
                            '|',
                            IFNULL(p_used_f.no_faktur, ''),
                            '|FISIK|',
                            IF(p.no_faktur = f.no_faktur, 'INVOICE', 'VALUE'),
                            '|',
                            REPLACE(IFNULL(f.nama_supplier, ''), '|', ' ')
                        ),
                        NULL
                    )
                    SEPARATOR ','
                )
            ) as candidate_nsfps
        FROM ff_pembelian p
        LEFT JOIN kode_store ks ON p.kode_store = ks.Kd_Store
        LEFT JOIN ff_coretax c ON p.dpp = c.harga_jual AND p.ppn = c.ppn AND p.kode_store = c.kode_store
        LEFT JOIN ff_pembelian p_used_c ON c.nsfp = p_used_c.nsfp AND p_used_c.ada_di_coretax = 1
        LEFT JOIN ff_faktur_pajak f ON (
            p.no_faktur = f.no_faktur 
            OR 
            (p.dpp = f.dpp AND p.ppn = f.ppn AND p.kode_store = f.kode_store)
        )
        LEFT JOIN ff_pembelian p_used_f ON f.nsfp = p_used_f.nsfp AND p_used_f.ada_di_coretax = 1
        WHERE $where_conditions
        GROUP BY p.id
        ORDER BY p.tgl_nota DESC, p.no_faktur ASC
        LIMIT ? OFFSET ?
    ";

    $bind_types .= 'ii';
    $bind_params[] = $limit;
    $bind_params[] = $offset;

    $stmt_data = $conn->prepare($sql_data);
    if ($stmt_data === false)
        throw new Exception("Prepare failed (data): " . $conn->error);
    $stmt_data->bind_param($bind_types, ...$bind_params);
    $stmt_data->execute();
    $result_data = $stmt_data->get_result();

    while ($row = $result_data->fetch_assoc()) {
        foreach ($row as $key => $value) {
            if (is_string($value)) {
                $row[$key] = iconv('UTF-8', 'UTF-8//IGNORE', $value);
            }
        }
        if (!empty($row['candidate_nsfps'])) {
            $row['candidate_nsfps'] = trim($row['candidate_nsfps'], ',');
        }
        $response['tabel_data'][] = $row;
    }

    $stmt_data->close();
    $conn->close();

} catch (Exception $e) {
    http_response_code(500);
    $response['error'] = $e->getMessage();
}

echo json_encode($response);
?>