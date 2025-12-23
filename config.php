<?php
$env = parse_ini_file('.env');
$conn = mysqli_connect(
    $env['DB_HOST'],
    $env['DB_USER'],
    $env['DB_PASS'],
    $env['DB_NAME']
);
if (!$conn) {
    $_SESSION['error'] = 'Failed Koneksi';
    header('Location: errorpg.php');
    exit();
}
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
date_default_timezone_set('Asia/Jakarta');
$is_dev = true;
$css_version = $is_dev ? time() : filemtime('css/style.css');
