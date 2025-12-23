<?php
session_start();
include '../../../aa_kon_sett.php';
require_once __DIR__ . "./../../auth/middleware_login.php"; // Tambahkan middleware untuk verifikasi token

header('Content-Type: application/json');

$response = [
    'summary' => [
        'total_qty' => 0,
        'total_netto' => 0,
        'total_ppn' => 0,
        'total_grand' => 0,
    ],
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
    // --- BAGIAN GET KODE CABANG (LOGIKA DARI get_kode.php) ---
    $header = getAllHeaders();
    $authHeader = $header['Authorization'] ?? '';
    $token = null;
    if (preg_match('/^Bearer\s(\S+)$/', $authHeader, $matches)) {
        $token = $matches[1];
    }

    if (!$token) {
        throw new Exception("Unauthenticated: Token tidak ditemukan");
    }

    $verif = verify_token($token);

    // Ambil hak akses cabang user
    $sqlUserCabang = "SELECT kd_store FROM user_account WHERE kode = ?";
    $stmtUserCabang = $conn->prepare($sqlUserCabang);
    $stmtUserCabang->bind_param("s", $verif->kode);
    $stmtUserCabang->execute();
    $resultUserCabang = $stmtUserCabang->get_result();
    $userCabangData = $resultUserCabang->fetch_assoc();

    if (!$userCabangData) {
        throw new Exception("User tidak ditemukan");
    }

    $kdStoreUser = $userCabangData['kd_store'];

    if ($kdStoreUser == "Pusat") {
        $sql_stores = "SELECT Kd_Store as store, Nm_Alias as nama_cabang FROM kode_store WHERE display = 'on' ORDER BY Nm_Alias ASC";
        $stmt_s = $conn->prepare($sql_stores);
    } else {
        $kdStoreArray = explode(',', $kdStoreUser);
        $placeholders = implode(',', array_fill(0, count($kdStoreArray), '?'));
        $sql_stores = "SELECT Kd_Store as store, Nm_Alias as nama_cabang FROM kode_store WHERE display = 'on' AND Kd_Store IN ($placeholders) ORDER BY Nm_Alias ASC";
        $stmt_s = $conn->prepare($sql_stores);
        $stmt_s->bind_param(str_repeat('s', count($kdStoreArray)), ...$kdStoreArray);
    }

    $stmt_s->execute();
    $result_stores = $stmt_s->get_result();
    while ($row = $result_stores->fetch_assoc()) {
        // Kita simpan dengan key 'kd_store' dan 'nm_alias' agar konsisten dengan frontend sebelumnya
        $response['stores'][] = [
            'kd_store' => $row['store'],
            'nm_alias' => $row['nama_cabang']
        ];
    }
    $stmt_s->close();
    // --- AKHIR BAGIAN GET KODE CABANG ---

    // Parameter Filter
    $default_mulai = date('Y-m-16', strtotime('last month'));
    $default_selesai = date('Y-m-15');
    $tgl_mulai = $_GET['tgl_mulai'] ?? $default_mulai;
    $tgl_selesai = $_GET['tgl_selesai'] ?? $default_selesai;
    $kd_store = $_GET['kd_store'] ?? 'all';
    $page = (int) ($_GET['page'] ?? 1);
    $limit = 100;

    if ($page < 1)
        $page = 1;
    $offset = ($page - 1) * $limit;

    $response['pagination']['current_page'] = $page;
    $response['pagination']['limit'] = $limit;
    $response['pagination']['offset'] = $offset;

    $where = "DATE(tgl_koreksi) BETWEEN ? AND ?";
    $params = ['ss', $tgl_mulai, $tgl_selesai];

    // Jika pilih "SEMUA CABANG" atau "all", dan bukan admin pusat, filter berdasarkan list cabangnya
    if ($kd_store != 'all' && $kd_store != 'SEMUA CABANG') {
        $where .= " AND kd_store = ?";
        $params[0] .= 's';
        $params[] = $kd_store;
    } else if ($kdStoreUser != "Pusat") {
        // Jika user bukan pusat dan pilih semua, hanya tampilkan cabang yang dia punya akses
        $kdStoreArray = explode(',', $kdStoreUser);
        $placeholders = implode(',', array_fill(0, count($kdStoreArray), '?'));
        $where .= " AND kd_store IN ($placeholders)";
        $params[0] .= str_repeat('s', count($kdStoreArray));
        foreach ($kdStoreArray as $k)
            $params[] = $k;
    }

    // 1. Summary
    $sql_summary = "SELECT SUM(sel_qty) as total_qty, SUM(avg_cost * sel_qty) as total_netto, SUM(ppn_kor * sel_qty) as total_ppn, SUM((avg_cost + ppn_kor) * sel_qty) as total_grand FROM koreksi_so WHERE $where";
    $stmt_sum = $conn->prepare($sql_summary);
    $stmt_sum->bind_param(...$params);
    $stmt_sum->execute();
    $res_sum = $stmt_sum->get_result()->fetch_assoc();
    if ($res_sum) {
        $response['summary']['total_qty'] = (float) ($res_sum['total_qty'] ?? 0);
        $response['summary']['total_netto'] = (float) ($res_sum['total_netto'] ?? 0);
        $response['summary']['total_ppn'] = (float) ($res_sum['total_ppn'] ?? 0);
        $response['summary']['total_grand'] = (float) ($res_sum['total_grand'] ?? 0);
    }
    $stmt_sum->close();

    // 2. Count for Pagination
    $sql_count = "SELECT COUNT(*) as total_group FROM (SELECT tgl_koreksi, kode_supp FROM koreksi_so WHERE $where GROUP BY tgl_koreksi, kode_supp) as grouped_table";
    $stmt_count = $conn->prepare($sql_count);
    $stmt_count->bind_param(...$params);
    $stmt_count->execute();
    $total_rows = $stmt_count->get_result()->fetch_assoc()['total_group'] ?? 0;
    $response['pagination']['total_rows'] = (int) $total_rows;
    $response['pagination']['total_pages'] = ceil($total_rows / $limit);
    $stmt_count->close();

    // 3. Data Table
    $sql_data = "SELECT DATE(tgl_koreksi) as tgl_koreksi, kode_supp, SUM(sel_qty) as grp_qty, SUM(avg_cost * sel_qty) as grp_netto, SUM(ppn_kor * sel_qty) as grp_ppn, SUM((avg_cost + ppn_kor) * sel_qty) as grp_total FROM koreksi_so WHERE $where GROUP BY tgl_koreksi, kode_supp ORDER BY tgl_koreksi DESC, kode_supp ASC LIMIT ? OFFSET ?";
    $params[0] .= 'ii';
    $params[] = $limit;
    $params[] = $offset;
    $stmt = $conn->prepare($sql_data);
    $stmt->bind_param(...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $response['tabel_data'][] = $row;
    }
    $stmt->close();

} catch (Exception $e) {
    http_response_code(500);
    $response['error'] = $e->getMessage();
}
echo json_encode($response);
?>