<?php

require_once __DIR__ . '/middleware_login.php';
header("Content-Type: application/json");
$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sql = "SELECT id_user, no_hp FROM user_asoka WHERE no_hp = ?";
    $phone = $input['phone'] ?? die("No Handphone tidak ditemukan");
    $result = checkUserPhone($sql, $phone);
    echo json_encode($result);
    exit;
}
