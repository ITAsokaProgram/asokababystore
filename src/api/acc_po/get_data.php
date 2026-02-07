<?php
header('Content-Type: application/json');
include '../../../aa_kon_sett.php';
$input = json_decode(file_get_contents('php://input'), true);
$kode_supp = isset($input['kode_supp']) ? trim($input['kode_supp']) : ''; 
$kode_area = $input['kode_area'] ?? '';
$manual_stores = $input['kd_store'] ?? []; 
$page = isset($input['page']) ? (int)$input['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;
if (empty($kode_supp)) {
    echo json_encode(['success' => false, 'error' => 'Supplier wajib dipilih']);
    exit;
}
$target_stores = [];
try {
    if (!empty($kode_area)) {
        $stmtArea = $conn->prepare("SELECT Nm_Alias FROM kode_store WHERE kode_Area = ? AND Nm_Alias IS NOT NULL AND Nm_Alias != '' ORDER BY Kd_Store ASC");
        $stmtArea->bind_param("s", $kode_area);
        $stmtArea->execute();
        $res = $stmtArea->get_result();
        while($row = $res->fetch_assoc()) {
            $target_stores[] = $row['Nm_Alias']; 
        }
        if (empty($target_stores)) {
            echo json_encode(['success' => false, 'error' => "Tidak ada cabang aktif (Alias) di Area $kode_area"]);
            exit;
        }
    } elseif (!empty($manual_stores)) {
        $target_stores = $manual_stores;
    } else {
        echo json_encode(['success' => false, 'error' => 'Pilih Area atau Cabang']);
        exit;
    }
    $store_placeholders = implode(',', array_fill(0, count($target_stores), '?'));
    $types = str_repeat('s', count($target_stores));
    $sqlHeaders = "SELECT Nm_Alias, Nm_Store FROM kode_store WHERE Nm_Alias IN ($store_placeholders) ORDER BY Kd_Store ASC";
    $stmtHead = $conn->prepare($sqlHeaders);
    $stmtHead->bind_param($types, ...$target_stores);
    $stmtHead->execute();
    $resHead = $stmtHead->get_result();
    $headers = [];
    while($row = $resHead->fetch_assoc()) {
        $headers[] = ['code' => $row['Nm_Alias'], 'name' => $row['Nm_Alias']]; 
    }
    $params = $target_stores;
    $params[] = $kode_supp;
    $paramTypes = $types . "s"; 
    $sqlPLU = "SELECT DISTINCT plu 
               FROM sisa_stok 
               WHERE cabang IN ($store_placeholders) 
               AND kode_supp = ? 
               ORDER BY plu ASC 
               LIMIT ? OFFSET ?";
    $paramsPLU = $params;
    $paramsPLU[] = $limit;
    $paramsPLU[] = $offset;
    $typesPLU = $paramTypes . "ii";
    $stmtPLU = $conn->prepare($sqlPLU);
    $stmtPLU->bind_param($typesPLU, ...$paramsPLU);
    $stmtPLU->execute();
    $resPLU = $stmtPLU->get_result();
    $target_plus = [];
    while($row = $resPLU->fetch_assoc()) {
        $target_plus[] = $row['plu'];
    }
    if(empty($target_plus)) {
        echo json_encode([
            'success' => true,
            'headers' => $headers,
            'data' => [],
            'has_more' => false
        ]);
        exit;
    }
    $plu_placeholders = implode(',', array_fill(0, count($target_plus), '?'));
    $sqlData = "SELECT * FROM sisa_stok 
                WHERE cabang IN ($store_placeholders) 
                AND kode_supp = ? 
                AND plu IN ($plu_placeholders)";
    $finalParams = array_merge($target_stores, [$kode_supp], $target_plus);
    $finalTypes = $types . "s" . str_repeat('s', count($target_plus));
    $stmtData = $conn->prepare($sqlData);
    $stmtData->bind_param($finalTypes, ...$finalParams);
    $stmtData->execute();
    $resData = $stmtData->get_result();
    $groupedData = [];
    while($row = $resData->fetch_assoc()) {
        $plu = $row['plu'];
        if (!isset($groupedData[$plu])) {
            $groupedData[$plu] = [
                'plu' => $row['plu'],
                'barcode' => $row['barcode'],
                'descp' => $row['descp'],
                'tgl_awal' => $row['tgl_awal'],
                'tgl_akhir' => $row['tgl_akhir'],
                'h_beli' => (float)$row['h_beli'],
                'h_jual' => (float)$row['h_jual'],
                'branches' => [],
                'summary' => [
                    'Total_PO' => 0, 'Total_PJ' => 0, 'Total_SS' => 0, 'Total_Rp' => 0
                ]
            ];
        }
        $groupedData[$plu]['branches'][$row['cabang']] = [
            'penjualan'    => $row['penjualan'],      
            'stok_akhir'   => $row['stok_akhir'],     
            'po_by_system' => $row['po_by_system'],   
            'po_by_md'     => $row['po_by_md'],       
            'penjualan_s'  => null,                   
            'mutasi'       => $row['Mutasi'],         
            'sudah_po'     => $row['sudah_PO']        
        ];
        $groupedData[$plu]['summary']['Total_PO'] += (int)$row['Total_PO'];
        $groupedData[$plu]['summary']['Total_PJ'] += (int)$row['Total_PJ'];
        $groupedData[$plu]['summary']['Total_SS'] += (int)$row['Total_SS'];
        $groupedData[$plu]['summary']['Total_Rp'] += (float)$row['Total_Rp'];
    }
    foreach ($groupedData as &$item) {
        if ($item['summary']['Total_PJ'] > 0) {
            $item['summary']['Rasio'] = round($item['summary']['Total_SS'] / $item['summary']['Total_PJ'], 2);
        } else {
            $item['summary']['Rasio'] = 0;
        }
    }
    echo json_encode([
        'success' => true,
        'page' => $page,
        'headers' => $headers,
        'data' => array_values($groupedData),
        'has_more' => count($target_plus) === $limit
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>