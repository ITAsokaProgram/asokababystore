<?php
require_once __DIR__ . '/../../../vendor/autoload.php';

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;

function encryptNumber($number)
{
    $map = [
        "0" => "91",
        "1" => "82",
        "2" => "73",
        "3" => "64",
        "4" => "55",
        "5" => "46",
        "6" => "37",
        "7" => "29",
        "8" => "10",
        "9" => "01"
    ];

    $digits = str_split($number);
    $encrypted = '';
    foreach ($digits as $digit) {
        $encrypted .= $map[$digit];
    }

    return $encrypted;
}

function decryptNumber($encrypted)
{
    $map1 = [
        "91" => "0",
        "82" => "1",
        "73" => "2",
        "64" => "3",
        "55" => "4",
        "46" => "5",
        "37" => "6",
        "29" => "7",
        "10" => "8",
        "01" => "9"
    ];

    $result = '';
    for ($i = 0; $i < strlen($encrypted); $i += 2) {
        $pair = substr($encrypted, $i, 2);
        $result .= $map1[$pair];
    }

    return $result;
}

// RESTful API: GET /qr?number=... OR POST /qr { number: ... }
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    if (isset($_GET['info'])) {
        // GET /qr?info=1&number=...
        if (!isset($_GET['number']) || empty($_GET['number'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Number is required']);
            exit();
        }
        $myNumber = $_GET['number'];
        $encrypted = encryptNumber($myNumber);
        $decrypted = decryptNumber($encrypted);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'number' => $myNumber,
            'encrypted' => $encrypted,
            'decrypted' => $decrypted,
            'preview_url' => "/src/api/qr/code.php?number=$myNumber"
        ]);
        exit();
    }
    // GET /qr?number=...
    if (!isset($_GET['number']) || empty($_GET['number'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Number is required']);
        exit();
    }
    $myNumber = $_GET['number'];
    $encrypted = encryptNumber($myNumber);
    $result = Builder::create()
        ->writer(new PngWriter())
        ->data($encrypted)
        ->size(300)
        ->margin(10)
        ->build();
    header('Content-Type: image/png');
    echo $result->getString();
    exit();
}

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!isset($input['number']) || empty($input['number'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Number is required']);
        exit();
    }
    $myNumber = $input['number'];
    $encrypted = encryptNumber($myNumber);
    $result = Builder::create()
        ->writer(new PngWriter())
        ->data($encrypted)
        ->size(300)
        ->margin(10)
        ->build();
    header('Content-Type: image/png');
    echo $result->getString();
    exit();
}

// Method not allowed
http_response_code(405);
echo json_encode(['success' => false, 'message' => 'Method not allowed']);
exit();
