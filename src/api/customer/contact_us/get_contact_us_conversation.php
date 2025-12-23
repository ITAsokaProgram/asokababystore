<?php
require_once __DIR__ . ("/../../../../aa_kon_sett.php");
require_once __DIR__ . ("/../../../auth/middleware_login.php");
header('Content-Type: application/json');
try {
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
    $accessor_type = null;
    if (isset($verif->id)) {
        $accessor_type = 'customer';
    } elseif (isset($verif->kode)) {
        $accessor_type = 'admin';
    } else {
        throw new Exception("Tipe pengguna tidak dikenali dari token", 401);
    }
    $type_to_mark_as_read = ($accessor_type === 'customer') ? 'admin' : 'customer';
    $contact_us_id = filter_input(INPUT_GET, 'contact_us_id', FILTER_VALIDATE_INT);
    if (!$contact_us_id) {
        throw new Exception("Contact US ID tidak valid", 400);
    }
    $conn->begin_transaction();
    $update_sql = "UPDATE contact_us_conversation SET sudah_dibaca = 1 WHERE contact_us_id = ? AND pengirim_type = ? AND sudah_dibaca = 0";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("is", $contact_us_id, $type_to_mark_as_read);
    $update_stmt->execute();
    $update_stmt->close();
    $sql = "SELECT pengirim_type, pesan, CONCAT(dibuat_tgl, ' ', dibuat_jam) as dibuat_tgl FROM contact_us_conversation WHERE contact_us_id = ? ORDER BY dibuat_tgl, dibuat_jam ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $contact_us_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $conversation = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    $conn->commit();
    echo json_encode(['success' => true, 'data' => $conversation]);
} catch (Exception $e) {
    if (isset($conn) && $conn->in_transaction) $conn->rollback();
    http_response_code($e->getCode() ?: 500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} finally {
    if (isset($conn)) $conn->close();
}