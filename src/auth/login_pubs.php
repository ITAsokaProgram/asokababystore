<?php

require_once __DIR__ . '/middleware_login.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");


function associateGuestMessages($conn, $userId, $email, $noHp) {
    if (empty($userId) || (empty($email) && empty($noHp))) {
        return;
    }

    $sql = "UPDATE contact_us SET id_user = ? WHERE id_user IS NULL AND (email = ? OR no_hp = ?)";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        
        $stmt->bind_param('iss', $userId, $email, $noHp);
        $stmt->execute();
        $stmt->close();
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $inputJSON = file_get_contents("php://input");
    $data = json_decode($inputJSON, true);

    if (!isset($data['email']) || !isset($data['pass'])) {
        echo json_encode(['status' => 'error', 'message' => 'Data tidak lengkap.']);
        exit;
    }

    $email = $data['email'];
    $pass = $data['pass'];

    
    $sql = "SELECT id_user, email, nama_lengkap, no_hp, password FROM user_asoka WHERE email = ?";
    $result = checkUser($sql, $pass, $email);

    
    
      if (isset($result['status']) && $result['status'] === 'success') {
        include __DIR__ . '/../../aa_kon_sett.php';

        if (isset($result['id_user'], $result['email'], $result['no_hp'])) {
            associateGuestMessages($conn, $result['id_user'], $result['email'], $result['no_hp']);
        }
        
        if (isset($conn)) {
            $conn->close();
        }
    }

    
    echo json_encode(["User" => $result]);
}