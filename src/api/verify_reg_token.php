<?php
require_once __DIR__ . "/../../aa_kon_sett.php"; // Sesuaikan path koneksi DB
header("Content-Type: application/json");

try {
    $inputJSON = file_get_contents("php://input");
    $data = json_decode($inputJSON, true);

    if (!isset($data['token'])) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Token tidak ditemukan.']);
        exit;
    }

    $token = $data['token'];

    // 1. Verifikasi token di 'reset_token'
    $stmt = $conn->prepare("SELECT no_hp FROM reset_token WHERE token = ? AND used = 0 AND kadaluarsa > NOW() AND token LIKE 'final_reg_%'");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $resultToken = $stmt->get_result();

    if ($resultToken->num_rows === 0) {
        $stmt->close();
        http_response_code(401);
        echo json_encode(['status' => 'error', 'message' => 'Token tidak valid, sudah digunakan, atau telah kedaluwarsa.']);
        exit;
    }

    $tokenData = $resultToken->fetch_assoc();
    $no_hp = $tokenData['no_hp'];
    $stmt->close();

    // 2. Ambil nama_cust dari tabel 'customers'
    $stmtCust = $conn->prepare("SELECT nama_cust FROM customers WHERE kd_cust = ?");
    $stmtCust->bind_param("s", $no_hp);
    $stmtCust->execute();
    $resultCust = $stmtCust->get_result();

    if ($resultCust->num_rows === 0) {
        $stmtCust->close();
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Data customer tidak dapat ditemukan untuk No. HP ini.']);
        exit;
    }

    $customerData = $resultCust->fetch_assoc();
    $nama_cust = $customerData['nama_cust'];
    $stmtCust->close();

    // 3. Kirim data kembali ke frontend
    echo json_encode([
        'status' => 'success',
        'no_hp' => $no_hp,
        'nama_cust' => $nama_cust
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Terjadi kesalahan pada server.', 'error' => $e->getMessage()]);
}

if (isset($conn)) {
    $conn->close();
}
?>