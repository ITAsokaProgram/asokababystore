<?php
include '../../../aa_kon_sett.php';
require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/../../../.env.php';

use PHPMailer\PHPMailer\OAuth;
use PHPMailer\PHPMailer\PHPMailer;
use League\OAuth2\Client\Provider\Google;

header('Content-Type: application/json');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

function updateQuery($conn, $sql, $params = []) {
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("SQL Prepare Error: " . $conn->error);
    }
    $types = str_repeat("s", count($params));
    $stmt->bind_param($types, ...$params);
    $result = $stmt->execute();
    if (!$result) {
        throw new Exception("SQL Execute Error: " . $stmt->error);
    }
    $stmt->close();
    return $result;
}

function getContact($conn, $sql) {
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $data;
}

function sintaksQuery($sql) {
    return $sql;
}


$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        $sql = sintaksQuery("
            SELECT 
                cu.id, 
                cu.no_hp, 
                cu.nama_lengkap, 
                cu.email, 
                cu.subject, 
                cu.message, 
                cu.status, 
                cu.dikirim,
                cu.id_user,
                (SELECT COUNT(*) 
                   FROM contact_us_conversation cuc 
                   WHERE cuc.contact_us_id = cu.id 
                     AND cuc.sudah_dibaca = 0 
                     AND cuc.pengirim_type = 'customer'
                ) as unread_count,
                CASE 
                    WHEN cu.id_user IS NOT NULL THEN 1
                    WHEN EXISTS (SELECT 1 FROM user_asoka ua WHERE ua.email = cu.email OR ua.no_hp = cu.no_hp) THEN 1
                    ELSE 0
                END AS is_user_registered
            FROM contact_us cu 
            ORDER BY cu.dikirim DESC
        ");
        $processQueryContact = getContact($conn, $sql);
        echo json_encode(['data' => $processQueryContact]);
    } elseif ($method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);

        $kode = $data['kode'] ?? null;
        $status = $data['status'] ?? null;
        $balasan = $data['balasan'] ?? null;
        $email_penerima = $data['email'] ?? null;

        if (empty($kode) || empty($status)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Data tidak lengkap.']);
            exit;
        }

        $sql = "UPDATE contact_us SET status = ? WHERE no_hp = ?";
        $params = [$status, $kode];
        updateQuery($conn, $sql, $params);

        if (!empty($balasan) && !empty($email_penerima)) {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->Port = 587;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->SMTPAuth = true;
            $mail->AuthType = 'XOAUTH2';

            $emailSender = GOOGLE_SENDER_MAIL;
            $clientId = GOOGLE_CLIENT_ID;
            $clientSecret = GOOGLE_CLIENT_SECRET;
            $refreshToken = GOOGLE_REFRESH_TOKEN;

            $provider = new Google(['clientId' => $clientId, 'clientSecret' => $clientSecret]);
            $mail->setOAuth(new OAuth(['provider' => $provider, 'clientId' => $clientId, 'clientSecret' => $clientSecret, 'refreshToken' => $refreshToken, 'userName' => $emailSender]));

        $htmlContent = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/template/email/balasan_laporan.html');
            if ($htmlContent === false) {
                throw new Exception("Template email tidak ditemukan.");
            }
            
            $placeholders = [
                '{{nama_pelanggan}}' => $data['nama'] ?? 'Pelanggan',
                '{{subjek_laporan}}' => $data['subject'] ?? 'Laporan Anda',
                '{{isi_laporan}}' => $data['laporan_awal'] ?? '-',
                '{{isi_balasan}}' => nl2br(htmlspecialchars($balasan)),
                '{{store_name}}' => 'ASOKA Baby Store',
                '{{store_url}}' => 'https://asokababystore.com',
                '{{store_logo}}' => 'https://asokababystore.com/public/images/logo.png',
            ];

            foreach ($placeholders as $key => $value) {
                $htmlContent = str_replace($key, $value, $htmlContent);
            }

            $mail->setFrom($emailSender, 'ASOKA Baby Store');
            $mail->addAddress($email_penerima);
            $mail->Subject = 'Re: ' . ($data['subject'] ?? 'Laporan Pelanggan');
            $mail->isHTML(true);
            $mail->Body = $htmlContent;

            $mail->send();
            echo json_encode(['success' => true, 'message' => 'Status berhasil diperbarui dan email balasan telah dikirim.']);
        } else {
            echo json_encode(['success' => true, 'message' => 'Status berhasil diperbarui.']);
        }
    }
} catch (Exception $e) {
    http_response_code(500); 
    echo json_encode([
        'success' => false,
        'message' => 'Terjadi kesalahan di server: ' . $e->getMessage()
    ]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
