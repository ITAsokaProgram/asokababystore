<?php

require_once __DIR__ . "/../../../../aa_kon_sett.php";
require_once __DIR__ . "/../../../auth/middleware_login.php";

header("Content-Type:application/json");
header("Access-Control-Allow-Methods: GET");
$headers = getallheaders();
if (!isset($headers['Authorization'])) {
    http_response_code(401);
    echo json_encode(['status' => "Unauthenticated", 'message' => 'Request ditolak, token tidak ditemukan']);
    exit;
}
$authHeader = $headers['Authorization'];
$token = null;
if (preg_match('/^Bearer\s(\S+)$/', $authHeader, $matches)) {
    $token = $matches[1];
}

$verif = verify_token($token);

// Get data from member
$kd_cust = $_GET['member'] ?? null;
$kd_store = $_GET['cabang'] ?? null;
$no_bon = $_GET['kode'] ?? null;

// --- TAMBAHAN: Ambil parameter filter ---
$filter_type = $_GET['filter_type'] ?? null;
$filter_preset = $_GET['filter'] ?? null;
$start_date = $_GET['start_date'] ?? null;
$end_date = $_GET['end_date'] ?? null;

$params = [];
$types = "";
$date_sql = "";

// --- TAMBAHAN: Logika untuk menentukan rentang tanggal ---
if ($filter_type === 'custom' && $start_date && $end_date) {
    if ($start_date === $end_date) {
        $date_sql = " AND DATE(t.tgl_trans) = ? ";
        $params[] = $start_date;
        $types .= "s";
    } else {
        $date_sql = " AND t.tgl_trans BETWEEN ? AND ? ";
        $params[] = $start_date;
        $params[] = $end_date;
        $types .= "ss";
    }
} elseif ($filter_type === 'preset' && $filter_preset) {
    $end = date('Y-m-d');
    $start = '';
    switch ($filter_preset) {
        case 'kemarin':
            $start = date('Y-m-d', strtotime('-1 day'));
            $end = $start;
            $date_sql = " AND DATE(t.tgl_trans) = ? ";
            $params[] = $start;
            $types .= "s";
            break;
        case '1minggu':
            $start = date('Y-m-d', strtotime('-7 days'));
            $date_sql = " AND t.tgl_trans BETWEEN ? AND ? ";
            $params[] = $start;
            $params[] = $end;
            $types .= "ss";
            break;
        case '1bulan':
            $start = date('Y-m-d', strtotime('-1 month'));
            $date_sql = " AND t.tgl_trans BETWEEN ? AND ? ";
            $params[] = $start;
            $params[] = $end;
            $types .= "ss";
            break;
        case '3bulan':
            $start = date('Y-m-d', strtotime('-3 months'));
            $date_sql = " AND t.tgl_trans BETWEEN ? AND ? ";
            $params[] = $start;
            $params[] = $end;
            $types .= "ss";
            break;
        case '6bulan':
            $start = date('Y-m-d', strtotime('-6 months'));
            $date_sql = " AND t.tgl_trans BETWEEN ? AND ? ";
            $params[] = $start;
            $params[] = $end;
            $types .= "ss";
            break;
        case '9bulan':
            $start = date('Y-m-d', strtotime('-9 months'));
            $date_sql = " AND t.tgl_trans BETWEEN ? AND ? ";
            $params[] = $start;
            $params[] = $end;
            $types .= "ss";
            break;
        case '12bulan':
            $start = date('Y-m-d', strtotime('-12 months'));
            $date_sql = " AND t.tgl_trans BETWEEN ? AND ? ";
            $params[] = $start;
            $params[] = $end;
            $types .= "ss";
            break;
        case 'semua':
            $date_sql = "";
            break;
        default:
            // Fallback jika filter tidak dikenali
            $start = date('Y-m-d', strtotime('-1 day'));
            $date_sql = " AND DATE(t.tgl_trans) = ? ";
            $params[] = $start;
            $types .= "s";
            break;
    }
} else {
    // Fallback jika tidak ada filter (termasuk jika start_date/end_date dikirim manual)
    if ($start_date && $end_date) {
        if ($start_date === $end_date) {
            $date_sql = " AND DATE(t.tgl_trans) = ? ";
            $params[] = $start_date;
            $types .= "s";
        } else {
            $date_sql = " AND t.tgl_trans BETWEEN ? AND ? ";
            $params[] = $start_date;
            $params[] = $end_date;
            $types .= "ss";
        }
    } else {
        // Default absolut ke 'kemarin' jika tidak ada info tanggal sama sekali
        $yesterday = date('Y-m-d', strtotime('-1 day'));
        $date_sql = " AND DATE(t.tgl_trans) = ? ";
        $params[] = $yesterday;
        $types .= "s";
    }
}
// --- AKHIR DARI LOGIKA TANGGAL ---


