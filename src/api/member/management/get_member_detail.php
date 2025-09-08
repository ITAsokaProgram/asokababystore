<?php
header('Content-Type: application/json');
require_once __DIR__ . ("/../../../../aa_kon_sett.php");
require_once __DIR__ . ("/../../../auth/middleware_login.php");

$token = $_COOKIE['token'] ?? null;
if (!$token) {
    http_response_code(401);
    echo json_encode(['status' => "Unauthenticated", 'message' => 'Request ditolak user tidak terdaftar']);
    exit;
}

$kd_cust = $_GET['kd_cust'] ?? '';
if ($kd_cust === '') {
    echo json_encode([
        "success" => false,
        "message" => "Kode member harus diisi"
    ]);
    exit;
}

try {
    // 1. Detail Member
    $sql_member = "
        SELECT
            c.kd_cust              AS kode_member,
            c.nama_cust            AS nama_lengkap,
            c.jenis_kel            AS jenis_kelamin,
            c.Kota                 AS kota_domisili,
            c.email                AS alamat_email,
            c.tgl_daftar           AS tanggal_registrasi,
            c.tgl_lahir            AS tanggal_lahir,
            c.upd_from_web         AS terakhir_update_web,
            ks.Nm_Alias            AS nama_cabang
        FROM customers c
        LEFT JOIN kode_store ks ON ks.kd_store = c.kd_store
        WHERE c.kd_cust = ?
    ";
    $stmt = $conn->prepare($sql_member);
    $stmt->bind_param("s", $kd_cust);
    $stmt->execute();
    $result = $stmt->get_result();
    $member = $result->fetch_assoc();

    // 2. Total Poin
    $sql_poin = "
        SELECT SUM(total) AS total_poin
        FROM (
            SELECT  SUM(point_1) AS total 
            FROM point_kasir WHERE kd_cust = ?
            UNION ALL
            SELECT  SUM(jum_point) AS total 
            FROM point_manual WHERE kd_cust = ?
            UNION ALL
            SELECT SUM(jum_point) AS total 
            FROM point_trans WHERE kd_cust = ?
        ) total_poin
    ";
    $stmt = $conn->prepare($sql_poin);
    $stmt->bind_param("sss", $kd_cust, $kd_cust, $kd_cust);
    $stmt->execute();
    $resultPoin = $stmt->get_result();
    $rowPoin = $resultPoin->fetch_assoc();
    $totalPoin = $rowPoin['total_poin'] ?? 0;

    // 3. Transaksi Terakhir (ambil 1 saja biar tidak berat)
    $sql_trans = "
        SELECT DISTINCT tanggal, ks.nm_alias as cabang, ks.nm_store as toko, no_faktur, belanja
        FROM pembayaran_b
        left join kode_store ks on ks.kd_store = pembayaran_b.kd_store
        WHERE kd_cust = ?
        ORDER BY tanggal DESC
    ";
    $stmt = $conn->prepare($sql_trans);
    $stmt->bind_param("s", $kd_cust);
    $stmt->execute();
    $resultLastTrans = $stmt->get_result();
    $lastTrans = $resultLastTrans->fetch_all(MYSQLI_ASSOC);


    // 4. Status Aktif Member
    $sql_status = "
    SELECT 
    CASE
        WHEN MAX(tanggal) >= CURDATE() - INTERVAL 3 MONTH THEN 'Aktif'
        WHEN MAX(tanggal) <  CURDATE() - INTERVAL 3 MONTH THEN 'Non-Aktif'
        ELSE 'Member Lama Non-Aktif'
    END AS status_aktif
FROM pembayaran_b
WHERE kd_cust = ?
";
    $stmt = $conn->prepare($sql_status);
    $stmt->bind_param("s", $kd_cust);
    $stmt->execute();
    $resultStatus = $stmt->get_result();
    $rowStatus = $resultStatus->fetch_assoc();
    $statusAktif = $rowStatus['status_aktif'] ?? 'Tidak Diketahui';

    // 5 Riwayat Poin Masuk / Keluar

    $sql_poin = "SELECT tanggal, 'Belanja' AS sumber, point_1 AS poin
                FROM point_kasir
                WHERE kd_cust = ?

                UNION ALL

                SELECT tgl_trans, 'Input Manual' AS sumber, jum_point AS poin
                FROM point_manual
                WHERE kd_cust = ?

                UNION ALL

                SELECT tgl_trans, 'Tukar Poin' AS sumber, -jum_point AS poin
                FROM point_trans
                WHERE kd_cust = ?

                ORDER BY tanggal DESC";
    $stmt = $conn->prepare($sql_poin);
    $stmt->bind_param("sss", $kd_cust, $kd_cust, $kd_cust);
    $stmt->execute();
    $resultPoin = $stmt->get_result();
    $poin = $resultPoin->fetch_all(MYSQLI_ASSOC);


    // Response JSON
    echo json_encode([
        "success" => true,
        "member" => $member,
        "poin" => ["total_poin" => $totalPoin, "history_poin" => $poin],
        "last_transaksi" => $lastTrans,
        "status_aktif" => $statusAktif
    ]);
    $stmt->close();

} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
} finally{
    $conn->close();
    exit;
}
