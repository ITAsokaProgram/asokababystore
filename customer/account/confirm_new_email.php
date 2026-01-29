<?php
// Sesuaikan path ke koneksi database Anda
require_once __DIR__ . '/../../aa_kon_sett.php';

$token = $_GET['token'] ?? '';
$email_baru_raw = $_GET['email'] ?? '';
$email_baru = urldecode($email_baru_raw); // Decode email dari URL

$error_message = '';
$success_message = '';
$is_token_valid = false;
$user_phone = '';

// Fungsi Validasi Token & Email
function validateRequest($conn, $token, $email) {
    if (!$token) {
        return ['valid' => false, 'message' => 'Token tidak ditemukan.'];
    }
    if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['valid' => false, 'message' => 'Format email baru tidak valid.'];
    }

    // 1. Cek Token
    $stmt = $conn->prepare("SELECT no_hp, kadaluarsa, used FROM reset_token WHERE token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$result) {
        return ['valid' => false, 'message' => 'Link verifikasi tidak valid atau tidak ditemukan.'];
    }

    if ($result['used'] == 1) {
        return ['valid' => false, 'message' => 'Link ini sudah pernah digunakan.'];
    }

    if (new DateTime() > new DateTime($result['kadaluarsa'])) {
        return ['valid' => false, 'message' => 'Link verifikasi sudah kedaluwarsa. Silakan ajukan ulang.'];
    }

    // 2. Cek apakah email baru sudah dipakai orang lain (Double check security)
    $stmtCheck = $conn->prepare("SELECT id_user FROM user_asoka WHERE email = ?");
    $stmtCheck->bind_param("s", $email);
    $stmtCheck->execute();
    if ($stmtCheck->get_result()->num_rows > 0) {
        $stmtCheck->close();
        return ['valid' => false, 'message' => 'Email ' . htmlspecialchars($email) . ' sudah digunakan oleh akun lain.'];
    }
    $stmtCheck->close();

    return ['valid' => true, 'no_hp' => $result['no_hp']];
}

