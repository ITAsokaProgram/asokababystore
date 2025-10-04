<?php
require_once __DIR__ . ("/../../../../aa_kon_sett.php");
require_once __DIR__ . ("/../../../auth/middleware_login.php");

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Metode yang diizinkan hanya POST", 405);
    }

    $headers = getallheaders();
    $authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : '';
    
    if (!preg_match('/^Bearer\s(\S+)$/', $authHeader, $matches)) {
        throw new Exception("Token tidak ditemukan atau format salah", 401);
    }
    
    $token = $matches[1];
    $verif = verify_token($token);
    
    if (!$verif) {
        throw new Exception("Token tidak valid", 401);
    }

    $pengirim_id = null;
    $pengirim_type = null;

    if (isset($verif->id)) { 
        $pengirim_id = $verif->id;
        $pengirim_type = 'customer';
    } elseif (isset($verif->kode)) { 
        $pengirim_id = $verif->kode;
        $pengirim_type = 'admin';
    } else {
        throw new Exception("Tipe pengguna tidak dikenali dari token", 401);
    }

    $data = json_decode(file_get_contents('php://input'), true);
    $contact_us_id = $data['contact_us_id'] ?? null;
    $pesan = trim($data['pesan'] ?? '');

    if (!$contact_us_id || empty($pesan)) {
        throw new Exception("ID dan pesan wajib diisi", 400);
    }

    
    $conn->begin_transaction();

    
    $sql_insert = "INSERT INTO contact_us_conversation (contact_us_id, pengirim_type, pengirim_id, pesan, dibuat_tgl, dibuat_jam) VALUES (?, ?, ?, ?, CURDATE(), CURTIME())";
    $stmt_insert = $conn->prepare($sql_insert);
    $stmt_insert->bind_param("isss", $contact_us_id, $pengirim_type, $pengirim_id, $pesan);
    
    if (!$stmt_insert->execute()) {
        throw new Exception("Gagal mengirim pesan: " . $stmt_insert->error, 500);
    }
    $stmt_insert->close();

    
    if ($pengirim_type === 'admin') {
        $sql_update = "UPDATE contact_us SET status = 'in_progress' WHERE id = ? AND status != 'in_progress'";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("i", $contact_us_id);

        if (!$stmt_update->execute()) {
            throw new Exception("Gagal memperbarui status: " . $stmt_update->error, 500);
        }
        $stmt_update->close();
    }

    
    $conn->commit();

    http_response_code(201);
    echo json_encode(['success' => true, 'message' => 'Pesan berhasil dikirim']);

} catch (Exception $e) {
    
    if (isset($conn) && $conn->in_transaction) {
        $conn->rollback();
    }
    http_response_code($e->getCode() ?: 500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}