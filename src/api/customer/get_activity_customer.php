<?php

include '../../../aa_kon_sett.php';
header("Content-Type:application/json");
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$range = $_GET['range'] ?? 'day';
$tanggal = $_GET['tanggal'] ?? null;
$kd_cust = $_GET['kd_cust'] ?? null;
$kd_store = $_GET['cabang'] ?? null;
$inStore = $kd_store ? "'" . implode("','", explode(',', $kd_store)) . "'" : "'defaultStore'";

function rangeParameter($rangeFilter, $date)
{
    $isValidDate = preg_match('/^\d{4}-\d{2}-\d{2}$/', $date);

    if ($isValidDate) {
        switch ($rangeFilter) {
            case 'day':
                $range = "'{$date}' AND '{$date}'";
                break;
            case 'week':
                $range = "'{$date}' - INTERVAL 6 DAY AND '{$date}'";
                break;
            case 'month':
                $range = "'{$date}' - INTERVAL 30 DAY AND '{$date}'";
                break;
        }
    } else {
        switch ($rangeFilter) {
            case 'day':
                $range = "CURDATE() - INTERVAL 1 DAY AND CURDATE()";
                break;
            case 'week':
                $range = "CURDATE() - INTERVAL 6 DAY AND CURDATE()";
                break;
            case 'month':
                $range = "CURDATE() - INTERVAL 30 DAY AND CURDATE()";
                break;
        }
    }

    // return dua nilai (karena kamu pakai di dua tabel)
    return [$range, $range];
}

function forQuery($query, $conn, $params = [])
{
    $sql = $conn->prepare($query);
    if (!$sql) {
        die("Prepare failed: " . $conn->error);
    }

    // Bind parameters jika ada
    if (!empty($params)) {
        // Buat format string (semua dianggap string: 's', atau bisa disesuaikan)
        $types = str_repeat('s', count($params));
        $sql->bind_param($types, ...$params);
    }

    $sql->execute();
    $result = $sql->get_result();

    if (!$result) {
        die("Execution failed: " . $conn->error);
    }

    $data = $result->fetch_all(MYSQLI_ASSOC);
    $sql->close();
    return $data;
}

