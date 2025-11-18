<?php

require_once __DIR__ . "/../../../config.php";
require_once __DIR__ . "/../../../redis.php";

// Nama key tetap
$redisKey = "member_poin";

// Hitung TTL ke jam 7 pagi besok
$now = new DateTime('now', new DateTimeZone('Asia/Jakarta'));
$next7am = new DateTime('tomorrow 06:55', new DateTimeZone('Asia/Jakarta'));
$ttl = $next7am->getTimestamp() - $now->getTimestamp();

try {
    // Query utama tanpa LIMIT
    $stmt1 = $conn->prepare("
        SELECT
    c.kd_cust,
    c.nama_cust,
    t2.last_trans AS tgl_trans_terakhir,
    IFNULL(tp.total_poin_pk, 0) + IFNULL(tpm.total_poin_pm, 0) AS total_poin_pk_pm,
    IFNULL(tp.total_poin_pk, 0) + IFNULL(tpm.total_poin_pm, 0) - IFNULL(pt.total_poin_pt, 0) AS total_poin,
    s.Nm_Alias AS nama_cabang,
CASE
    WHEN t2.last_trans >= CURDATE() - INTERVAL 3 MONTH THEN 'Aktif'
    WHEN t2.last_trans <= CURDATE() - INTERVAL 3 MONTH THEN 'Non-Aktif'
    ELSE 'Tidak Diketahui' -- opsional, kalau null atau data aneh
END AS status_aktif
FROM customers c

-- Ambil transaksi terakhir dari semua sumber
LEFT JOIN (
    SELECT kd_cust, MAX(tgl_trans) AS last_trans, kd_store
    FROM (
        SELECT kd_cust, tgl_trans, kd_store FROM trans_b
        WHERE kd_cust IS NOT NULL AND kd_cust != '' AND kd_cust NOT IN ('898989', '89898989', '999999999')

        UNION ALL

        SELECT kd_cust, tanggal, kd_branch FROM point_kasir
        WHERE kd_cust IS NOT NULL AND kd_cust != '' AND kd_cust NOT IN ('898989', '89898989', '999999999')

        UNION ALL

        SELECT kd_cust, tgl_trans, kd_store FROM point_manual
        WHERE kd_cust IS NOT NULL AND kd_cust != '' AND kd_cust NOT IN ('898989', '89898989', '999999999')

        UNION ALL

        SELECT kd_cust, tgl_trans , kd_branch FROM point_trans
        WHERE kd_cust IS NOT NULL AND kd_cust != '' AND kd_cust NOT IN ('898989', '89898989', '999999999')
    ) AS all_trans
    GROUP BY kd_cust
) t2 ON c.kd_cust = t2.kd_cust
LEFT JOIN kode_store s ON t2.kd_store = s.Kd_Store
-- Poin dari point_kasir
LEFT JOIN (
    SELECT kd_cust, SUM(point_1) AS total_poin_pk
    FROM point_kasir
    WHERE kd_cust NOT IN ('898989', '89898989', '999999999')
    GROUP BY kd_cust
) AS tp ON c.kd_cust = tp.kd_cust

-- Poin dari point_manual
LEFT JOIN (
    SELECT kd_cust, SUM(jum_point) AS total_poin_pm
    FROM point_manual
    WHERE kd_cust NOT IN ('898989', '89898989', '999999999')
    GROUP BY kd_cust
) AS tpm ON c.kd_cust = tpm.kd_cust

-- Poin yang sudah ditukar
LEFT JOIN (
    SELECT kd_cust, SUM(jum_point) AS total_poin_pt
    FROM point_trans
    WHERE kd_cust NOT IN ('898989', '89898989', '999999999')
    GROUP BY kd_cust
) AS pt ON c.kd_cust = pt.kd_cust

-- Filter akhir
WHERE c.kd_cust != ''
  AND c.kd_cust NOT IN ('898989', '89898989', '999999999')

ORDER BY total_poin_pk_pm DESC;
    ");

    if (!$stmt1) {
        echo date('Y-m-d H:i:s') . " - Statement error: " . "\n";
        exit;
    }

    $stmt1->execute();
    $result1 = $stmt1->get_result();
    $data = [];

    while ($row = $result1->fetch_assoc()) {
        $data[] = $row;
    }

    if (count($data) === 0) {
        echo date('Y-m-d H:i:s') . " - No Data: Not Found \n";
        exit;
    }

    $response = [
        "success" => true,
        "message" => "Data berhasil diambil",
        "total" => count($data),
        "data" => $data
    ];

    // Simpan ke Redis sampai jam 7 pagi
    $redis->setex($redisKey, $ttl, json_encode($response));
    echo date('Y-m-d H:i:s') . " - Redis updated: $redisKey\n";
} catch (Exception $e) {
    echo date('Y-m-d H:i:s') . " - Redis error: " . $e->getMessage() . "\n";
} finally {
    if (isset($stmt1))
        $stmt1->close();
    if (isset($conn))
        $conn->close();
}
