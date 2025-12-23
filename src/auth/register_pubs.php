<?php

require_once __DIR__ . '/middleware_login.php';
include '../../aa_kon_sett.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $inputJSON = file_get_contents("php://input");
    $data = json_decode($inputJSON, true);
    $date = date("Y-m-d H:i:s");

    if (!$data['email'] || !$data['pass'] || !$data['name'] || !$data['phone']) {
        echo json_encode(['status' => 'error', 'message' => 'Data tidak lengkap.']);
        exit;
    }

    // Cek apakah email atau no_hp sudah ada
    $checkSql = "SELECT * FROM user_asoka WHERE email = ? OR no_hp = ?";
    $stmt = $conn->prepare($checkSql);
    $stmt->bind_param('ss', $data['email'], $data['phone']);
    $stmt->execute();
    $result = $stmt->get_result();
    $existing = $result ? $result->fetch_assoc() : null;

    if ($existing) {
        $emailMatch = $existing['email'] === $data['email'];
        $phoneMatch = $existing['no_hp'] === $data['phone'];

        if ($emailMatch && $phoneMatch) {
            $message = 'Email dan Nomor HP sudah terdaftar.';
        } else if ($emailMatch) {
            $message = 'Email sudah terdaftar.';
        } else if ($phoneMatch) {
            $message = 'Nomor HP sudah terdaftar.';
        }

        http_response_code(409);
        echo json_encode(['status' => 'error', 'message' => $message]);
        exit;
    }

    $sql = "INSERT INTO user_asoka (email,nama_lengkap,no_hp,password,provider,tgl_pembuatan) VALUES(?,?,?,?,?,?)";
    // Sanitasi nama hanya huruf dan spasi
    $sanitizedName = preg_replace('/[^a-zA-Z ]/', '', $data['name']);
    if(!$sanitizedName) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Nama tidak valid.']);
        exit;
    }
    $params = [
        $data['email'],
        $sanitizedName,
        $data['phone'],
        password_hash($data['pass'], PASSWORD_BCRYPT),
        "ASOKA",
        $date
    ];
    echo json_encode(regisUser($conn, $sql, ...$params));
    exit;
} else {
    echo json_encode(['status' => 'error', 'message' => 'Method tidak diizinkan.']);
    exit;
}