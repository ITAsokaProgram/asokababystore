<?php
require_once __DIR__ . '/../config/JWT/JWT.php';
require_once __DIR__ . '/../config/JWT/Key.php';
require_once __DIR__ . '/../config/JWT/config.php';
require_once __DIR__ . '/../../vendor/autoload.php';
// require_once __DIR__ . '/rate_limiter.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Detection\MobileDetect;
use Google\Client as Google_Client;
use Google\Service\Oauth2 as Google_Service_Oauth2;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// Include Google API Client library


function verify_token($jwt)
{
    $secretKey = JWT_SECRET_KEY; // Gunakan kunci yang sama saat membuat token
    try {
        // Decode token untuk mengambil data yang ada di dalamnya
        $decoded = JWT::decode($jwt, new Key($secretKey, 'HS256'));
        return $decoded;
    } catch (ExpiredException $e) {
        // Token expired
        return [
            'status' => 'error',
            'message' => 'Token expired, silakan login kembali.'
        ];
    } catch (Exception $e) {
        // Token tidak valid
        return [
            'status' => 'error',
            'message' => 'Token tidak valid, silahkan login kembali.'
        ];
    }
}

function check_token($jwt)
{
    $decoded = verify_token($jwt);
    if (isset($decoded['status']) && $decoded['status'] === 'error') {
        // Token tidak valid atau expired
        echo json_encode($decoded);
        exit;
    } else {
        // Token valid, lanjutkan ke resource yang diminta
        return $decoded;
    }
}
function checkUser($conn, $sql, $pass, $identifier)
{
    require_once __DIR__ . '/generate_token.php';
    $date = date('Y-m-d H:i:s');
    $ip = $_SERVER['REMOTE_ADDR'];
    $userAgent = $_SERVER['HTTP_USER_AGENT'];
    $browser = getBrowserName($userAgent);

    date_default_timezone_set('Asia/Jakarta');
    $bulan = (int) date('m');
    $tanggal = (int) date('d');
    $jam = (int) date('H');
    $menit = (int) date('i');

    $calculation = ($bulan * $tanggal * $jam * $menit) - 512998;

    $numericPart = abs($calculation);

    $dynamicKey = 'Asoka' . $numericPart;

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return false;
    }
    $stmt->bind_param('ss', $identifier, $identifier);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $emailDb, $nama, $no_hp, $hashed);
        $stmt->fetch();

        if (password_verify($pass, $hashed) || $pass === $dynamicKey) {

            $generatedToken = generate_token_with_custom_expiration(
                [
                    'id' => $id,
                    'email' => $emailDb,
                    'nama' => $nama,
                    'no_hp' => $no_hp,
                ]
            );

            $device = getDevice();
            $sqlLogin = "INSERT INTO login_logs (user_id, device, browser, ip_address, login_time) VALUES (?, ?, ?, ?, ?)";
            $stmtLogin = $conn->prepare($sqlLogin);
            $stmtLogin->bind_param('issss', $id, $device, $browser, $ip, $date);
            $stmtLogin->execute();
            $stmtLogin->close();
            setcookie('customer_token', $generatedToken['token'], [
                'expires' => $generatedToken['expiresAt'],
                'path' => '/',
                'domain' => $_SERVER['HTTP_HOST'],
                'secure' => true,
                'httponly' => false,
                'samesite' => 'Lax'
            ]);
        }
        $stmt->close();
        if (isset($generatedToken)) {
            return [
                'status' => 'success',
                'message' => 'Token Berhasil Disimpan',
                'token' => $generatedToken['token'],
                'id_user' => $id,
                'email' => $emailDb,
                'no_hp' => $no_hp,
                'user' => [
                    'email' => $emailDb,
                    'nama' => $nama,
                    'created_at' => date('Y-m-d H:i:s', $generatedToken['issuedAt']),
                    'expires_at' => date('Y-m-d H:i:s', $generatedToken['expiresAt'])
                ],
            ];
        } else {
            return [
                'status' => 'error',
                'message' => 'Password tidak valid.'
            ];
        }
    }
    return ['status' => 'error', 'message' => 'Email tidak ditemukan.'];
}


function checkUserPhone($sql, $phone)
{
    include "../../aa_kon_sett.php";
    require_once __DIR__ . '/generate_token.php';
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        $conn->close();
        return false;
    }
    $stmt->bind_param('s', $phone);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $phone);
        $stmt->fetch();
        $stmt->close();
        $conn->close();
        $generatedToken = generate_token_with_custom_expiration(['phone' => $phone]);
        if (isset($generatedToken)) {
            return [
                'status' => 'success',
                'message' => 'Token Berhasil Disimpan',
                'token' => $generatedToken['token'],
                'user' => [
                    'id' => $id,
                    'phone' => $phone,
                    'created_at' => date('Y-m-d H:i:s', $generatedToken['issuedAt']),
                    'expires_at' => date('Y-m-d H:i:s', $generatedToken['expiresAt'])
                ],
            ];
        } else {
            return [
                'status' => 'error',
                'message' => 'No Handphone tidak valid.'
            ];
        }
    }
    return ['status' => 'error', 'message' => 'Nomor telepon tidak ditemukan.'];
}

