<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
require_once __DIR__ . '/../../../aa_kon_sett.php';
require_once __DIR__ . '/../../auth/middleware_login.php';

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_response(405, ['error' => 'Method not allowed']);
}

$headers = getallheaders();
$token = extract_token($headers);
$verif = verify_token($token);

try {
    $input = get_input_data();
    $reward_id = validate_and_sanitize_input($input, $verif);
    $reward = get_reward_details($reward_id);
    $user_points = get_user_points($verif->no_hp);
    $store_id = trim($input['store_id']) ?? null;
    $cabang = trim($input['cabang']) ?? null;
    if ($user_points['total_poin_pk_pm'] < $reward['poin']) {
        send_response(400, ['success' => false, 'message' => 'Insufficient points']);
    }

    $redemption_code = $input['plu'] ?? null;
    $expires_at = date('Y-m-d H:i:s', strtotime('+6 hours'));

    $conn->begin_transaction();
    $inTransaction = true;

    insert_redemption_record($conn, $verif->id, $reward_id, $reward, $redemption_code, $expires_at, $cabang, $store_id);
    update_user_points($conn, $verif->no_hp, $reward, $redemption_code, $user_points,$store_id);
    update_reward_quantity($conn, $reward_id);

    $qr_url = generate_qr_code($redemption_code);
    $conn->commit();

    send_response(200, [
        'success' => true,
        'code' => $redemption_code,
        'qr' => $qr_url,
        'expires_at' => $expires_at,
        'message' => 'Redemption successful'
    ]);


} catch (Exception $e) {
    if (isset($conn) && $inTransaction) {
        $conn->rollBack();
    }
    send_response(400, ['success' => false, 'error' => $e->getMessage()]);
}

function extract_token($headers) {
    if (!isset($headers['Authorization']) || $headers['Authorization'] == null) {
        send_response(401, ['status' => false, 'message' => 'Request ditolak user tidak terdaftar']);
    }
    if (preg_match('/^Bearer\s(\S+)$/', $headers['Authorization'], $matches)) {
        return $matches[1];
    }
    send_response(401, ['status' => false, 'message' => 'Request ditolak user tidak terdaftar']);
}

function get_input_data() {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        send_response(400, ['success' => false, 'message' => 'Invalid input data']);
    }
    return $input;
}

function validate_and_sanitize_input($input, $verif) {
    if (!isset($input['reward_id']) || $verif->no_hp == null || !is_numeric($input['reward_id'])) {
        send_response(400, ['success' => false, 'message' => 'Invalid input data']);
    }
    return intval($input['reward_id']);
}

function get_reward_details($reward_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT nama_hadiah, poin FROM hadiah WHERE id_hadiah = ?");
    $stmt->bind_param('i', $reward_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        send_response(200, ['success' => false, 'message' => 'Reward not found']);
    }
    return $result->fetch_assoc();
}

function get_user_points($user_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT COALESCE(tp.total_poin_pk, 0) + COALESCE(tpm.total_poin_pm, 0) - COALESCE(pt.total_poin_pt, 0) AS total_poin_pk_pm FROM customers c LEFT JOIN (SELECT kd_cust, SUM(point_1) AS total_poin_pk FROM point_kasir GROUP BY kd_cust) AS tp ON c.kd_cust = tp.kd_cust LEFT JOIN (SELECT kd_cust, SUM(jum_point) AS total_poin_pm FROM point_manual GROUP BY kd_cust) AS tpm ON c.kd_cust = tpm.kd_cust LEFT JOIN (SELECT kd_cust, SUM(jum_point) AS total_poin_pt FROM point_trans GROUP BY kd_cust) AS pt ON c.kd_cust = pt.kd_cust WHERE c.kd_cust = ?");
    $stmt->bind_param('s', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        send_response(200, ['success' => false, 'message' => 'User not found']);
    }
    return $result->fetch_assoc();
}

function insert_redemption_record($conn, $user_id, $reward_id, $reward, $redemption_code, $expires_at, $cabang, $store_id) {
    $stmt = $conn->prepare("INSERT INTO hadiah_t (id_user, id_hadiah, nama_hadiah, poin_tukar, qr_code_url, status, dibuat_tanggal, expired_at, ditukar_tanggal, cabang, kd_store) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $now = date('Y-m-d H:i:s');
    $status = 'pending';
    $ditukar_tanggal = null;
    $stmt->bind_param('iisssssssss', $user_id, $reward_id, $reward['nama_hadiah'], $reward['poin'], $redemption_code, $status, $now, $expires_at, $ditukar_tanggal, $cabang, $store_id);
    if (!$stmt->execute()) {
        throw new Exception('Failed to insert redemption record');
    }
}

function update_user_points($conn, $user_id, $reward, $redemption_code, $user_points, $store_id) {
    $new_points = $reward['poin'];
    $nilai_point = $user_points['total_poin_pk_pm'] * 100000;
    $stmt = $conn->prepare("INSERT INTO point_trans (kd_cust, no_trans, tgl_entry, current_point, jum_point, nilai_point, point_tukar, tgl_trans, jam, kd_store, kd_branch) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $now = date('Y-m-d H:i:s');
    $clock = date('H:i:s');
    $stmt->bind_param('sssdddsssss', $user_id, $redemption_code, $now, $user_points['total_poin_pk_pm'], $new_points, $nilai_point, $reward['nama_hadiah'], $now, $clock, $store_id, $store_id);
    if (!$stmt->execute()) {
        throw new Exception('Failed to update user points');
    }
}

function update_reward_quantity($conn, $reward_id) {
    $stmt = $conn->prepare("UPDATE hadiah SET qty = qty - 1 WHERE id_hadiah = ? AND qty > 0");
    $stmt->bind_param('i', $reward_id);
    $stmt->execute();
    if ($stmt->affected_rows === 0) {
        throw new Exception('Reward quantity insufficient');
    }
}

function send_response($status_code, $data) {
    http_response_code($status_code);
    echo json_encode($data);
    exit;
}

function sanitize_input($data)
{
    return htmlspecialchars(strip_tags(trim($data)));
}


function generate_qr_code($code)
{
    return "/src/api/qr/code.php?number=" . urlencode($code);
}
