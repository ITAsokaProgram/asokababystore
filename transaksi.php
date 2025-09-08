<?php
require_once __DIR__ . '/aa_kon_sett.php';
require_once __DIR__ . '/src/auth/middleware_login.php';

$token = $_COOKIE['token'];
$userId = null;
$ip = $_SERVER['REMOTE_ADDR'];
$ua = $_SERVER['HTTP_USER_AGENT'];
$page = $_SERVER['REQUEST_URI'];
$pageName = "Cek Struk";
if ($token) {
    $verify = verify_token($token);
    $userId = $verify->id;
    // Cek apakah sudah ada record dalam 5 menit terakhir
    $stmt = $conn->prepare("
    SELECT id FROM visitors
    WHERE COALESCE(user_id, ip) = COALESCE(?, ?)
      AND page = ?
      AND visit_time >= (NOW() - INTERVAL 5 MINUTE)
    LIMIT 1
");
    $stmt->bind_param("sss", $userId, $ip, $page);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
        $stmt = $conn->prepare("
        INSERT INTO visitors (user_id, ip, user_agent, page, page_name) 
        VALUES (?, ?, ?, ?, ?)
    ");
        $stmt->bind_param("issss", $userId, $ip, $ua, $page, $pageName);
        $stmt->execute();
    }
} else {
    header("Location:/log_in");
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Struk Transaksi</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet" />
    <link rel="icon" type="image/png" href="images/logo1.png">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        body {
            font-family: 'Courier New', Courier, monospace;
        }
    </style>
</head>

<body class="bg-gray-100 py-10">
    <div class="max-w-md mx-auto">
        <div class="flex justify-end mb-2">
            <button id="print" type="button" class="bg-gray-600 rounded text-sm shadow px-4 py-1 text-white">Print</button>
        </div>
        <div id="struk-container"
            class=" p-4 text-sm text-black font-mono leading-tight space-y-2">
            <!-- Isi struk akan dimuat di sini -->
        </div>
        <div id="struk-container-print"
            class=" p-4 text-sm text-black font-mono leading-tight space-y-2">
            <!-- Isi struk akan dimuat di sini -->
        </div>
    </div>
    <div id="pdf-container" class="hidden"></div>
    <script src="src/js/struk.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <!-- Tambahkan script html2pdf.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.2/html2pdf.bundle.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/dompurify@2.3.4/dist/purify.min.js"></script>
</body>

</html>