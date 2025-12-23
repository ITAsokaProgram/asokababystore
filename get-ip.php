<?php
// cek header proxy dulu (Cloudflare / load balancer)
if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
    $ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
    $ip = trim($ips[0]); // client asli di kiri
} else {
    $ip = $_SERVER['REMOTE_ADDR'];
}
echo "IP kamu: " . $ip;

?>
