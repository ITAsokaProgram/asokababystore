<?php
include "../../../aa_kon_sett.php";
include "../../auth/verify_tokens.php";

header("Content-Type: application/json");

// Ambil dan decode JSON input
$data = json_decode(file_get_contents("php://input"), true);

// Ambil header Authorization
$headers = getallheaders();

if (!isset($headers['Authorization'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Authorization header missing']);
    exit;
}

$authHeader = $headers['Authorization'];
$token = str_replace('Bearer ', '', $authHeader);

// Verifikasi token
$verify = verify_token($token);
if (!$verify || !isset($verify->email)) {
    http_response_code(401);
    echo json_encode(['status' => 'unauthorized', 'message' => 'Token tidak valid atau tidak memiliki email']);
    exit;
}

$email = $verify->email;

// Query ke database
$sql = "SELECT 
            ua.no_hp AS phone_number,
            ua.nama_lengkap AS nama_lengkap, c.upd_from_web as updated,
            CASE 
                WHEN c.kd_cust IS NOT NULL THEN 'member'
                ELSE 'non-member'
            END AS status_member
        FROM user_asoka ua
        LEFT JOIN customers c ON ua.no_hp = c.kd_cust
        WHERE ua.email = ?";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Gagal menyiapkan statement']);
    exit;
}

$stmt->bind_param("s", $email);

if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Gagal eksekusi database']);
    exit;
}

$result = $stmt->get_result();
$data = $result->fetch_assoc();

http_response_code(200);
echo json_encode([
    'status' => 'success',
    'message' => 'Data berhasil di-fetch',
    'data' => $data
]);

$stmt->close();
$conn->close();
?>

