<?php
require_once __DIR__ . '/../../../aa_kon_sett.php';

// Function to mark redemption as successful
function mark_redemption_success($redemption_code) {
    global $conn;

    // Update the redemption status to success
    $stmt = $conn->prepare("UPDATE hadiah_t SET status = 'success', ditukar_tanggal = NOW() WHERE qr_code_url = ?");
    $stmt->bind_param('s', $redemption_code);

    if (!$stmt->execute()) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to mark redemption as successful']);
        exit;
    }

    echo json_encode(['success' => true, 'message' => 'Redemption marked as successful']);
}

// Example usage
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['redemption_code'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Redemption code is required']);
        exit;
    }

    $redemption_code = $input['redemption_code'];
    mark_redemption_success($redemption_code);
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