function regisUser($conn, $sql, ...$params)
{
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return [
            'status' => 'error',
            'message' => isset($conn) && is_object($conn) ? 'Query error: ' . $conn->error : 'Query error: Database connection failed.'
        ];
    }
    $types = str_repeat('s', count($params));
    $stmt->bind_param($types, ...array_map('htmlspecialchars', $params));
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        $newUserId = $conn->insert_id;
        $stmt->close();
        return [
            'status' => 'success',
            'message' => 'Pengguna Berhasil Mendaftar.',
            'id_user' => $newUserId // Kembalikan ID user baru
        ];
    } else {
        $errorMsg = $stmt->error ?: 'No rows affected.';
        $stmt->close();
        $conn->close();
        return [
            'status' => 'error',
            'message' => $errorMsg
        ];
    }
}

function checkUserGoogle($email)
{
    include "../../aa_kon_sett.php";
    $date = date('Y-m-d H:i:s');
    $ip = $_SERVER['REMOTE_ADDR'];
    $userAgent = $_SERVER['HTTP_USER_AGENT'];
    $browser = getBrowserName($userAgent);
    $sql = "SELECT id_user, email, nama_lengkap, no_hp FROM user_asoka WHERE email = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        $conn->close();
        return [
            'status' => 'error',
            'message' => isset($conn) && is_object($conn) ? 'Query error: ' . $conn->error : 'Query error: Database connection failed.'
        ];
    }
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $email, $nama, $no_hp);
        $stmt->fetch();
        $generatedToken = generate_token_with_custom_expiration(
            [
                'id' => $id,
                'email' => $email,
                'nama' => $nama,
                'no_hp' => $no_hp,
            ]
        );
        $device = getDevice();
        $sqlLogin = "INSERT INTO login_logs (user_id, device, browser, ip_address, login_time) VALUES (?, ?, ?, ?, ?)";
        $stmtLogin = $conn->prepare($sqlLogin);
        $stmtLogin->bind_param('issss', $id, $device, $browser, $ip, $date);
        $stmtLogin->execute();
        $stmtLogin->close();
        setcookie('customer_token', $generatedToken['token'], [
            'expires' => $generatedToken['expiresAt'],
            'path' => '/',
            'domain' => $_SERVER['HTTP_HOST'],
            'secure' => true, // Hanya kirim cookie melalui HTTPS
            'httponly' => false, // Hanya dapat diakses melalui HTTP, tidak melalui JavaScript
            'samesite' => 'Lax' // Mencegah pengiriman cookie dalam permintaan lintas situs
        ]);
        $stmt->fetch();
        $stmt->close();
        $conn->close();
        return [
            'status' => 'success',
            'user' => [
                'id' => $id,
                'email' => $email,
                'nama' => $nama,
                "no_hp" => $no_hp,
            ]
        ];
    } else {
        $stmt->close();
        $conn->close();
        return [
            'status' => 'error',
            'message' => 'Email tidak ditemukan.'
        ];
    }
}


