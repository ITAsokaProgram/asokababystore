<?php

include '../../../aa_kon_sett.php';
// Penting: Tutup sesi secepat mungkin setelah mendapatkan data yang dibutuhkan dari session
if (session_status() === PHP_SESSION_ACTIVE) {
    session_write_close();
}

header("Content-Type:application/json");
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$range = $_GET['range'] ?? 'day';
$tanggal = $_GET['tanggal'] ?? null;
$kd_cust = $_GET['kd_cust'] ?? null;
$kd_store = $_GET['cabang'] ?? null;
$inStore = $kd_store && $kd_store !== 'all' ? "'" . implode("','", explode(',', $kd_store)) . "'" : null;

function rangeParameter($rangeFilter, $date)
{
    $baseDate = preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) ? $date : date('Y-m-d', strtotime('-1 day'));

    switch ($rangeFilter) {
        case 'day':
            $start_date = $baseDate;
            $end_date = $baseDate;
            break;
        case 'week':
            $start_date = date('Y-m-d', strtotime($baseDate . ' -6 days'));
            $end_date = $baseDate;
            break;
        case 'month':
            $start_date = date('Y-m-d', strtotime($baseDate . ' -30 days'));
            $end_date = $baseDate;
            break;
        default:
            $start_date = date('Y-m-d', strtotime('-1 day'));
            $end_date = date('Y-m-d', strtotime('-1 day'));
            break;
    }
    return "BETWEEN '$start_date' AND '$end_date'";
}

function forQuery($query, $conn, $params = [])
{
    $sql = $conn->prepare($query);
    if (!$sql) {
        http_response_code(500);
        echo json_encode(['error' => 'Prepare failed: ' . $conn->error]);
        exit;
    }

    if (!empty($params)) {
        $types = str_repeat('s', count($params));
        $sql->bind_param($types, ...$params);
    }

    if (!$sql->execute()) {
        http_response_code(500);
        echo json_encode(['error' => 'Execution failed: ' . $sql->error]);
        exit;
    }

    $result = $sql->get_result();
    $data = $result->fetch_all(MYSQLI_ASSOC);
    $sql->close();
    return $data;
}

$whereDate = rangeParameter($range, $tanggal);

if (!$kd_cust) {
    $storeFilterPK = $inStore ? "AND pk.kd_store IN ($inStore)" : "";
    $storeFilterPM = $inStore ? "AND pm.kd_store IN ($inStore)" : "";

    // -- PERUBAHAN DI SINI: Mengganti WITH clause dengan Derived Table --
    $sql = "
    SELECT 
        c.kd_cust,
        c.nama_cust,
        tr.store_kode,
        tr.store_alias_pk,
        IFNULL(tp.total_poin_pk, 0) + IFNULL(tpm.total_poin_pm, 0) AS total_poin_pk_pm,
        IFNULL(pt.total_poin_pt, 0) AS poin_trans,
        (IFNULL(tp.total_poin_pk, 0) + IFNULL(tpm.total_poin_pm, 0) - IFNULL(pt.total_poin_pt, 0)) AS sisa_poin,
        tr.jumlah_transaksi AS T_Trans,
        u.status_upload,
        u.folder,
        u.uploaded_at
    FROM customers c
    -- 2. Join HANYA pada customer yang aktif menggunakan subquery (derived table)
    INNER JOIN (
        -- 1. Ambil HANYA customer yang aktif pada rentang tanggal yang dipilih
        SELECT kd_cust FROM point_kasir pk WHERE pk.tanggal $whereDate $storeFilterPK
        UNION
        SELECT kd_cust FROM point_manual pm WHERE pm.tgl_trans $whereDate $storeFilterPM
    ) AS ac ON c.kd_cust = ac.kd_cust
    
    -- 3. Hitung transaksi HANYA pada rentang tanggal yang dipilih
    LEFT JOIN (
        SELECT 
            pk.kd_cust,
            COUNT(pk.kd_cust) AS jumlah_transaksi,
            GROUP_CONCAT(DISTINCT pk.kd_store SEPARATOR ',') AS store_kode,
            GROUP_CONCAT(DISTINCT ks.Nm_Alias SEPARATOR ', ') AS store_alias_pk
        FROM point_kasir pk
        LEFT JOIN kode_store ks ON pk.kd_store = ks.kd_store
        WHERE pk.tanggal $whereDate $storeFilterPK
        GROUP BY pk.kd_cust
    ) AS tr ON c.kd_cust = tr.kd_cust

    -- 4. Hitung total poin seumur hidup (ini tetap berat, tapi hanya untuk customer yang aktif)
    LEFT JOIN (SELECT kd_cust, SUM(point_1) AS total_poin_pk FROM point_kasir GROUP BY kd_cust) AS tp ON c.kd_cust = tp.kd_cust
    LEFT JOIN (SELECT kd_cust, SUM(jum_point) AS total_poin_pm FROM point_manual GROUP BY kd_cust) AS tpm ON c.kd_cust = tpm.kd_cust
    LEFT JOIN (SELECT kd_cust, SUM(jum_point) AS total_poin_pt FROM point_trans GROUP BY kd_cust) AS pt ON c.kd_cust = pt.kd_cust
    
    -- Join untuk data upload
    LEFT JOIN (
        SELECT 
            kd_cust,
            MAX(status) AS status_upload,
            MAX(uploaded_at) AS uploaded_at,
            GROUP_CONCAT(file_id SEPARATOR ',') AS folder
        FROM uploads
        GROUP BY kd_cust
    ) AS u ON c.kd_cust = u.kd_cust
    ORDER BY T_Trans DESC;
    ";

    $data1 = forQuery($sql, $conn);
    echo json_encode(['data' => $data1]);

} else {
    // Query Detail (tidak ada perubahan, sudah OK)
    $storeFilterDetail = $inStore ? "AND pk.kd_store IN ($inStore)" : "";
    $storeFilterManual = $inStore ? "AND pm.kd_store IN ($inStore)" : "";
    
    $sqlDetail = "
    SELECT 
        'Kasir' AS sumber, pk.kd_cust, DATE_FORMAT(pk.tanggal, '%d-%m-%Y') AS tanggal, pk.jam,
        pk.no_faktur AS no_trans, pk.kode_kasir AS user, pk.nama_kasir AS kasir,
        ks.Nm_Alias AS cabang, pk.point_1 AS jumlah_point, pk.belanja as nominal, 'Detail' AS keterangan_struk
    FROM point_kasir pk
    LEFT JOIN kode_store ks ON pk.kd_store = ks.Kd_Store
    WHERE pk.kd_cust = ? AND pk.tanggal $whereDate $storeFilterDetail

    UNION ALL

    SELECT 
        'Back Office' AS sumber, pm.kd_cust, DATE_FORMAT(pm.tgl_trans, '%d-%m-%Y') AS tanggal, pm.jam,
        pm.no_trans, pm.kd_user AS user, NULL AS kasir, ks.Nm_Alias AS cabang,
        pm.jum_point AS jumlah_point, pm.belanja as nominal, 'Manual' AS keterangan_struk
    FROM point_manual pm
    LEFT JOIN kode_store ks ON pm.kd_store = ks.kd_store
    WHERE pm.kd_cust = ? AND pm.tgl_trans $whereDate $storeFilterManual
    ORDER BY tanggal ASC, jam ASC";

    $data2 = forQuery($sqlDetail, $conn, [$kd_cust, $kd_cust]);
    echo json_encode(['detail' => $data2]);
}

$conn->close();