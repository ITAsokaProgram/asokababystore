src/api/uang_brangkas/insert.php:
<?php
session_start();
ini_set('display_errors', 0);
require_once __DIR__ . '/../../../aa_kon_sett.php';
// require_once __DIR__ . '/../../auth/middleware_login.php'; // Aktifkan jika butuh cek login user sistem

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method Not Allowed');
    }

    $input = json_decode(file_get_contents('php://input'), true);

    // 1. Validasi Input Wajib
    $tanggal = $input['tanggal'] ?? date('Y-m-d'); // Default hari ini
    $jam = $input['jam'] ?? date('H:i:s');         // Default jam sekarang
    $user_hitung = $input['user_hitung'] ?? null;
    $user_cek = $input['user_cek'] ?? null; // Supervisor/Authorizer
    $kode_otorisasi = $input['kode_otorisasi'] ?? '';
    $keterangan = $input['keterangan'] ?? '';

    if (empty($user_hitung) || empty($user_cek)) {
        throw new Exception("User Hitung dan User Cek (Supervisor) wajib diisi.");
    }
    if (empty($kode_otorisasi)) {
        throw new Exception("Kode Otorisasi wajib diisi untuk melakukan input.");
    }

    // 2. Validasi Otorisasi (Core Logic)
    // Cek apakah user_cek memiliki kode_otorisasi tersebut pada tanggal input
    $sql_auth = "SELECT kode_user FROM otorisasi_user WHERE kode_user = ? AND PASSWORD = ? AND tanggal = ?";
    $stmt_auth = $conn->prepare($sql_auth);
    $stmt_auth->bind_param("iss", $user_cek, $kode_otorisasi, $tanggal);
    $stmt_auth->execute();
    $res_auth = $stmt_auth->get_result();

    if ($res_auth->num_rows === 0) {
        throw new Exception("Otorisasi Gagal! Kode salah atau tidak berlaku untuk User Cek pada tanggal tersebut.");
    }
    $stmt_auth->close();

    // 3. Kalkulasi Total Nominal (Server Side Calculation agar aman)
    $denominations = [
        'qty_100rb' => 100000,
        'qty_50rb' => 50000,
        'qty_20rb' => 20000,
        'qty_10rb' => 10000,
        'qty_5rb' => 5000,
        'qty_2rb' => 2000,
        'qty_1rb' => 1000,
        'qty_1000_koin' => 1000,
        'qty_500_koin' => 500,
        'qty_200_koin' => 200,
        'qty_100_koin' => 100
    ];

    $total_nominal = 0;
    $qty_values = [];

    foreach ($denominations as $key => $val) {
        $qty = (int) ($input[$key] ?? 0);
        $qty_values[] = $qty; // Simpan untuk bind_param nanti
        $total_nominal += ($qty * $val);
    }

    // 4. Proses Insert
    $conn->begin_transaction();

    $sql_insert = "
        INSERT INTO uang_brangkas (
            tanggal, jam, user_hitung, user_cek, kode_otorisasi_input,
            qty_100rb, qty_50rb, qty_20rb, qty_10rb, qty_5rb, qty_2rb, qty_1rb,
            qty_1000_koin, qty_500_koin, qty_200_koin, qty_100_koin,
            total_nominal, keterangan
        ) VALUES (
            ?, ?, ?, ?, ?,
            ?, ?, ?, ?, ?, ?, ?,
            ?, ?, ?, ?,
            ?, ?
        )
    ";

    $stmt = $conn->prepare($sql_insert);
    if (!$stmt)
        throw new Exception("Database Error: " . $conn->error);

    // Tipe data bind: ssiis (5) + iiiiiii (7) + iiii (4) + ds (2) = Total 18 parameter
    // total_nominal pakai 'd' (double), keterangan 's'
    $types = "ssiis" . str_repeat("i", 11) . "ds";

    $params = array_merge(
        [$tanggal, $jam, $user_hitung, $user_cek, $kode_otorisasi],
        $qty_values,
        [$total_nominal, $keterangan]
    );

    $stmt->bind_param($types, ...$params);

    if (!$stmt->execute()) {
        if ($conn->errno == 1062) {
            throw new Exception("Data duplikat! User ini sudah melakukan hitung pada tanggal dan jam yang sama.");
        }
        throw new Exception("Gagal Insert: " . $stmt->error);
    }

    $stmt->close();
    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Data uang brangkas berhasil disimpan.',
        'total_nominal' => $total_nominal
    ]);

} catch (Exception $e) {
    if (isset($conn))
        $conn->rollback();
    http_response_code(200); // Return 200 tapi success false agar ditangkap frontend dengan rapi
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} finally {
    if (isset($conn))
        $conn->close();
}
?>