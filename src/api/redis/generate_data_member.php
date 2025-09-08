<?php
require_once __DIR__ . "/../../../config.php";
require_once __DIR__ . "/../../../redis.php";

// Redis keys
$metaKey = "member:poin:meta";
$prefix  = "member:poin:page:";

// Hitung TTL ke jam 07:00 pagi besok
$now     = new DateTime('now', new DateTimeZone('Asia/Jakarta'));
$next7am = new DateTime('tomorrow 06:55', new DateTimeZone('Asia/Jakarta'));
$ttl     = $next7am->getTimestamp() - $now->getTimestamp();

try {
    // Query utama
    $stmt1 = $conn->prepare("
        SELECT
            c.kd_cust              AS kode_member,
            c.nama_cust            AS nama_lengkap,
            c.jenis_kel            AS jenis_kelamin,
            c.Kota                 AS kota_domisili,
            c.email                AS alamat_email,
            c.tgl_daftar           AS tanggal_registrasi,
            c.tgl_lahir            AS tanggal_lahir,
            c.upd_from_web         AS terakhir_update_web,
            t2.last_trans          AS tgl_trans_terakhir,
            IFNULL(tp.total_poin_pk, 0) + IFNULL(tpm.total_poin_pm, 0) - IFNULL(pt.total_poin_pt, 0) AS total_poin,
            s.Nm_Alias             AS nama_cabang,
            CASE
                WHEN t2.last_trans >= CURDATE() - INTERVAL 3 MONTH THEN 'Aktif'
                WHEN t2.last_trans <= CURDATE() - INTERVAL 3 MONTH THEN 'Non-Aktif'
                ELSE 'Member Lama Non-Aktif'
            END AS status_aktif
        FROM customers c
        LEFT JOIN (
            SELECT kd_cust, MAX(tgl_trans) AS last_trans, kd_store
            FROM (
                SELECT kd_cust, tgl_trans, kd_store FROM trans_b
                WHERE kd_cust IS NOT NULL AND kd_cust NOT IN ('898989','','999999999')
                UNION ALL
                SELECT kd_cust, tanggal, kd_branch FROM point_kasir
                WHERE kd_cust IS NOT NULL AND kd_cust NOT IN ('898989','','999999999')
                UNION ALL
                SELECT kd_cust, tgl_trans, kd_store FROM point_manual
                WHERE kd_cust IS NOT NULL AND kd_cust NOT IN ('898989','','999999999')
                UNION ALL
                SELECT kd_cust, tgl_trans, kd_branch FROM point_trans
                WHERE kd_cust IS NOT NULL AND kd_cust NOT IN ('898989','','999999999')
            ) AS all_trans
            GROUP BY kd_cust
        ) t2 ON c.kd_cust = t2.kd_cust
        LEFT JOIN kode_store s ON t2.kd_store = s.Kd_Store
        LEFT JOIN (
            SELECT kd_cust, SUM(point_1) AS total_poin_pk
            FROM point_kasir
            WHERE kd_cust NOT IN ('898989','','999999999')
            GROUP BY kd_cust
        ) AS tp ON c.kd_cust = tp.kd_cust
        LEFT JOIN (
            SELECT kd_cust, SUM(jum_point) AS total_poin_pm
            FROM point_manual
            WHERE kd_cust NOT IN ('898989','','999999999')
            GROUP BY kd_cust
        ) AS tpm ON c.kd_cust = tpm.kd_cust
        LEFT JOIN (
            SELECT kd_cust, SUM(jum_point) AS total_poin_pt
            FROM point_trans
            WHERE kd_cust NOT IN ('898989','','999999999')
            GROUP BY kd_cust
        ) AS pt ON c.kd_cust = pt.kd_cust
        WHERE c.kd_cust NOT IN ('','898989','999999999')
        ORDER BY total_poin DESC
    ");

    $stmt1->execute();
    $result1 = $stmt1->get_result();

    // Hapus key lama
    $keys = $redis->keys($prefix . "*");
    if (!empty($keys)) {
        $redis->del($keys);
    }
    $redis->del($metaKey);

    // Simpan data per 1000 row
    $chunkSize = 1000;
    $page = 1;
    $count = 0;
    $buffer = [];

    while ($row = $result1->fetch_assoc()) {
        $buffer[] = $row;
        $count++;

        if (count($buffer) >= $chunkSize) {
            $redis->setex($prefix . $page, $ttl, json_encode($buffer, JSON_UNESCAPED_UNICODE));
            $page++;
            $buffer = [];
        }
    }

    // simpan sisa buffer
    if (!empty($buffer)) {
        $redis->setex($prefix . $page, $ttl, json_encode($buffer, JSON_UNESCAPED_UNICODE));
    }

    // Simpan meta info
    $meta = [
        "success" => true,
        "message" => "Data berhasil di-cache",
        "total"   => $count,
        "pages"   => $page,
        "chunk_size" => $chunkSize,
        "updated" => date('Y-m-d H:i:s')
    ];
    $redis->setex($metaKey, $ttl, json_encode($meta));

    echo date('Y-m-d H:i:s') . " - Redis updated: {$page} pages, {$count} rows\n";

} catch (Exception $e) {
    echo date('Y-m-d H:i:s') . " - Redis error: " . $e->getMessage() . "\n";
} finally {
    if (isset($stmt1)) $stmt1->close();
    if (isset($conn)) $conn->close();
}
