<?php
$env = parse_ini_file('.env');

$conn = mysqli_connect(
    $env['DB_HOST'],
    $env['DB_USER'],
    $env['DB_PASS'],
    $env['DB_NAME']
);
if (!$conn) {
    $_SESSION['error'] = 'Failed Koneksi'; // Simpan error di session
    header('Location: errorpg.php');
    exit();
}


 // Set the default timezone
 date_default_timezone_set('Asia/Jakarta');
 
 #Versi CSS

 // Jika sedang development, gunakan time() agar selalu refresh CSS
 // Jika di live server, gunakan filemtime() agar cache tetap optimal
 $is_dev = true; // Ubah ke true saat development
 // --> false (Live Server)
 // --> true (Development)
 $css_version = $is_dev ? time() : filemtime('css/style.css');

 
