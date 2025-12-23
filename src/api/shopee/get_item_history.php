<?php
session_start();
ini_set('display_errors', 0); 

try {
    include '../../../aa_kon_sett.php';
    require_once __DIR__ . '/../../auth/middleware_login.php'; 
} catch (Throwable $t) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal Server Error: Gagal memuat file dependensi.']);
    exit();
}

header('Content-Type: application/json');

try {
    $authHeader = null;
    if (function_exists('getallheaders')) {
        $headers = getallheaders();
        if (isset($headers['Authorization'])) {
            $authHeader = $headers['Authorization'];
        }
    }
    if ($authHeader === null && isset($_SERVER['HTTP_AUTHORIZATION'])) {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
    }
    if ($authHeader === null && isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
        $authHeader = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
    }
    if ($authHeader === null || !preg_match('/^Bearer\s(\S+)$/', $authHeader, $matches)) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => "Token tidak ditemukan atau format salah."]);
        exit;
    }
    
    $token = $matches[1];
    $decoded = verify_token($token); // Verifikasi token
    if (!is_object($decoded) || !isset($decoded->kode)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Token tidak valid.']);
        exit;
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Token validation error: ' . $e->getMessage()]);
    exit;
}
// --- Akhir Autentikasi ---


if (!isset($conn) || !$conn instanceof mysqli) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Koneksi database tidak terinisialisasi.']);
    exit();
}

$plu = $_GET['plu'] ?? '';

if (empty($plu)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'PLU wajib diisi.']);
    exit();
}

try {
    $sql = "SELECT tgl_pesan, no_faktur, no_lpb, QTY_REC, hrg_beli, kode_kasir 
            FROM s_receipt 
            WHERE plu = ? 
            ORDER BY tgl_pesan DESC
            LIMIT 100";
    
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        throw new Exception("Database prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param("s", $plu);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $history = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $history[] = $row;
        }
    }
    $stmt->close();
    $conn->close();

    echo json_encode(['success' => true, 'history' => $history]);

} catch (Throwable $t) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => "Terjadi kesalahan: " . $t->getMessage()
    ]);
}
?>