<?php
include '../../../aa_kon_sett.php';
require_once __DIR__ . "/../../auth/middleware_login.php";
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Origin: *"); 
$verif = authenticate_request();


if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Metode tidak diizinkan']);
    exit;
}

$id_user = $verif->kode;

// Ambil menu dari user_internal_access
$stmt = $conn->prepare("SELECT menu_code FROM user_internal_access WHERE id_user = ? AND can_view = 1");
$stmt->bind_param("i", $id_user);
$stmt->execute();
$result = $stmt->get_result();

$menus = [];
while ($row = $result->fetch_assoc()) {
    $menus[] = $row['menu_code'];
}

$stmt->close();

http_response_code(200);
echo json_encode(['status' => 'success', 'data' => $menus]);

$conn->close();