list($wherePK, $wherePM) = rangeParameter($range, $tanggal);
if ($kd_store === "all") {
    $sql = " 
       SELECT 
    c.kd_cust,
    c.nama_cust,
    tr.store_kode,
    tr.store_alias_pk, 
    IFNULL(tp.total_poin_pk, 0) + IFNULL(tpm.total_poin_pm, 0) AS total_poin_pk_pm,
    IFNULL(pt.total_poin_pt, 0) AS poin_trans,
    IFNULL(tp.total_poin_pk, 0) + IFNULL(tpm.total_poin_pm, 0) - IFNULL(pt.total_poin_pt, 0) AS sisa_poin,
    IFNULL(tr.jumlah_transaksi, 0) + IFNULL(pm.j_pm, 0) AS T_Trans,
    u.status_upload,
    u.folder,
u.uploaded_at
FROM customers c

-- Transaksi Kasir + cabang
LEFT JOIN (
    SELECT 
        pk.kd_cust,
        GROUP_CONCAT(DISTINCT pk.kd_store ORDER BY pk.kd_store SEPARATOR ',') AS store_kode,
        COUNT(*) AS jumlah_transaksi,
        GROUP_CONCAT(DISTINCT ks.Nm_Alias ORDER BY ks.Nm_Alias SEPARATOR ', ') AS store_alias_pk
    FROM point_kasir pk
    LEFT JOIN kode_store ks ON pk.kd_store = ks.kd_store
    WHERE pk.tanggal BETWEEN $wherePK
    GROUP BY pk.kd_cust
) AS tr ON c.kd_cust = tr.kd_cust

-- Transaksi Manual
LEFT JOIN (
    SELECT kd_cust, COUNT(*) AS j_pm
    FROM point_manual
    WHERE tgl_trans BETWEEN $wherePM
    GROUP BY kd_cust
) AS pm ON c.kd_cust = pm.kd_cust

-- Poin Kasir
LEFT JOIN (
    SELECT kd_cust, SUM(point_1) AS total_poin_pk
    FROM point_kasir
    GROUP BY kd_cust
) AS tp ON c.kd_cust = tp.kd_cust

-- Poin Manual
LEFT JOIN (
    SELECT kd_cust, SUM(jum_point) AS total_poin_pm
    FROM point_manual
    GROUP BY kd_cust
) AS tpm ON c.kd_cust = tpm.kd_cust

-- Poin Penukaran
LEFT JOIN (
    SELECT kd_cust, SUM(jum_point) AS total_poin_pt
    FROM point_trans
    GROUP BY kd_cust
) AS pt ON c.kd_cust = pt.kd_cust
-- Upload file Google Drive
LEFT JOIN (
    SELECT 
        kd_cust,
        MAX(STATUS) AS status_upload,
        MAX(uploaded_at) AS uploaded_at,
        GROUP_CONCAT(file_id SEPARATOR ',') AS folder
    FROM uploads
    GROUP BY kd_cust
) AS u ON c.kd_cust = u.kd_cust
WHERE IFNULL(tr.jumlah_transaksi, 0) > 0 OR IFNULL(pm.j_pm, 0) > 0
ORDER BY T_Trans DESC";
} else {
    $sql = " 
           SELECT 
        c.kd_cust,
        c.nama_cust,
        tr.store_kode,
        tr.store_alias_pk, 
        IFNULL(tp.total_poin_pk, 0) + IFNULL(tpm.total_poin_pm, 0) AS total_poin_pk_pm,
        IFNULL(pt.total_poin_pt, 0) AS poin_trans,
        IFNULL(tp.total_poin_pk, 0) + IFNULL(tpm.total_poin_pm, 0) - IFNULL(pt.total_poin_pt, 0) AS sisa_poin,
        IFNULL(tr.jumlah_transaksi, 0) + IFNULL(pm.j_pm, 0) AS T_Trans,
        u.status_upload,
        u.folder,
    u.uploaded_at
    FROM customers c
    
    -- Transaksi Kasir + cabang
    LEFT JOIN (
        SELECT 
            pk.kd_cust,
            GROUP_CONCAT(DISTINCT pk.kd_store ORDER BY pk.kd_store SEPARATOR ',') AS store_kode,
            COUNT(*) AS jumlah_transaksi,
            GROUP_CONCAT(DISTINCT ks.Nm_Alias ORDER BY ks.Nm_Alias SEPARATOR ', ') AS store_alias_pk
        FROM point_kasir pk
        LEFT JOIN kode_store ks ON pk.kd_store = ks.kd_store
        WHERE pk.tanggal BETWEEN $wherePK
          AND pk.kd_store IN ($inStore)
        GROUP BY pk.kd_cust
    ) AS tr ON c.kd_cust = tr.kd_cust
    
    -- Transaksi Manual
    LEFT JOIN (
        SELECT kd_cust, COUNT(*) AS j_pm
        FROM point_manual
        WHERE tgl_trans BETWEEN $wherePM
          AND kd_store IN ($inStore)
        GROUP BY kd_cust
    ) AS pm ON c.kd_cust = pm.kd_cust
    
    -- Poin Kasir
    LEFT JOIN (
        SELECT kd_cust, SUM(point_1) AS total_poin_pk
        FROM point_kasir
        GROUP BY kd_cust
    ) AS tp ON c.kd_cust = tp.kd_cust
    
    -- Poin Manual
    LEFT JOIN (
        SELECT kd_cust, SUM(jum_point) AS total_poin_pm
        FROM point_manual
        GROUP BY kd_cust
    ) AS tpm ON c.kd_cust = tpm.kd_cust
    
    -- Poin Penukaran
    LEFT JOIN (
        SELECT kd_cust, SUM(jum_point) AS total_poin_pt
        FROM point_trans
        GROUP BY kd_cust
    ) AS pt ON c.kd_cust = pt.kd_cust
    -- Upload file Google Drive
    LEFT JOIN (
        SELECT 
            kd_cust,
            MAX(STATUS) AS status_upload,
            MAX(uploaded_at) AS uploaded_at,
            GROUP_CONCAT(file_id SEPARATOR ',') AS folder
        FROM uploads
        GROUP BY kd_cust
    ) AS u ON c.kd_cust = u.kd_cust
    WHERE IFNULL(tr.jumlah_transaksi, 0) > 0 OR IFNULL(pm.j_pm, 0) > 0
    ORDER BY T_Trans DESC";
}
$sqlDetail = "
SELECT 
    'Kasir' AS sumber,
    pk.kd_cust,
    DATE_FORMAT(pk.tanggal, '%d-%m-%Y') AS tanggal,
    pk.jam,
    pk.no_faktur AS no_trans,
    pk.kode_kasir AS USER,
    pk.nama_kasir AS kasir,
    ks.Nm_Alias AS cabang,
    pk.point_1 AS jumlah_point,
    pk.belanja as nominal,
    'Detail' AS keterangan_struk
FROM point_kasir pk
LEFT JOIN kode_store ks ON pk.kd_store = ks.Kd_Store
WHERE pk.kd_cust = ?  AND pk.kd_store IN($inStore)
  AND pk.tanggal BETWEEN $wherePK

UNION ALL

SELECT 
    'Back Office' AS sumber,
    pm.kd_cust,
    DATE_FORMAT(pm.tgl_trans, '%d-%m-%Y') AS tanggal,
    pm.jam,
    pm.no_trans,
    pm.kd_user AS user,
    NULL AS kasir,
    ks.Nm_Alias AS cabang,
    pm.jum_point AS jumlah_point,
    pm.belanja as nominal,
    'Manual' AS keterangan_struk
FROM point_manual pm
LEFT JOIN kode_store ks ON pm.kd_store = ks.kd_store
WHERE pm.kd_cust = ? AND pm.kd_store IN($inStore)
  AND pm.tgl_trans BETWEEN $wherePM 
ORDER BY tanggal ASC, jam ASC";

if ($kd_cust) {
    $data2 = forQuery($sqlDetail, $conn, [$kd_cust, $kd_cust]);
    echo json_encode(['detail' => $data2]);
} else {
    $data1 = forQuery($sql, $conn);
    echo json_encode([
        'data' => $data1
    ]);
}
$conn->close();

// 