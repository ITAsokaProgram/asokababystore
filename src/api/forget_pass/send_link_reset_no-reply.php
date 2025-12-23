
<?php

require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/../../../aa_kon_sett.php'; 
$env = parse_ini_file(__DIR__ . '/../../../.env');
require_once __DIR__ . '/../../auth/middleware_login.php'; 
require_once __DIR__ . '/../../../src/utils/Logger.php'; 

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$logger = new AppLogger('send_link_reset.log');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $inputJSON = file_get_contents('php://input');
    $input = json_decode($inputJSON, true);
    $email = $input['email'] ?? null;

    if (!$email) {
        http_response_code(400); 
        echo json_encode(['status' => 'error', 'message' => 'Email wajib diisi.']);
        exit;
    }

    $token = bin2hex(random_bytes(32));
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = $env['SMTP_HOST'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $env['SMTP_USER'];
        $mail->Password   = $env['SMTP_PASS'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = $env['SMTP_PORT'];

        $mail->CharSet = 'UTF-8';

        $mail->setFrom($env['SMTP_USER'], 'ASOKA Baby Store');
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = 'Reset Password Akun ASOKA Baby Store';

        $templatePath = __DIR__ . '/../../../email_template.html';
        if (!is_readable($templatePath)) {
            throw new Exception('File template email tidak ditemukan.');
        }
        $htmlContent = file_get_contents($templatePath);

        $logoUrl = 'https://asokababystore.com/public/images/logo.png';
        
        $resetLink = "https://asokababystore.com/src/fitur/pubs/user/reset/reset_password.php?token=$token";
        
        $placeholders = [
            '{{reset_link}}' => $resetLink,
            '{{email}}'      => htmlspecialchars($email),
            '{{store_name}}' => 'Asoka Baby Store',
            '{{store_url}}'  => 'https://asokababystore.com',
            '{{store_logo}}' => $logoUrl,
        ];

        foreach ($placeholders as $key => $value) {
            $htmlContent = str_replace($key, $value, $htmlContent);
        }

        $mail->Body = $htmlContent;

        $mail->AltBody = "Halo " . $email . ",\n\nUntuk mereset password Anda, silakan kunjungi link berikut: " . $resetLink . "\n\nSalam,\nTim ASOKA Baby Store";

        
        $checkEmail = checkEmail($conn, $email);
        if ($checkEmail['status'] !== 'success') {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Email tidak terdaftar.']);
            exit;
        }

        $stmtDelete = $conn->prepare("DELETE FROM reset_token WHERE email = ?");
        $stmtDelete->bind_param("s", $email);
        $stmtDelete->execute();

        $mail->send();

        $createdAt = date('Y-m-d H:i:s');
        $expiredAt = date('Y-m-d H:i:s', strtotime('+30 minutes'));

        $stmtInsert = $conn->prepare("INSERT INTO reset_token (email, token, dibuat_tgl, kadaluarsa, used) VALUES (?, ?, ?, ?, 0)");
        $stmtInsert->bind_param("ssss", $email, $token, $createdAt, $expiredAt);
        $stmtInsert->execute();

        http_response_code(200);
        echo json_encode(['status' => 'success', 'message' => 'Link reset password berhasil dikirim ke email Anda.']);

    } catch (Exception $e) {
        http_response_code(500); 
        echo json_encode([
            'status' => 'error',
            'message' => 'Gagal mengirim email. Mailer Error: ' . $mail->ErrorInfo
        ]);
    }

} else {
    http_response_code(405); 
    echo json_encode(['status' => 'error', 'message' => 'Metode HTTP tidak diizinkan.']);
}