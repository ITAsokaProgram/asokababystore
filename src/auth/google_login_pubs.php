<?php

require_once __DIR__ . '/middleware_login.php';
include "../../aa_kon_sett.php";
require_once __DIR__ . '/generate_token.php';
header("Access-Control-Allow-Origin: *");

$tokenOrRedirect = handleGoogleLogin($conn);

// Setelah proses handleGoogleLogin dan mendapatkan response $tokenOrRedirect

if (is_string($tokenOrRedirect) && filter_var($tokenOrRedirect, FILTER_VALIDATE_URL)) {
    // Step awal redirect ke Google OAuth
    header("Location: $tokenOrRedirect");
    exit;
} else {
    // Callback dari Google
    ?>
    <html>

    <body>
        <script>
            window.onload = function () {
                console.log('Popup window loaded');
                console.log('Window opener:', window.opener);

                const data = <?php echo json_encode($tokenOrRedirect); ?>;

                // Coba kirim pesan ke window utama
                if (window.opener && typeof window.opener.postMessage === 'function') {
                    console.log('Sending message to opener');
                    window.opener.postMessage(data, "*");
                } else {
                    console.log('No opener window, trying localStorage');
                    // Simpan data ke localStorage
                    localStorage.setItem('googleLoginResponse', JSON.stringify(data));
                    // Trigger custom event
                    window.dispatchEvent(new Event('googleLoginResponse'));
                }

                // Tutup popup setelah 1 detik
                setTimeout(() => {
                    window.close();
                }, 500);
            };
        </script>
    </body>

    </html>
    <?php
    exit;
}


?>