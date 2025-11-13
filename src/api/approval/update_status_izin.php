<?php
session_start();
include '../../../aa_kon_sett.php';
header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit;
}
$input = json_decode(file_get_contents('php://input'), true);
$no_faktur = $input['no_faktur'] ?? null;
$plu = $input['plu'] ?? null;
$kd_store = $input['kd_store'] ?? null;
$new_status = $input['status'] ?? null;
if (!$no_faktur || !$plu || !$kd_store || !$new_status) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Data input tidak lengkap.']);
    exit;
}
$allowed_status = ['Izinkan', 'SO_Ulang'];
if (!in_array($new_status, $allowed_status)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Status tidak valid.']);
    exit;
}
try {
    $check_sql = "SELECT izin_koreksi FROM koreksi_izin WHERE no_faktur = ? AND plu = ? AND kd_store = ?";
    $stmt_check = $conn->prepare($check_sql);
    $stmt_check->bind_param("sss", $no_faktur, $plu, $kd_store);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    $current_data = $result_check->fetch_assoc();
    $stmt_check->close();
    if (!$current_data) {
        echo json_encode(['success' => false, 'message' => 'Data transaksi tidak ditemukan di database.']);
        exit;
    }
    if (!empty($current_data['izin_koreksi'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Gagal! Item ini sudah diproses (' . $current_data['izin_koreksi'] . '). Tidak bisa diedit dua kali.'
        ]);
        exit;
    }
    $update_sql = "UPDATE koreksi_izin 
                   SET izin_koreksi = ?, sync = 'False'
                   WHERE no_faktur = ? AND plu = ? AND kd_store = ?";
    $stmt_update = $conn->prepare($update_sql);
    $stmt_update->bind_param("ssss", $new_status, $no_faktur, $plu, $kd_store);
    $stmt_update->execute();
    if ($stmt_update->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'Status berhasil disimpan.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Tidak ada perubahan data yang disimpan.']);
    }
    $stmt_update->close();
    $conn->close();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server Error: ' . $e->getMessage()]);
}
?>