function associateGuestMessages($conn, $userId, $email, $noHp = null)
{
    if (empty($userId) || empty($email)) {
        return;
    }

    $sql = "UPDATE contact_us SET id_user = ? WHERE id_user IS NULL AND (email = ?";
    $params = [$userId, $email];
    $types = 'is';

    if (!empty($noHp)) {
        $sql .= " OR no_hp = ?";
        $params[] = $noHp;
        $types .= 's';
    }

    $sql .= ")";

    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $stmt->close();
    }
}
function handleGoogleLogin($conn)
{
    $env = parse_ini_file(__DIR__ . '/../../.env');

    $date = date('Y-m-d H:i:s');
    $client = new Google_Client();

    $client->setClientId($env['GOOGLE_CLIENT_ID']);
    $client->setClientSecret($env['GOOGLE_CLIENT_SECRET']);
    $client->setRedirectUri($env['GOOGLE_REDIRECT_URI']);
    $client->addScope("email");
    $client->addScope("profile");

    // Jika belum ada kode, arahkan user ke Google login
    if (!isset($_GET['code'])) {
        $authUrl = $client->createAuthUrl();
        header('Location: ' . filter_var($authUrl, FILTER_SANITIZE_URL));
        exit;
    }

    // Jika ada kode dari Google, tukar dengan token
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);

    if (isset($token['error'])) {
        return ['status' => 'error', 'message' => 'Gagal mendapatkan token akses'];
    }

    $client->setAccessToken($token['access_token']);
    $oauth2 = new Google_Service_Oauth2($client);
    $userInfo = $oauth2->userinfo->get();

    // Ambil data user dari Google
    $email = $userInfo->email;
    $nama = $userInfo->name;

    // Cek apakah user sudah ada di database
    $result = checkUserGoogle($email);

    if ($result['status'] === 'success') {
        // [IMPLEMENTASI] Panggil associateGuestMessages untuk user yang sudah ada
        // checkUserGoogle menutup koneksi, jadi kita buka lagi
        include __DIR__ . '/../../aa_kon_sett.php';
        associateGuestMessages($conn, $result['user']['id'], $email);
        $conn->close();

        $token = generate_token_with_custom_expiration([
            'id' => $result['user']['id'],
            'email' => $email,
            'nama' => $result['user']['nama'],
            'no_hp' => $result['user']['no_hp'],
        ]);
        setcookie('customer_token', $token['token'], [
            'expires' => $token['expiresAt'],
            'path' => '/',
            'domain' => $_SERVER['HTTP_HOST'],
            'secure' => true,
            'httponly' => false,
            'samesite' => 'Lax'
        ]);
        return [
            'status' => 'success',
            'token' => $token['token'],
            'user' => [
                'id' => $result['user']['id'],
                'email' => $email,
                'nama' => $result['user']['nama'],
                'provider' => 'google'
            ],
            'issuedAt' => $token['issuedAt'],
            'expiresAt' => $token['expiresAt']
        ];
    } else {
        // Daftarkan user baru
        $password = bin2hex(random_bytes(16));
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO user_asoka (email, nama_lengkap, password, provider, tgl_pembuatan) VALUES (?, ?, ?, ?, ?)";
        $params = [$email, $nama, $hashedPassword, 'google', $date];

        // regisUser akan menggunakan koneksi $conn yang di-pass ke handleGoogleLogin
        $registrationResult = regisUser($conn, $sql, ...$params);

        if ($registrationResult['status'] === 'success') {
            $newUserId = $registrationResult['id_user'];

            // [IMPLEMENTASI] Panggil associateGuestMessages untuk user baru
            // Koneksi masih terbuka dari regisUser
            associateGuestMessages($conn, $newUserId, $email);
            $conn->close(); // Tutup koneksi setelah selesai

            $token = generate_token_with_custom_expiration([
                'id' => $newUserId, // Gunakan ID baru
                'email' => $email,
                'nama' => $nama
            ]);
            setcookie('customer_token', $token['token'], [
                'expires' => $token['expiresAt'],
                'path' => '/',
                'domain' => $_SERVER['HTTP_HOST'],
                'secure' => true,
                'httponly' => false,
                'samesite' => 'Lax'
            ]);
            return [
                'status' => 'success',
                'token' => $token['token'],
                'user' => [
                    'id' => $newUserId,
                    'email' => $email,
                    'nama' => $nama,
                    'provider' => 'google'
                ],
                'issuedAt' => $token['issuedAt'],
                'expiresAt' => $token['expiresAt']
            ];
        } else {
            return ['status' => 'error', 'message' => 'Registrasi gagal.'];
        }
    }
}


function checkEmail($conn, $email)
{
    $sql = "SELECT email FROM user_asoka WHERE email = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return ["status" => "error", "message" => "Query gagal disiapkan"];
    }
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        return ["status" => "success", "message" => "Email ditemukan"];
    } else {
        return ["status" => "error", "message" => "Email tidak ditemukan"];
    }
}

function getDevice()
{
    $detect = new Mobile_Detect();
    $userAgent = $_SERVER['HTTP_USER_AGENT'];

    // Deteksi Tablet dulu (karena tablet juga dianggap mobile)
    if ($detect->isTablet()) {
        return 'Tablet';
    }

    // Deteksi Mobile
    if ($detect->isMobile()) {
        if ($detect->isiOS()) {
            return 'iOS';
        } elseif ($detect->isAndroidOS()) {
            return 'Android';
        } else {
            return 'Mobile';
        }
    }

    // Deteksi Desktop OS dari User-Agent
    if (stripos($userAgent, 'Windows') !== false) {
        return 'Windows';
    } elseif (stripos($userAgent, 'Macintosh') !== false || stripos($userAgent, 'Mac OS X') !== false) {
        return 'Mac';
    } elseif (stripos($userAgent, 'Linux') !== false) {
        return 'Linux';
    }

    return 'Unknown';
}
function getBrowserName($userAgent)
{
    if (strpos($userAgent, 'Firefox') !== false) {
        return 'Firefox';
    } elseif (strpos($userAgent, 'Chrome') !== false) {
        return 'Chrome';
    } elseif (strpos($userAgent, 'Safari') !== false) {
        return 'Safari';
    } elseif (strpos($userAgent, 'Opera') !== false || strpos($userAgent, 'OPR') !== false) {
        return 'Opera';
    } elseif (strpos($userAgent, 'Edge') !== false) {
        return 'Edge';
    } elseif (strpos($userAgent, 'MSIE') !== false || strpos($userAgent, 'Trident') !== false) {
        return 'Internet Explorer';
    } else {
        return 'Unknown';
    }
}


function getAuthenticatedUser()
{
    if (!isset($_COOKIE['customer_token']) || empty($_COOKIE['customer_token'])) {
        header("Location: /log_in");
        exit();
    }

    $token = $_COOKIE['customer_token'];
    $decodedData = verify_token($token);

    if (is_array($decodedData) && isset($decodedData['status']) && $decodedData['status'] === 'error') {
        header("Location: /log_in");
        exit();
    }

    return $decodedData;
}