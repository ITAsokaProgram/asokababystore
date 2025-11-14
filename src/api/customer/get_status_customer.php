<?php
include "../../../aa_kon_sett.php";
include "../../auth/verify_tokens.php";
header("Content-Type: application/json");
$data = json_decode(file_get_contents("php://input"), true);
$headers = getallheaders();
if (!isset($headers['Authorization'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Authorization header missing']);
    exit;
}
$authHeader = $headers['Authorization'];
$token = str_replace('Bearer ', '', $authHeader);
$verify = verify_token($token);
if (!$verify || !isset($verify->email)) {
    http_response_code(401);
    echo json_encode(['status' => 'unauthorized', 'message' => 'Token tidak valid atau tidak memiliki email']);
    exit;
}
$email = $verify->email;
$sql = "SELECT 
            ua.no_hp AS phone_number,
            ua.nama_lengkap AS nama_lengkap,
            CASE 
                WHEN c.kd_cust IS NOT NULL THEN 'member'
                ELSE 'non-member'
            END AS status_member,
            CASE
                WHEN c.nama_cust IS NULL OR c.nama_cust = '' OR
                     c.alamat IS NULL OR c.alamat = '' OR
                     c.Prov IS NULL OR c.Prov = '' OR
                     c.Kota IS NULL OR c.Kota = '' OR
                     c.Kec IS NULL OR c.Kec = '' OR
                     c.Kel IS NULL OR c.Kel = '' OR
                     c.alamat_ds IS NULL OR c.alamat_ds = '' OR
                     c.Prov_ds IS NULL OR c.Prov_ds = '' OR 
                     c.Kota_ds IS NULL OR c.Kota_ds = '' OR 
                     c.Kec_ds IS NULL OR c.Kec_ds = '' OR 
                     c.Kel_ds IS NULL OR c.Kel_ds = '' OR 
                     c.tgl_lahir IS NULL OR 
                     c.Jenis_kel IS NULL OR c.Jenis_kel = '' OR 
                     c.juml_anak IS NULL 
                THEN 1 
                ELSE 0 
            END AS updated
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