if ($kd_cust) {
    // --- Salin parameter tanggal untuk query ini ---
    $memberParams = $params;
    $memberTypes = $types;

    $sql = "SELECT 
    'Transaksi' AS sumber,
    t.kd_cust,
    DATE_FORMAT(t.tgl_trans, '%d-%m-%Y') AS tanggal,
    t.jam_trs AS jam,
    t.no_bon AS no_trans,
    t.kode_kasir AS USER,
    t.nama_kasir AS kasir,
    ks.Nm_Alias AS cabang,
    (
        SELECT SUM(qty * harga) 
        FROM trans_b t2 
        WHERE t2.no_bon = t.no_bon
    ) AS nominal,
    IFNULL(pk.point_1, 0) + IFNULL(pm.jum_point, 0) - IFNULL(pt.jum_point,0) AS jumlah_point,
    CASE 
        WHEN pk.kd_cust IS NOT NULL THEN 'Detail'
        WHEN pm.kd_cust IS NOT NULL THEN 'Manual'
        ELSE 'Tanpa Poin'
    END AS keterangan_struk
FROM trans_b t
LEFT JOIN (
select kd_cust,sum(point_1) as point_1 from 
point_kasir
group by kd_cust
) pk ON pk.kd_cust = t.kd_cust
LEFT JOIN ( select kd_cust, sum(jum_point) as jum_point from
point_manual
group by kd_cust
) pm ON pm.kd_cust = t.kd_cust
LEFT JOIN ( select kd_cust, sum(jum_point) as jum_point from
point_trans
group by kd_cust
) pt ON pt.kd_cust = t.kd_cust
LEFT JOIN kode_store ks ON t.kd_store = ks.Kd_Store
WHERE t.kd_cust = ?
    AND t.kd_store = ?
    $date_sql -- <-- MODIFIKASI: Gunakan filter tanggal dinamis
GROUP BY t.no_bon
ORDER BY t.tgl_trans DESC";

    // Statement for Member
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Server Gagal Memproses (prepare)']);
        exit;
    }

    // --- MODIFIKASI: Bind parameter dinamis ---
    array_unshift($memberParams, $kd_cust, $kd_store);
    $memberTypes = "ss" . $memberTypes;

    $stmt->bind_param($memberTypes, ...$memberParams);
    // --- AKHIR MODIFIKASI BIND ---

    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        http_response_code(200);
        $data = $result->fetch_all(MYSQLI_ASSOC);
        echo json_encode([
            'status' => 'success',
            'message' => 'Data transaksi Member',
            'detail_transaction' => $data
        ]);
    } else {
        http_response_code(200); // Kirim 200 agar JS bisa handle
        echo json_encode(['status' => 'success', 'message' => 'Data tidak ditemukan', 'detail_transaction' => []]);
    }
    $stmt->close();
} else {
    // --- Salin parameter tanggal untuk query ini ---
    $nonMemberParams = $params;
    $nonMemberTypes = $types;

    // Statement for Non Member
    $sqlNonMember = "SELECT 
    t.no_bon AS kode_transaksi, 
    DATE_FORMAT(t.tgl_trans, '%d-%m-%Y') AS tanggal,
    t.jam_trs,
    t.descp AS nama_item,
    t.qty AS jumlah_item,
    t.harga AS harga_satuan,
    t.harga AS harga_promo,
    t.nama_kasir AS kasir,
    SUM(t.harga*t.qty) AS nominal,
    ks.Nm_Alias AS cabang
FROM trans_b t 
LEFT JOIN kode_store ks ON t.kd_store = ks.kd_store
WHERE t.no_bon = ? 
    $date_sql -- <-- MODIFIKASI: Gunakan filter tanggal dinamis
GROUP BY plu
ORDER BY t.jam_trs, t.no_bon, t.descp";

    $stmt = $conn->prepare($sqlNonMember);
    if (!$stmt) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Server Gagal Memproses (prepare non-member)']);
        exit;
    }

    // --- MODIFIKASI: Bind parameter dinamis ---
    array_unshift($nonMemberParams, $no_bon);
    $nonMemberTypes = "s" . $nonMemberTypes;

    $stmt->bind_param($nonMemberTypes, ...$nonMemberParams);
    // --- AKHIR MODIFIKASI BIND ---

    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        http_response_code(200);
        $dataNonMember = $result->fetch_all(MYSQLI_ASSOC);
        echo json_encode([
            'status' => 'success',
            'message' => 'Data transaksi Non Member',
            'detail_transaction' => $dataNonMember
        ]);
    } else {
        http_response_code(200); // Kirim 200 agar JS bisa handle
        echo json_encode(['status' => 'success', 'message' => 'Data tidak ditemukan', 'detail_transaction' => []]);
    }
    $stmt->close();
}
$conn->close();
?>