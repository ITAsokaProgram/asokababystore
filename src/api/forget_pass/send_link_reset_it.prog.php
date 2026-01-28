<?php

require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/../../../aa_kon_sett.php';
$env = parse_ini_file(__DIR__ . '/../../../.env');
require_once __DIR__ . '/../../auth/middleware_login.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use PHPMailer\PHPMailer\OAuth;
use PHPMailer\PHPMailer\PHPMailer;
use League\OAuth2\Client\Provider\Google;


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $inputJSON = file_get_contents('php://input');
    $input = json_decode($inputJSON, true);
    $email = $input['email'] ?? die("Email tidak ditemukan");
    $token = bin2hex(random_bytes(32));



    // Inisialisasi PHPMailer
    $mail = new PHPMailer(true);

    // Konfigurasi OAuth2
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->Port = 587;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->SMTPAuth = true;
    $mail->AuthType = 'XOAUTH2';

    // Ganti konstanta dengan variabel dari $env
    $emailSender = $env['GOOGLE_SENDER_MAIL'];
    $clientId = $env['GOOGLE_CLIENT_ID'];
    $clientSecret = $env['GOOGLE_CLIENT_SECRET'];
    $refreshToken = $env['GOOGLE_REFRESH_TOKEN'];

    $provider = new Google(
        [
            'clientId' => $clientId,
            'clientSecret' => $clientSecret,
        ]
    );

    $mail->setOAuth(
        new OAuth(
            [
                'provider' => $provider,
                'clientId' => $clientId,
                'clientSecret' => $clientSecret,
                'refreshToken' => $refreshToken,
                'userName' => $emailSender,
            ]
        )
    );

    // Html Template
    $htmlContent = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/email_template.html');
    $resetLink = "https://asokababystore.com/src/fitur/pubs/user/reset/reset_password.php?token=$token";
    $placeholders = [
        '{{reset_link}}' => $resetLink,
        '{{email}}' => $email,
        '{{store_name}}' => 'ASOKA Baby Store',
        '{{store_url}}' => 'https://asokababystore.com',
        '{{store_logo}}' => 'https://asokababystore.com/public/images/logo.png',
    ];


    foreach ($placeholders as $key => $value) {
        $htmlContent = str_replace($key, $value, $htmlContent);
    }

    // Email
    $mail->setFrom($emailSender, 'ASOKA Baby Store');
    $mail->addAddress($email);
    $mail->Subject = 'Reset Password';
    $mail->isHTML(true);
    $mail->Body = $htmlContent;

    $mail->SMTPDebug = 2;
    $mail->Debugoutput = function ($str, $level) {
    };


    try {
        // Cek apakah email ada di database user
        $checkEmail = checkEmail($conn, $email);
        $createdAt = date('Y-m-d H:i:s');
        $expiredAt = date('Y-m-d H:i:s', strtotime('+30 minutes'));
        if ($checkEmail['status'] !== 'success') {
            http_response_code(404);
            echo json_encode([
                'status' => 'error',
                'message' => 'Email tidak ditemukan.'
            ]);
            exit;
        }

        // Cek apakah ada token aktif yang belum expired
        $stmt = $conn->prepare("SELECT * FROM reset_token WHERE email = ? AND used = 0 AND dibuat_tgl > ? - INTERVAL 1 HOUR");
        $stmt->bind_param("ss", $email, $createdAt);
        $stmt->execute();
        $result = $stmt->get_result();
        $existingToken = $result->fetch_assoc();

        // Jika ada token aktif sebelumnya, hapus dulu
        if ($existingToken) {
            $del = $conn->prepare("DELETE FROM reset_token WHERE email = ?");
            $del->bind_param("s", $email);
            $del->execute();
        }

        // Kirim email
        $mail->send();

        // Simpan token baru

        $insert = $conn->prepare("INSERT INTO reset_token (email, token, dibuat_tgl, kadaluarsa, used) VALUES (?, ?, ?, ?, 0)");
        $insert->bind_param("ssss", $email, $token, $createdAt, $expiredAt);
        $insert->execute();

        http_response_code(200);
        echo json_encode([
            'status' => 'success',
            'message' => 'Link reset password berhasil dikirim ke email Anda.'
        ]);

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'Mailer Error: ' . $mail->ErrorInfo,
            'exception' => $e->getMessage()
        ]);
    }

} else {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'message' => 'Metode HTTP tidak diizinkan.'
    ]);
}