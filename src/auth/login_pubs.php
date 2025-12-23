<?php

require_once __DIR__ . '/middleware_login.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");



if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $inputJSON = file_get_contents("php://input");
    $data = json_decode($inputJSON, true);

    if (!isset($data['identifier']) || !isset($data['pass'])) {
        echo json_encode(['status' => 'error', 'message' => 'Data tidak lengkap.']);
        exit;
    }

    $identifier = $data['identifier'];
    $pass = $data['pass'];
    
    include __DIR__ . '/../../aa_kon_sett.php';

    $sql = "SELECT id_user, email, nama_lengkap, no_hp, password FROM user_asoka WHERE email = ? OR no_hp = ?";
    
    $result = checkUser($conn, $sql, $pass, $identifier);

    
    if (isset($result['status']) && $result['status'] === 'success') {
        
        if (isset($result['id_user'], $result['email'], $result['no_hp'])) {
            
            associateGuestMessages($conn, $result['id_user'], $result['email'], $result['no_hp']);
        }
    }
    
    
    if (isset($conn)) {
        $conn->close();
    }
    
    echo json_encode(["User" => $result]);
}