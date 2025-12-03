src/api/uang_brangkas/update.php:
<?php
session_start();
ini_set('display_errors', 0);
require_once __DIR__ . '/../../../aa_kon_sett.php';

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method Not Allowed');
    }

    $input = json_decode(file_get_contents('php://input'), true);

    // 1. Identifikasi Row yang akan diupdate (Primary Key Composite)
    $pk_tanggal = $input['pk_tanggal'] ?? null;
    $pk_jam = $input['pk_jam'] ?? null;
    $pk_user_hitung = $input['pk_user_hitung'] ?? null;

    if (!$pk_tanggal || !$pk_jam || !$pk_user_hitung) {
        throw new Exception("Data tidak valid. Primary Key tidak ditemukan.");
    }

    // 2. Data Baru & Otorisasi
    // User Cek baru (jika ganti supervisor) atau tetap yang lama
    $user_cek = $input['user_cek'] ?? null;
    $kode_otorisasi = $input['kode_otorisasi'] ?? '';
    $keterangan = $input['keterangan'] ?? '';

    if (empty($user_cek) || empty($kode_otorisasi)) {
        throw new Exception("Update memerlukan User Cek dan Kode Otorisasi yang valid.");
    }

    // 3. Validasi Otorisasi (Harus valid untuk tanggal dari record yang diedit)
    // NOTE: Validasi menggunakan Tanggal Record (pk_tanggal) agar password sesuai dengan tanggal transaksi
    $sql_auth = "SELECT kode_user FROM otorisasi_user WHERE kode_user = ? AND PASSWORD = ? AND tanggal = ?";
    $stmt_auth = $conn->prepare($sql_auth);
    $stmt_auth->bind_param("iss", $user_cek, $kode_otorisasi, $pk_tanggal);
    $stmt_auth->execute();

    if ($stmt_auth->get_result()->num_rows === 0) {
        throw new Exception("Otorisasi Gagal! Password salah atau tidak cocok untuk tanggal transaksi ($pk_tanggal).");
    }
    $stmt_auth->close();

    // 4. Kalkulasi Ulang Total
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
        $qty_values[] = $qty;
        $total_nominal += ($qty * $val);
    }

    // 5. Proses Update
    $conn->begin_transaction();

    $sql_update = "
        UPDATE uang_brangkas SET
            user_cek = ?,
            kode_otorisasi_input = ?,
            qty_100rb = ?, qty_50rb = ?, qty_20rb = ?, qty_10rb = ?, 
            qty_5rb = ?, qty_2rb = ?, qty_1rb = ?,
            qty_1000_koin = ?, qty_500_koin = ?, qty_200_koin = ?, qty_100_koin = ?,
            total_nominal = ?,
            keterangan = ?
        WHERE 
            tanggal = ? AND jam = ? AND user_hitung = ?
    ";

    $stmt = $conn->prepare($sql_update);

    // Tipe Data Bind:
    // user_cek(i), kode(s) = is
    // qty(11 ints) = iiiiiiiiiii
    // total(d), ket(s) = ds
    // PK: ssi
    $types = "is" . str_repeat("i", 11) . "dsssi";

    $params = array_merge(
        [$user_cek, $kode_otorisasi],
        $qty_values,
        [$total_nominal, $keterangan, $pk_tanggal, $pk_jam, $pk_user_hitung]
    );

    $stmt->bind_param($types, ...$params);

    if (!$stmt->execute()) {
        throw new Exception("Gagal Update: " . $stmt->error);
    }

    if ($stmt->affected_rows === 0) {
        // Bisa jadi karena data sama persis, atau ID tidak ketemu
        // Kita anggap sukses saja jika tidak error, tapi beri pesan info
    }

    $stmt->close();
    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Data uang brangkas berhasil diperbarui.',
        'total_nominal' => $total_nominal
    ]);

} catch (Exception $e) {
    if (isset($conn))
        $conn->rollback();
    http_response_code(200);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} finally {
    if (isset($conn))
        $conn->close();
}
?>