<?php
session_start();
include '../../../aa_kon_sett.php';
header('Content-Type: application/json');

try {
    $search = $_GET['search_keyword'] ?? '';

    // PERBAIKAN: Select 'nama_flow' dan alias sebagai 'deskripsi' agar kompatibel dengan frontend JS
    $sql = "SELECT id, keyword, nama_flow as deskripsi, status_aktif, created_at, max_global_usage, max_user_usage, current_global_usage, expired_at, pesan_habis, pesan_sudah_klaim 
            FROM wa_flows 
            WHERE 1=1";

    $params = [];
    $types = "";

    if (!empty($search)) {
        // PERBAIKAN: Cari berdasarkan nama_flow
        $sql .= " AND (keyword LIKE ? OR nama_flow LIKE ?)";
        $types .= "ss";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    $sql .= " ORDER BY id DESC";

    $stmt = $conn->prepare($sql);
    if (!empty($params))
        $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    $flows = [];
    while ($row = $result->fetch_assoc()) {
        $row['steps'] = [];
        $flows[$row['id']] = $row;
    }
    $stmt->close();

    // Get Steps
    if (!empty($flows)) {
        $ids = implode(',', array_keys($flows));
        $sqlSteps = "SELECT * FROM wa_flow_steps WHERE flow_id IN ($ids) ORDER BY flow_id, urutan ASC";
        $resSteps = $conn->query($sqlSteps);

        if ($resSteps) {
            while ($step = $resSteps->fetch_assoc()) {
                $decoded = json_decode($step['isi_pesan'], true);
                if (json_last_error() === JSON_ERROR_NONE)
                    $step['isi_pesan'] = $decoded;

                $flows[$step['flow_id']]['steps'][] = $step;
            }
        }
    }

    foreach ($flows as &$f) {
        $f['total_steps'] = count($f['steps']);
    }

    echo json_encode(['data' => array_values($flows), 'success' => true]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>