<?php
header('Content-Type: application/json');
include '../../../aa_kon_sett.php';
$input = json_decode(file_get_contents('php://input'), true);
$kd_stores = $input['kd_store'] ?? [];
$kode_supp = $input['kode_supp'] ?? '';
$page = isset($input['page']) ? (int) $input['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;
if (empty($kd_stores) || empty($kode_supp)) {
    echo json_encode(['success' => false, 'error' => 'Parameter tidak lengkap']);
    exit;
}
$store_placeholders = implode(',', array_fill(0, count($kd_stores), '?'));
$types = str_repeat('s', count($kd_stores));
$params = $kd_stores;
try {
    $store_headers = [];
    $sqlStore = "SELECT Kd_Store, Nm_Alias FROM kode_store WHERE Kd_Store IN ($store_placeholders) ORDER BY Nm_Alias ASC";
    $stmtStore = $conn->prepare($sqlStore);
    $stmtStore->bind_param($types, ...$params);
    $stmtStore->execute();
    $resStore = $stmtStore->get_result();
    while ($s = $resStore->fetch_assoc()) {
        $store_headers[] = ['code' => $s['Kd_Store'], 'name' => $s['Nm_Alias']];
    }
    $total_items = 0;
    if ($page === 1) {
        $countParams = $params;
        $countParams[] = $kode_supp;
        $countTypes = $types . 's';
        $sqlCount = "SELECT COUNT(DISTINCT plu) as total 
                     FROM master_backup 
                     WHERE KD_STORE IN ($store_placeholders) 
                     AND VENDOR = ?
                     AND DATE(TGL_BACKUP) = CURDATE()";
        $stmtCount = $conn->prepare($sqlCount);
        $stmtCount->bind_param($countTypes, ...$countParams);
        $stmtCount->execute();
        $resCount = $stmtCount->get_result();
        if ($rowC = $resCount->fetch_assoc()) {
            $total_items = $rowC['total'];
        }
    }
    $paramsPLU = $kd_stores;
    $paramsPLU[] = $kode_supp;
    $typesPLU = $types . 's';
    $sqlPLU = "SELECT DISTINCT plu 
               FROM master_backup 
               WHERE KD_STORE IN ($store_placeholders) 
               AND VENDOR = ? 
               AND DATE(TGL_BACKUP) = CURDATE() 
               LIMIT ? OFFSET ?";
    $paramsPLU[] = $limit;
    $paramsPLU[] = $offset;
    $typesPLU .= 'ii';
    $stmtPLU = $conn->prepare($sqlPLU);
    $stmtPLU->bind_param($typesPLU, ...$paramsPLU);
    $stmtPLU->execute();
    $resPLU = $stmtPLU->get_result();
    $target_plus = [];
    while ($row = $resPLU->fetch_assoc()) {
        $target_plus[] = $row['plu'];
    }
    if (empty($target_plus)) {
        echo json_encode([
            'success' => true,
            'page' => $page,
            'headers' => $store_headers,
            'data' => [],
            'has_more' => false
        ]);
        exit;
    }
    $plu_placeholders = implode(',', array_fill(0, count($target_plus), '?'));
    $sqlDetail = "SELECT KD_STORE, plu, ITEM_N as barcode, DESCP as nama_barang, ON_HAND1 as qty, AVG_COST
                  FROM master_backup 
                  WHERE KD_STORE IN ($store_placeholders) 
                  AND plu IN ($plu_placeholders)
                  AND VENDOR = ?
                  AND DATE(TGL_BACKUP) = CURDATE() 
                  ORDER BY DESCP ASC";
    $typesDetail = $types . str_repeat('s', count($target_plus)) . 's';
    $paramsDetail = array_merge($kd_stores, $target_plus, [$kode_supp]);
    $stmtDetail = $conn->prepare($sqlDetail);
    $stmtDetail->bind_param($typesDetail, ...$paramsDetail);
    $stmtDetail->execute();
    $resultDetail = $stmtDetail->get_result();
    $items = [];
    while ($row = $resultDetail->fetch_assoc()) {
        $plu = $row['plu'];
        $store = $row['KD_STORE'];
        $qty = floatval($row['qty']);
        $cost = floatval($row['AVG_COST'] ?? 0);
        $total_row_rp = round($qty * $cost);
        if (!isset($items[$plu])) {
            $items[$plu] = [
                'plu' => $plu,
                'barcode' => $row['barcode'],
                'nama_barang' => $row['nama_barang'],
                'stok_per_store' => [],
                'total_value_rp' => 0
            ];
        }
        $items[$plu]['stok_per_store'][$store] = $qty;
        $items[$plu]['total_value_rp'] += $total_row_rp;
    }
    echo json_encode([
        'success' => true,
        'page' => $page,
        'headers' => $store_headers,
        'data' => array_values($items),
        'total_items' => (int) $total_items,
        'has_more' => count($target_plus) === $limit
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>