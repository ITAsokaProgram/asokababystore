<?php
require_once __DIR__ . "/../../aa_kon_sett.php"; // Sesuaikan path ke koneksi DB
$env = parse_ini_file(__DIR__ . '/../../.env'); // Sesuaikan path ke .env

header("Content-Type: application/json");

try {
    $inputJSON = file_get_contents("php://input");
    $data = json_decode($inputJSON, true);

    if (!isset($data['identifier'])) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Data tidak lengkap.']);
        exit;
    }

    $identifier = $data['identifier'];

    // Cek apakah identifier adalah nomor HP
    // Asumsi: No HP valid Indonesia diawali '08' dan punya 10-13 digit
    $isPhone = preg_match('/^08\d{8,11}$/', $identifier);

    if (!$isPhone) {
        // Jika bukan HP (berarti email), langsung ke login normal
        echo json_encode(['status' => 'normal_login']);
        exit;
    }

    // --- Identifier ADALAH Nomor HP ---

    // 1. Cek di table 'customers'
    $stmtCust = $conn->prepare("SELECT 1 FROM customers WHERE kd_cust = ?");
    $stmtCust->bind_param("s", $identifier);
    $stmtCust->execute();
    $resultCust = $stmtCust->get_result();
    $stmtCust->close();

    if ($resultCust->num_rows === 0) {
        // Jika No HP tidak ada di 'customers', login normal
        echo json_encode(['status' => 'normal_login']);
        exit;
    }

    // --- No HP ADA di 'customers' ---

    // 2. Cek apakah sudah ada di 'user_asoka'
    $stmtUser = $conn->prepare("SELECT 1 FROM user_asoka WHERE no_hp = ?");
    $stmtUser->bind_param("s", $identifier);
    $stmtUser->execute();
    $resultUser = $stmtUser->get_result();
    $stmtUser->close();

    if ($resultUser->num_rows > 0) {
        // Jika sudah ada di 'user_asoka' (sudah jadi user), login normal
        echo json_encode(['status' => 'normal_login']);
        exit;
    }

    // --- No HP ada di 'customers' TAPI BELUM ada di 'user_asoka' ---
    // Ini adalah alur verifikasi untuk pendaftaran baru

    // Hapus token lama (jika ada) untuk nomor ini
    $stmtDelete = $conn->prepare("DELETE FROM reset_token WHERE no_hp = ?");
    $stmtDelete->bind_param("s", $identifier);
    $stmtDelete->execute();
    $stmtDelete->close();

    // Buat token baru
    $token = "reg_" . bin2hex(random_bytes(30)); // 'reg_' untuk membedakan
    $createdAt = date('Y-m-d H:i:s');
    $expiredAt = date('Y-m-d H:i:s', strtotime('+15 minutes'));

    $stmtInsert = $conn->prepare("INSERT INTO reset_token (no_hp, token, dibuat_tgl, kadaluarsa, used) VALUES (?, ?, ?, ?, 0)");
    $stmtInsert->bind_param("ssss", $identifier, $token, $createdAt, $expiredAt);

    if (!$stmtInsert->execute()) {
        throw new Exception("Gagal menyimpan token registrasi: " . $stmtInsert->error);
    }
    $stmtInsert->close();

    // Buat URL WhatsApp (mirip 'request_reset_link_wa.php')
    $nomorTujuan = $env['WHATSAPP_NOMOR_TUJUAN'];
    $pesanWA = "Halo Asoka, saya ingin mendaftarkan akun. Token saya: " . $token;
    $whatsappUrl = "https://wa.me/" . $nomorTujuan . "?text=" . urlencode($pesanWA);

    echo json_encode([
        'status' => 'verify_customer',
        'whatsapp_url' => $whatsappUrl
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Terjadi kesalahan pada server.', 'error' => $e->getMessage()]);
}

if (isset($conn)) {
    $conn->close();
}
?>