<?php
require_once __DIR__ . '/middleware_login.php';
require_once __DIR__ . '/../../aa_kon_sett.php';

header("Content-Type: application/json");

$decoded = authenticate_request();

try {
    $kode_user = $decoded->kode;

    $stmt = $conn->prepare("SELECT expired_token FROM user_account WHERE kode = ?");
    $stmt->bind_param("s", $kode_user);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($expired_token);
        $stmt->fetch();

        $current_time = date('Y-m-d H:i:s');
        
        if ($expired_token < $current_time) {
            http_response_code(401);
            echo json_encode([
                'status' => 'error',
                'message' => 'Token sudah kadaluarsa, silakan login ulang.'
            ]);
        } else {
            echo json_encode([
                'status' => 'success',
                'message' => 'Token valid!',
                'data' => $decoded
            ]);
        }
    } else {
        http_response_code(404);
        echo json_encode([
            'status' => 'error',
            'message' => 'User tidak ditemukan.'
        ]);
    }

    $stmt->close();

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Terjadi kesalahan sistem',
        'error' => $e->getMessage()
    ]);
}
?>