// HANDLER SAAT TOMBOL DIKLIK (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token_form = $_POST['token'] ?? '';
    $email_form = $_POST['email'] ?? '';
    
    $validation = validateRequest($conn, $token_form, $email_form);

    if (!$validation['valid']) {
        $error_message = $validation['message'];
    } else {
        $no_hp = $validation['no_hp'];
        
        $conn->begin_transaction();
        try {
            // 1. Update Email di user_asoka
            $stmtUser = $conn->prepare("UPDATE user_asoka SET email = ? WHERE no_hp = ?");
            $stmtUser->bind_param("ss", $email_form, $no_hp);
            $stmtUser->execute();
            $stmtUser->close();

            // 2. Update Email di customers (jika ada relasi via no_hp)
            // Cek dulu apakah no_hp ada di table customers
            $stmtCustCheck = $conn->prepare("UPDATE customers SET email = ? WHERE kd_cust = ?"); // Asumsi kd_cust = no_hp
            $stmtCustCheck->bind_param("ss", $email_form, $no_hp);
            $stmtCustCheck->execute();
            $stmtCustCheck->close();

            // 3. Matikan Token
            $stmtToken = $conn->prepare("UPDATE reset_token SET used = 1 WHERE token = ?");
            $stmtToken->bind_param("s", $token_form);
            $stmtToken->execute();
            $stmtToken->close();

            $conn->commit();
            $success_message = 'Email berhasil diperbarui menjadi ' . htmlspecialchars($email_form) . '. Silakan login kembali.';

        } catch (Exception $e) {
            $conn->rollback();
            $error_message = 'Terjadi kesalahan sistem: ' . $e->getMessage();
        }
    }
    $is_token_valid = empty($success_message); // Sembunyikan form jika sukses
} 
// HANDLER SAAT HALAMAN DIBUKA (GET)
else {
    $validation = validateRequest($conn, $token, $email_baru);
    if ($validation['valid']) {
        $is_token_valid = true;
        $user_phone = $validation['no_hp']; // Opsional: untuk ditampilkan (disensor)
    } else {
        $error_message = $validation['message'];
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfirmasi Ganti Email - Asoka Baby Store</title>
    <link rel="stylesheet" href="/src/output2.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <style>
        * { font-family: 'Poppins', sans-serif; }
    </style>
</head>

<body class="bg-gray-50 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md bg-white rounded-2xl shadow-xl p-8 space-y-6 border border-gray-100">

        <div class="text-center">
            <a href="/">
                <img src="/public/images/LOGO Asoka k.png" class="w-20 mx-auto mb-4 hover:scale-105 transition-transform" alt="Logo Asoka">
            </a>
            <h2 class="text-2xl font-bold text-gray-800">Konfirmasi Email Baru</h2>
            <p class="text-sm text-gray-500 mt-2">Verifikasi perubahan email akun Anda</p>
        </div>

        <?php if (!empty($success_message)): ?>
            <div class="text-center space-y-4">
                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto text-green-500 mb-4">
                    <i class="fas fa-check-circle text-3xl"></i>
                </div>
                <div class="p-4 bg-green-50 text-green-800 border border-green-200 rounded-xl">
                    <p class="font-semibold text-lg">Berhasil!</p>
                    <p class="text-sm mt-1"><?php echo $success_message; ?></p>
                </div>
                <div class="pt-4">
                    <p class="text-gray-500 text-sm mb-2">Mengalihkan ke halaman login...</p>
                    <div class="w-full bg-gray-200 rounded-full h-1.5 dark:bg-gray-700">
                        <div class="bg-green-500 h-1.5 rounded-full transition-all duration-[3000ms] w-0" id="progress-bar"></div>
                    </div>
                </div>
            </div>
             <script>
                setTimeout(() => { document.getElementById('progress-bar').style.width = '100%'; }, 100);
                setTimeout(() => { window.location.href = '/log_in.php'; }, 3000);
            </script>

        <?php elseif (!$is_token_valid): ?>
            <div class="text-center space-y-4">
                <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto text-red-500 mb-4">
                    <i class="fas fa-exclamation-triangle text-3xl"></i>
                </div>
                <div class="p-4 bg-red-50 text-red-800 border border-red-200 rounded-xl">
                    <p class="font-bold text-lg">Gagal Memproses</p>
                    <p class="text-sm mt-1"><?php echo htmlspecialchars($error_message); ?></p>
                </div>
                 <div class="pt-4">
                    <a href="/log_in.php" class="inline-flex items-center justify-center w-full px-5 py-3 text-base font-medium text-white transition-colors duration-150 bg-gray-800 border border-transparent rounded-xl hover:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-900">
                        <i class="fas fa-arrow-left mr-2"></i> Kembali ke Login
                    </a>
                </div>
            </div>

        <?php else: ?>
            <?php if (!empty($error_message)): ?>
                <div class="p-3 text-center bg-red-100 text-red-700 rounded-lg text-sm border border-red-200">
                    <i class="fas fa-exclamation-circle mr-1"></i> <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>
            
            <div class="bg-blue-50 p-5 rounded-xl border border-blue-100 text-center">
                <p class="text-sm text-gray-600 mb-1">Anda akan mengubah email menjadi:</p>
                <p class="text-lg font-bold text-blue-700 break-all"><?php echo htmlspecialchars($email_baru); ?></p>
            </div>

            <form method="POST" action="" class="space-y-4">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                <input type="hidden" name="email" value="<?php echo htmlspecialchars($email_baru); ?>">

                <button type="submit" class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 text-white py-4 rounded-xl font-semibold shadow-lg hover:shadow-xl hover:from-blue-700 hover:to-indigo-700 transform hover:-translate-y-0.5 transition-all duration-200 focus:ring-4 focus:ring-blue-300">
                    <i class="fas fa-save mr-2"></i> Konfirmasi Perubahan
                </button>
                
                <div class="text-center mt-4">
                    <a href="/log_in.php" class="text-sm text-gray-500 hover:text-gray-700 transition-colors">
                        Batalkan
                    </a>
                </div>
            </form>
        <?php endif; ?>

    </div>
</body>
</html>