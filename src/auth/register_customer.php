<?php
require_once __DIR__ . "/../../aa_kon_sett.php"; // Sesuaikan path koneksi DB
header("Content-Type: application/json");

try {
    $inputJSON = file_get_contents("php://input");
    $data = json_decode($inputJSON, true);

    // Ambil data
    $token = $data['token'] ?? null;
    $email = $data['email'] ?? null;
    $password = $data['password'] ?? null;
    $nama_lengkap = $data['nama_lengkap'] ?? null;

    // 1. Validasi server-side sederhana
    if (empty($token) || empty($email) || empty($password) || empty($nama_lengkap)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Semua data wajib diisi.']);
        exit;
    }

    // 2. Re-verifikasi token (KEAMANAN PENTING)
    $stmtToken = $conn->prepare("SELECT no_hp FROM reset_token WHERE token = ? AND used = 0 AND kadaluarsa > NOW() AND token LIKE 'final_reg_%'");
    $stmtToken->bind_param("s", $token);
    $stmtToken->execute();
    $resultToken = $stmtToken->get_result();

    if ($resultToken->num_rows === 0) {
        $stmtToken->close();
        http_response_code(401);
        echo json_encode(['status' => 'error', 'message' => 'Token tidak valid atau sesi telah kedaluwarsa. Silakan muat ulang halaman.']);
        exit;
    }

    $no_hp_trusted = $resultToken->fetch_assoc()['no_hp']; // Ambil no_hp dari token, BUKAN dari input user
    $stmtToken->close();

    // 3. Cek apakah email atau no_hp sudah ada di user_asoka
    $stmtCheck = $conn->prepare("SELECT 1 FROM user_asoka WHERE email = ? OR no_hp = ?");
    $stmtCheck->bind_param("ss", $email, $no_hp_trusted);
    $stmtCheck->execute();
    $resultCheck = $stmtCheck->get_result();

    if ($resultCheck->num_rows > 0) {
        $stmtCheck->close();
        http_response_code(409); // 409 Conflict
        echo json_encode(['status' => 'error', 'message' => 'Email atau Nomor HP ini sudah terdaftar sebagai akun. Silakan login.']);
        exit;
    }
    $stmtCheck->close();

    // 4. Proses Pendaftaran (Gunakan Transaksi)
    $conn->begin_transaction();

    try {
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $tgl_pembuatan = date('Y-m-d H:i:s');

        // Insert ke user_asoka
        $stmtInsert = $conn->prepare("INSERT INTO user_asoka (no_hp, nama_lengkap, email, password, tgl_pembuatan) VALUES (?, ?, ?, ?, ?)");
        $stmtInsert->bind_param("sssss", $no_hp_trusted, $nama_lengkap, $email, $hashed_password, $tgl_pembuatan);
        $stmtInsert->execute();
        $stmtInsert->close();

        // Tandai token sebagai 'used'
        $stmtUpdate = $conn->prepare("UPDATE reset_token SET used = 1 WHERE token = ?");
        $stmtUpdate->bind_param("s", $token);
        $stmtUpdate->execute();
        $stmtUpdate->close();

        // Commit transaksi
        $conn->commit();

        echo json_encode(['status' => 'success', 'message' => 'Pendaftaran berhasil! Anda akan diarahkan ke halaman login.']);

    } catch (Exception $e) {
        $conn->rollback();
        throw $e; // Lempar error agar ditangkap oleh blok catch luar
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Terjadi kesalahan pada server.', 'error' => $e->getMessage()]);
}

if (isset($conn)) {
    $conn->close();
}
?>