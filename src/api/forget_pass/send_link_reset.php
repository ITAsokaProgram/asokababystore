<?php
require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/../../../aa_kon_sett.php';
$env = parse_ini_file(__DIR__ . '/../../../.env');
require_once __DIR__ . '/../../auth/middleware_login.php';
require_once __DIR__ . '/../../../src/utils/Logger.php';
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json");
ini_set('display_errors', 0);
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
    $checkEmail = checkEmail($conn, $email);
    if ($checkEmail['status'] !== 'success') {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Email tidak terdaftar.']);
        exit;
    }
    $token = bin2hex(random_bytes(32));
    $createdAt = date('Y-m-d H:i:s');
    $expiredAt = date('Y-m-d H:i:s', strtotime('+30 minutes'));
    $stmtDelete = $conn->prepare("DELETE FROM reset_token WHERE email = ?");
    $stmtDelete->bind_param("s", $email);
    $stmtDelete->execute();
    $stmtInsert = $conn->prepare("INSERT INTO reset_token (email, token, dibuat_tgl, kadaluarsa, used) VALUES (?, ?, ?, ?, 0)");
    $stmtInsert->bind_param("ssss", $email, $token, $createdAt, $expiredAt);
    if (!$stmtInsert->execute()) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Gagal membuat token reset.']);
        exit;
    }
    http_response_code(200);
    echo json_encode(['status' => 'success', 'message' => 'Link reset password sedang dikirim ke email Anda.']);
    if (function_exists('fastcgi_finish_request')) {
        fastcgi_finish_request();
    } else {
        flush();
    }
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = gethostbyname($env['SMTP_HOST']);
        $mail->SMTPAuth = true;
        $mail->Username = $env['SMTP_USER'];
        $mail->Password = $env['SMTP_PASS'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = $env['SMTP_PORT'];
        $mail->CharSet = 'UTF-8';
        $mail->Timeout = 15;
        $mail->Timelimit = 15;
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
        $mail->setFrom($env['SMTP_USER'], 'ASOKA Baby Store');
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = 'Reset Password Akun ASOKA Baby Store';
        $logoPath = __DIR__ . '/../../../public/images/logo.png';
        $logoCid = 'logo_asoka_id';

        if (file_exists($logoPath)) {
            $mail->addEmbeddedImage($logoPath, $logoCid, 'logo.png');
            $logoSrc = 'cid:' . $logoCid;
        } else {
            $logoSrc = 'https://asokababystore.com/public/images/logo.png';
        }

        $templatePath = __DIR__ . '/../../../email_template.html';
        if (is_readable($templatePath)) {
            $htmlContent = file_get_contents($templatePath);
        } else {
            $htmlContent = "Klik link ini untuk reset: {{reset_link}}";
        }

        $resetLink = "https://asokababystore.com/src/fitur/pubs/user/reset/reset_password.php?token=$token";

        $placeholders = [
            '{{reset_link}}' => $resetLink,
            '{{email}}' => htmlspecialchars($email),
            '{{store_name}}' => 'ASOKA Baby Store',
            '{{store_url}}' => 'https://asokababystore.com',
            '{{store_logo}}' => $logoSrc,
        ];
        foreach ($placeholders as $key => $value) {
            $htmlContent = str_replace($key, $value, $htmlContent);
        }
        $mail->Body = $htmlContent;
        $mail->AltBody = "Halo $email,\n\nUntuk mereset password Anda, silakan kunjungi link berikut: $resetLink\n\nSalam,\nTim ASOKA Baby Store";
        $mail->send();
    } catch (Exception $e) {
        error_log("Gagal kirim email reset ke $email. Error: " . $mail->ErrorInfo);
    }
} else {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Metode HTTP tidak diizinkan.']);
}