<?php
session_start();
include '../../../aa_kon_sett.php';
require_once __DIR__ . "./../../auth/middleware_login.php";
header('Content-Type: application/json');

$header = getAllHeaders();
$authHeader = $header['Authorization'] ?? $header['authorization'] ?? '';
$token = null;
if (preg_match('/^Bearer\s(\S+)$/', $authHeader, $macthes))
    $token = $macthes[1];

if (!$token) {
    http_response_code(401);
    exit(json_encode(['error' => 'Token tidak ditemukan']));
}

$verif = verify_token($token);
if (!$verif) {
    http_response_code(401);
    exit(json_encode(['error' => 'Sesi login tidak valid']));
}

$response = ['stores' => [], 'tabel_data' => [], 'pagination' => ['total_rows' => 0], 'error' => null];

try {
    $tgl_mulai = $_GET['tgl_mulai'] ?? date('Y-m-d');
    $tgl_selesai = $_GET['tgl_selesai'] ?? date('Y-m-d');
    $kd_store = $_GET['kd_store'] ?? 'all';
    $page = max(1, (int) ($_GET['page'] ?? 1));
    $limit = 100;
    $offset = ($page - 1) * $limit;

    // Ambil daftar toko untuk dropdown filter
    $sqlUser = "SELECT kd_store FROM user_account WHERE kode = ?";
    $stmtU = $conn->prepare($sqlUser);
    $stmtU->bind_param("s", $verif->kode);
    $stmtU->execute();
    $resU = $stmtU->get_result()->fetch_assoc();
    $stmtU->close();

    if ($resU['kd_store'] == "Pusat") {
        $sql_s = "SELECT Kd_Store as kd_store, Nm_Alias as nm_alias FROM kode_store WHERE display = 'on' ORDER BY Nm_Alias ASC";
    } else {
        $kdStoreArray = explode(',', $resU['kd_store']);
        $kdStoreImplode = "'" . implode("','", $kdStoreArray) . "'";
        $sql_s = "SELECT Kd_Store as kd_store, Nm_Alias as nm_alias FROM kode_store WHERE Kd_Store IN ($kdStoreImplode) AND display = 'on' ORDER BY Nm_Alias ASC";
    }
    $res_s = $conn->query($sql_s);
    while ($row = $res_s->fetch_assoc())
        $response['stores'][] = $row;

    // Query Utama dengan JOIN ke kode_store
    $where = "ub.tanggal BETWEEN ? AND ?";
    $params = [$tgl_mulai, $tgl_selesai];
    $types = "ss";

    if ($kd_store != 'all' && $kd_store != 'SEMUA CABANG') {
        $where .= " AND ub.kd_store = ?";
        $types .= "s";
        $params[] = $kd_store;
    }

    $sql_data = "SELECT SQL_CALC_FOUND_ROWS 
            ub.*, 
            u1.nama AS nama_user_hitung, 
            u2.nama AS nama_user_cek, 
            u2.inisial AS nama_user_cek_inisial,
            ks.Nm_Alias AS nm_alias_store  /* MENGAMBIL ALIAS TOKO */
        FROM uang_brangkas ub
        LEFT JOIN user_account u1 ON ub.user_hitung = u1.kode
        LEFT JOIN user_account u2 ON ub.user_cek = u2.kode
        LEFT JOIN kode_store ks ON ub.kd_store = ks.Kd_Store /* JOIN KE TABEL STORE */
        WHERE $where
        ORDER BY ub.tanggal DESC, ub.jam DESC
        LIMIT ? OFFSET ?";

    $stmt = $conn->prepare($sql_data);
    $types .= "ii";
    $params[] = $limit;
    $params[] = $offset;
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        // Jika nm_alias_store kosong, tampilkan kd_store sebagai fallback
        $row['display_store'] = $row['nm_alias_store'] ?? $row['kd_store'];
        $response['tabel_data'][] = $row;
    }
    $stmt->close();

    $total_rows = $conn->query("SELECT FOUND_ROWS() AS total_rows")->fetch_assoc()['total_rows'] ?? 0;
    $response['pagination']['total_rows'] = (int) $total_rows;
    $response['pagination']['total_pages'] = ceil($total_rows / $limit);

} catch (Exception $e) {
    http_response_code(500);
    $response['error'] = $e->getMessage();
}
echo json_encode($response);