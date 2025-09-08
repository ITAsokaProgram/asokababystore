<?php
include '../../../aa_kon_sett.php';
session_start();
session_regenerate_id(true); // Mencegah session fixation
$envFile = '/var/www/endes.env';
header('Content-Type: application/json');

$input = json_decode(file_get_contents("php://input"), true);

// Pastikan file ada dan bisa dibaca
if (!file_exists($envFile) || !is_readable($envFile)) {
    die("File konfigurasi tidak ditemukan atau tidak bisa dibaca.");
}

// Baca file ENV
$env = parse_ini_file($envFile);

// Simpan ke $_ENV agar lebih aman
$_ENV['ENCRYPTION_KEY'] = $env['ENCRYPTION_KEY'];
$_ENV['ENCRYPTION_IV'] = $env['ENCRYPTION_IV'];

define('ENCRYPTION_KEY', $_ENV['ENCRYPTION_KEY']);
define('ENCRYPTION_IV', $_ENV['ENCRYPTION_IV']);
define('ENCRYPTION_METHOD', 'AES-256-CBC');

// Fungsi enkripsi & dekripsi

function decryptData($data)
{
    return openssl_decrypt($data, ENCRYPTION_METHOD, ENCRYPTION_KEY, 0, ENCRYPTION_IV);
}
function maskPhoneNumber($phone)
{
    return substr($phone, 0, 5) . '*****' . substr($phone, -3);
}

function historyTrans($conn, $kd_cust)
{
    // Query transaksi terakhir
    $trans_stmt = $conn->prepare("SELECT 
    t.no_bon AS kode_transaksi,
    DATE_FORMAT(t.tgl_trans, '%d-%m-%Y') AS tanggal,
    ks.Nm_Store AS nama_toko,
    SUM(t.hrg_promo * t.qty) AS total_belanja
    FROM trans_b t 
    LEFT JOIN kode_store ks ON t.kd_store = ks.kd_store
    WHERE t.kd_cust = ?
    GROUP BY t.no_bon,t.tgl_trans,ks.kd_store ORDER BY t.tgl_trans DESC");
    $trans_stmt->bind_param('s', $kd_cust);
    $trans_stmt->execute();
    $trans_result = $trans_stmt->get_result();
    $latest_trans = $trans_result->fetch_all(MYSQLI_ASSOC);
    $trans_stmt->close();
    return $latest_trans;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kd_cust = $input['kode-member'];
    // Ambil data customer dan total poin
    $stmt = $conn->prepare("
        SELECT 
            a.upd_from_web, 
            a.kd_cust, 
            a.nama_cust,
            a.alamat, a.Prov, a.Kota, a.Kec, a.Kel,
            a.alamat_ds, a.Prov_ds, a.Kota_ds, a.Kec_ds, a.Kel_ds,
            (
                COALESCE(b.total_point_1,0) + 
                COALESCE(c.total_point,0) + 
                COALESCE(d.total_jum_point,0)
            ) - COALESCE(e.total_jum_point_minus,0) AS total_point 
        FROM customers a
        LEFT JOIN (
            SELECT kd_cust, SUM(point_1) AS total_point_1 FROM point_kasir WHERE kd_cust=? GROUP BY kd_cust
        ) b ON a.kd_cust = b.kd_cust
        LEFT JOIN (
            SELECT kd_cust, SUM(`point`) AS total_point FROM t_pembayaran WHERE kd_cust=? GROUP BY kd_cust
        ) c ON a.kd_cust = c.kd_cust
        LEFT JOIN (
            SELECT kd_cust, SUM(jum_point) AS total_jum_point FROM point_manual WHERE kd_cust=? GROUP BY kd_cust
        ) d ON a.kd_cust = d.kd_cust
        LEFT JOIN (
            SELECT kd_cust, SUM(jum_point) AS total_jum_point_minus FROM point_trans WHERE kd_cust=? GROUP BY kd_cust
        ) e ON a.kd_cust = e.kd_cust
        WHERE a.kd_cust = ?
        GROUP BY a.upd_from_web, a.kd_cust, a.nama_cust, a.alamat,a.Prov,a.Kota,a.Kec,a.Kel,a.alamat_ds,a.Prov_ds,a.Kota_ds,a.Kec_ds,a.Kel_ds;
    ");

    if ($stmt) {
        $stmt->bind_param('sssss', $kd_cust, $kd_cust, $kd_cust, $kd_cust, $kd_cust);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if ($row['upd_from_web'] === 0) {
                $history = historyTrans($conn, $row['kd_cust']);
                echo json_encode([
                    'message' => 'Data berhasil didapatkan',
                    'data' => [
                        'customer' => [
                            'kode_member' => $row['kd_cust'],
                            'nama_customer' => $row['nama_cust'],
                            'total_point' => (int) $row['total_point'],
                            'update_profile' => (bool) $row['upd_from_web'],
                        ],
                        'transaksi_terakhir' => $history
                    ]
                ]);
            } else {
                echo json_encode([
                    'message' => 'Data berhasil didapatkan',
                    'data' => [
                        'customer' => [
                            'kode_member' => $row['kd_cust'],
                            'nama_customer' => $row['nama_cust'],
                            'total_point' => (int) $row['total_point'],
                            'alamat' => $row['alamat'],
                            'provinsi' => $row['Prov'],
                            'kota' => $row['Kota'],
                            'kecamatan' => $row['Kec'],
                            'kelurahan' => $row['Kel'],
                            'alamat_domisili' => $row['alamat_ds'],
                            'provinsi_domisili' => $row['Prov_ds'],
                            'kota_domisili' => $row['Kota_ds'],
                            'kecamatan_domisili' => $row['Kec_ds'],
                            'kelurahan_domisili' => $row['Kel_ds'],
                            'update_profile' => (bool) $row['upd_from_web'],
                            'member' => "Terdaftar"
                        ]
                    ],
                ]);
            }
        } else {
            http_response_code(404);
            echo json_encode(['message' => 'Data Customer Tidak ditemukan']);
        }

        $stmt->close();
    } else {
        http_response_code(500);
        echo json_encode(['message' => 'Gagal menyiapkan query']);
    }

    $conn->close();
    exit;
}