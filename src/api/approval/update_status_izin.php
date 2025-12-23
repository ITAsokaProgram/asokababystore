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
$mode = $input['mode'] ?? 'single';
$new_status = $input['status'] ?? null;

$allowed_status = ['Izinkan', 'SO_Ulang'];
if (!in_array($new_status, $allowed_status)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Status tidak valid.']);
    exit;
}

try {

    if ($mode === 'bulk') {
        $items = $input['items'] ?? [];
        if (empty($items)) {
            echo json_encode(['success' => false, 'message' => 'Tidak ada item yang dipilih.']);
            exit;
        }

        $conn->begin_transaction();
        $update_sql = "UPDATE koreksi_izin 
                       SET izin_koreksi = ?, sync = 'False' 
                       WHERE no_faktur = ? AND plu = ? AND kd_store = ? AND (izin_koreksi IS NULL OR izin_koreksi = '')";

        $stmt = $conn->prepare($update_sql);
        $affected_count = 0;

        foreach ($items as $item) {
            $no_faktur = $item['no_faktur'];
            $plu = $item['plu'];
            $kd_store = $item['kd_store'];

            $stmt->bind_param("ssss", $new_status, $no_faktur, $plu, $kd_store);
            $stmt->execute();
            $affected_count += $stmt->affected_rows;
        }

        $conn->commit();
        $stmt->close();

        echo json_encode([
            'success' => true,
            'message' => 'Batch update berhasil.',
            'affected' => $affected_count
        ]);
        exit;
    } else {
        $no_faktur = $input['no_faktur'] ?? null;
        $plu = $input['plu'] ?? null;
        $kd_store = $input['kd_store'] ?? null;

        if (!$no_faktur || !$plu || !$kd_store) {
            echo json_encode(['success' => false, 'message' => 'Data input tidak lengkap.']);
            exit;
        }


        $check_sql = "SELECT izin_koreksi FROM koreksi_izin WHERE no_faktur = ? AND plu = ? AND kd_store = ?";
        $stmt_check = $conn->prepare($check_sql);
        $stmt_check->bind_param("sss", $no_faktur, $plu, $kd_store);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();
        $current_data = $result_check->fetch_assoc();
        $stmt_check->close();

        if (!$current_data) {
            echo json_encode(['success' => false, 'message' => 'Data transaksi tidak ditemukan.']);
            exit;
        }

        if (!empty($current_data['izin_koreksi'])) {
            echo json_encode([
                'success' => false,
                'message' => 'Item ini sudah diproses sebelumnya.'
            ]);
            exit;
        }

        $update_sql = "UPDATE koreksi_izin SET izin_koreksi = ?, sync = 'False' WHERE no_faktur = ? AND plu = ? AND kd_store = ?";
        $stmt_update = $conn->prepare($update_sql);
        $stmt_update->bind_param("ssss", $new_status, $no_faktur, $plu, $kd_store);
        $stmt_update->execute();

        if ($stmt_update->affected_rows > 0) {
            echo json_encode(['success' => true, 'message' => 'Status berhasil disimpan.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Tidak ada perubahan data.']);
        }
        $stmt_update->close();
    }

    $conn->close();

} catch (Exception $e) {
    if (isset($conn))
        $conn->rollback();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server Error: ' . $e->getMessage()]);
}
?>