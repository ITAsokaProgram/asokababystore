<?php
require_once __DIR__ . "/../../../../aa_kon_sett.php";
require_once __DIR__ . "/../../../auth/middleware_login.php";
header("Content-Type:application/json");
header("Access-Control-Allow-Methods: POST");
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Metode yang diizinkan hanya POST']);
    exit;
}
try {
    $verif = authenticate_request();
    $userId = $verif->id;
    $input = json_decode(file_get_contents('php://input'), true);
    $currentPassword = $input['current_password'] ?? '';
    $newEmail = $input['new_email'] ?? '';
    if (empty($currentPassword) || empty($newEmail)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Password dan Email baru wajib diisi.']);
        exit;
    }
    if (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Format email tidak valid.']);
        exit;
    }
    $stmt = $conn->prepare("SELECT id_user, password, email, no_hp FROM user_asoka WHERE id_user = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$user) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'User tidak ditemukan.']);
        exit;
    }
    if (!password_verify($currentPassword, $user['password'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Password saat ini salah.']);
        exit;
    }
    if ($user['email'] === $newEmail) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Email baru tidak boleh sama dengan email lama.']);
        exit;
    }
    $stmtCheck = $conn->prepare("SELECT id_user FROM user_asoka WHERE email = ? AND id_user != ?");
    $stmtCheck->bind_param("si", $newEmail, $userId);
    $stmtCheck->execute();
    $resultCheck = $stmtCheck->get_result();
    if ($resultCheck->num_rows > 0) {
        $stmtCheck->close();
        http_response_code(409);
        echo json_encode(['success' => false, 'message' => 'Email tersebut sudah digunakan oleh akun lain.']);
        exit;
    }
    $stmtCheck->close();
    $conn->begin_transaction();
    try {
        $stmtUpdateUser = $conn->prepare("UPDATE user_asoka SET email = ? WHERE id_user = ?");
        $stmtUpdateUser->bind_param("si", $newEmail, $userId);
        if (!$stmtUpdateUser->execute()) {
            throw new Exception("Gagal update user_asoka: " . $stmtUpdateUser->error);
        }
        $stmtUpdateUser->close();
        if (!empty($user['no_hp'])) {
            $stmtUpdateCust = $conn->prepare("UPDATE customers SET email = ? WHERE kd_cust = ?");
            $stmtUpdateCust->bind_param("ss", $newEmail, $user['no_hp']);
            if (!$stmtUpdateCust->execute()) {
                throw new Exception("Gagal update customers: " . $stmtUpdateCust->error);
            }
            $stmtUpdateCust->close();
        }
        $conn->commit();
        http_response_code(200);
        echo json_encode(['success' => true, 'message' => 'Email berhasil diperbarui. Silakan login ulang dengan email baru.']);
    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan server.', 'error_detail' => $e->getMessage()]);
} finally {
    if (isset($conn))
        $conn->close();
}
?>