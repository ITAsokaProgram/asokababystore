<?php
require_once __DIR__ . '/../../../aa_kon_sett.php';
require_once __DIR__ . '/../../../auth/middleware_login.php';

header("Content-Type: application/json");

// cek token dari cookie
$token = $_COOKIE['admin_token'] ?? null;
if (!$token) {
    http_response_code(401);
    echo json_encode(['status' => false, "message" => "Unauthorize user cannot be used"]);
    exit;
}

// ambil input JSON
$jsonInput = json_decode(file_get_contents("php://input"), true);
$kd_store = $jsonInput['kd_store'] ?? null;

try {
    // query (kd_store ganti pakai placeholder ?)
    $sql = "
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
            IFNULL(tp.total_poin_pk, 0) 
              + IFNULL(tpm.total_poin_pm, 0) 
              - IFNULL(pt.total_poin_pt, 0) AS total_poin,
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
                WHERE kd_cust IS NOT NULL AND kd_cust NOT IN ('898989','','999999999') AND kd_store = ?
                UNION ALL
                SELECT kd_cust, tanggal, kd_branch FROM point_kasir
                WHERE kd_cust IS NOT NULL AND kd_cust NOT IN ('898989','','999999999') AND kd_store = ?
                UNION ALL
                SELECT kd_cust, tgl_trans, kd_store FROM point_manual
                WHERE kd_cust IS NOT NULL AND kd_cust NOT IN ('898989','','999999999') AND kd_store = ?
                UNION ALL
                SELECT kd_cust, tgl_trans, kd_branch FROM point_trans
                WHERE kd_cust IS NOT NULL AND kd_cust NOT IN ('898989','','999999999') AND kd_store = ?
            ) AS all_trans
            GROUP BY kd_cust
        ) t2 ON c.kd_cust = t2.kd_cust
        LEFT JOIN kode_store s ON t2.kd_store = s.Kd_Store
        LEFT JOIN (
            SELECT kd_cust, SUM(point_1) AS total_poin_pk, kd_store
            FROM point_kasir
            WHERE kd_cust NOT IN ('898989','','999999999') AND kd_store = ?
            GROUP BY kd_cust
        ) AS tp ON c.kd_cust = tp.kd_cust
        LEFT JOIN (
            SELECT kd_cust, SUM(jum_point) AS total_poin_pm , kd_store
            FROM point_manual
            WHERE kd_cust NOT IN ('898989','','999999999') AND kd_store = ?
            GROUP BY kd_cust
        ) AS tpm ON c.kd_cust = tpm.kd_cust
        LEFT JOIN (
            SELECT kd_cust, SUM(jum_point) AS total_poin_pt, kd_store 
            FROM point_trans
            WHERE kd_cust NOT IN ('898989','','999999999') AND kd_store = ?
            GROUP BY kd_cust
        ) AS pt ON c.kd_cust = pt.kd_cust
        WHERE c.kd_cust NOT IN ('','898989','999999999') 
          AND c.kd_store = ?
        ORDER BY total_poin DESC
    ";

    // prepare statement
    $stmt = $conn->prepare($sql);

    // bind parameter untuk kd_store (7x)
    $stmt->bind_param(
        "sssssss",
        $kd_store,
        $kd_store,
        $kd_store,
        $kd_store,
        $kd_store,
        $kd_store,
        $kd_store
    );

    $stmt->execute();
    $result = $stmt->get_result();
    $rows = $result->fetch_all(MYSQLI_ASSOC);

    echo json_encode(['status' => true, 'data' => $rows], JSON_UNESCAPED_UNICODE);

    $stmt->close();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => "error", "message" => "run error " . $e->getMessage()]);
} finally {
    $conn->close();
}
