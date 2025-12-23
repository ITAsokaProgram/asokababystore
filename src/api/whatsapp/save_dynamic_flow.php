<?php
// Cegah output error PHP langsung ke browser
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Mulai Output Buffering
ob_start();

session_start();
require_once __DIR__ . '/../../../aa_kon_sett.php';
require_once __DIR__ . '/../../utils/Logger.php';

header('Content-Type: application/json');

$logger = new AppLogger('save_dynamic_flow.log');

try {
    $rawInput = file_get_contents('php://input');

    if (!$rawInput) {
        throw new Exception("Tidak ada data yang diterima.");
    }

    $input = json_decode($rawInput, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("JSON Invalid: " . json_last_error_msg());
    }

    // Mapping Input
    $mode = $input['mode'] ?? 'insert';
    $id = $input['id'] ?? null;
    $keyword = trim($input['keyword'] ?? '');

    // NOTE: Di JS dikirim sebagai 'deskripsi', tapi di DB masuk ke 'nama_flow'
    $nama_flow = $input['deskripsi'] ?? '';

    $expired_at = !empty($input['expired_at']) ? $input['expired_at'] : null;
    $max_global = (int) ($input['max_global_usage'] ?? 0);
    $max_user = (int) ($input['max_user_usage'] ?? 0);
    $status = (int) ($input['status_aktif'] ?? 1);
    $msg_habis = $input['pesan_habis'] ?? '';
    $msg_klaim = $input['pesan_sudah_klaim'] ?? '';
    $steps = $input['steps'] ?? [];

    if (empty($keyword))
        throw new Exception("Keyword wajib diisi.");
    if (empty($steps))
        throw new Exception("Minimal 1 langkah (step) diperlukan.");

    $conn->begin_transaction();

    if ($mode === 'insert') {
        // Cek keyword unique
        $check = $conn->query("SELECT id FROM wa_flows WHERE keyword = '$keyword'");
        if ($check && $check->num_rows > 0) {
            throw new Exception("Keyword '$keyword' sudah ada.");
        }

        // PERBAIKAN: Menggunakan kolom 'nama_flow'
        $sql = "INSERT INTO wa_flows (keyword, nama_flow, expired_at, max_global_usage, max_user_usage, status_aktif, pesan_habis, pesan_sudah_klaim) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);

        if (!$stmt)
            throw new Exception("Prepare Insert Gagal: " . $conn->error);

        $stmt->bind_param("sssiisss", $keyword, $nama_flow, $expired_at, $max_global, $max_user, $status, $msg_habis, $msg_klaim);

        if (!$stmt->execute())
            throw new Exception("Eksekusi Insert Gagal: " . $stmt->error);

        $id = $conn->insert_id;
        $stmt->close();

    } elseif ($mode === 'update') {
        if (!$id)
            throw new Exception("ID Flow tidak ditemukan.");

        // PERBAIKAN: Menggunakan kolom 'nama_flow'
        $sql = "UPDATE wa_flows SET keyword=?, nama_flow=?, expired_at=?, max_global_usage=?, max_user_usage=?, status_aktif=?, pesan_habis=?, pesan_sudah_klaim=? WHERE id=?";
        $stmt = $conn->prepare($sql);

        if (!$stmt)
            throw new Exception("Prepare Update Gagal: " . $conn->error);

        $stmt->bind_param("sssiisssi", $keyword, $nama_flow, $expired_at, $max_global, $max_user, $status, $msg_habis, $msg_klaim, $id);

        if (!$stmt->execute())
            throw new Exception("Eksekusi Update Gagal: " . $stmt->error);

        $stmt->close();

        // Reset steps
        $conn->query("DELETE FROM wa_flow_steps WHERE flow_id = $id");
    }

    // Insert Steps
    $sqlStep = "INSERT INTO wa_flow_steps (flow_id, tipe_respon, isi_pesan, key_penyimpanan, urutan) VALUES (?, ?, ?, ?, ?)";
    $stmtStep = $conn->prepare($sqlStep);

    if (!$stmtStep)
        throw new Exception("Prepare Steps Gagal: " . $conn->error);

    foreach ($steps as $idx => $step) {
        $urutan = $idx + 1;
        $tipe = $step['tipe_respon'];
        $key = $step['key_penyimpanan'] ?? '';
        $isi = json_encode($step['isi_pesan']);

        $stmtStep->bind_param("isssi", $id, $tipe, $isi, $key, $urutan);

        if (!$stmtStep->execute()) {
            throw new Exception("Gagal simpan step ke-$urutan: " . $stmtStep->error);
        }
    }
    $stmtStep->close();

    $conn->commit();
    ob_end_clean();
    echo json_encode(['success' => true, 'message' => 'Flow berhasil disimpan.']);

} catch (Exception $e) {
    if (isset($conn) && $conn->connect_errno == 0)
        $conn->rollback();

    $logger->error("Save Failed: " . $e->getMessage());
    ob_end_clean();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>