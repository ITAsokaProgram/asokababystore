<?php
require_once __DIR__ . '/../../../aa_kon_sett.php';
require_once __DIR__ . '/../../auth/middleware_login.php';
header('Content-Type: application/json');

try {
    $q = isset($_GET['q']) ? trim($_GET['q']) : '';
    $limit = 10;

    if ($q === '') {
        $sql = "SELECT DISTINCT group_aset FROM history_aset WHERE group_aset IS NOT NULL AND group_aset <> '' LIMIT ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $limit);
    } else {
        $like = "%{$q}%";
        $sql = "SELECT DISTINCT group_aset FROM history_aset WHERE group_aset IS NOT NULL AND group_aset <> '' AND group_aset LIKE ? LIMIT ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('si', $like, $limit);
    }

    if (!$stmt->execute()) throw new Exception('DB error: ' . $stmt->error);
    $res = $stmt->get_result();
    $items = [];
    while ($r = $res->fetch_assoc()) {
        $items[] = $r['group_aset'];
    }
    $stmt->close();

    echo json_encode(['status' => true, 'data' => $items]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => false, 'message' => $e->getMessage()]);
}
