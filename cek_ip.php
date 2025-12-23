<?php
echo "<h3>Diagnosa IP</h3>";
echo "REMOTE_ADDR (Yang dibaca Apache): <b>" . $_SERVER['REMOTE_ADDR'] . "</b><br>";

if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    echo "HTTP_X_FORWARDED_FOR (IP Asli jika Proxy): <b>" . $_SERVER['HTTP_X_FORWARDED_FOR'] . "</b><br>";
}

if (isset($_SERVER['HTTP_CF_CONNECTING_IP'])) {
    echo "HTTP_CF_CONNECTING_IP (IP Asli jika Cloudflare): <b>" . $_SERVER['HTTP_CF_CONNECTING_IP'] . "</b><br>";
}
?>