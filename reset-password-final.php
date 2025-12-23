<?php
require_once __DIR__ . '/aa_kon_sett.php'; 

$token = $_GET['token'] ?? null;
$error_message = '';
$success_message = '';
$is_token_valid = false;

function validateToken($conn, $token) {
    if (!$token) {
        return ['valid' => false, 'message' => 'Token tidak ditemukan atau link tidak lengkap.'];
    }

    $stmt = $conn->prepare("SELECT no_hp, kadaluarsa, used FROM reset_token WHERE token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$result) {
        return ['valid' => false, 'message' => 'Link reset tidak valid atau salah.'];
    }

    if ($result['used'] == 1) {
        return ['valid' => false, 'message' => 'Link ini sudah pernah digunakan untuk mereset password.'];
    }

    if (new DateTime() > new DateTime($result['kadaluarsa'])) {
        return ['valid' => false, 'message' => 'Link reset Anda sudah kedaluwarsa. Silakan ajukan permintaan baru.'];
    }

    return ['valid' => true, 'no_hp' => $result['no_hp']];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token_from_form = $_POST['token'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    $token_validation = validateToken($conn, $token_from_form);

    if (!$token_validation['valid']) {
        $error_message = $token_validation['message'];
    } elseif (empty($new_password) || empty($confirm_password)) {
        $error_message = 'Password baru dan konfirmasi tidak boleh kosong.';
    } elseif (strlen($new_password) < 8) {
        $error_message = 'Password minimal harus 8 karakter.';
    } elseif (!preg_match('/[A-Z]/', $new_password) || !preg_match('/[0-9]/', $new_password)) {
        $error_message = 'Password harus mengandung setidaknya satu huruf besar dan satu angka.';
    } elseif ($new_password !== $confirm_password) {
        $error_message = 'Password baru dan konfirmasi tidak cocok.';
    } else {
        try {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $no_hp = $token_validation['no_hp'];

            $stmt_update_user = $conn->prepare("UPDATE user_asoka SET password = ? WHERE no_hp = ?");
            $stmt_update_user->bind_param("ss", $hashed_password, $no_hp);
            $stmt_update_user->execute();

            $stmt_update_token = $conn->prepare("UPDATE reset_token SET used = 1 WHERE token = ?");
            $stmt_update_token->bind_param("s", $token_from_form);
            $stmt_update_token->execute();

            $success_message = 'Password Anda berhasil diubah! Anda akan diarahkan ke halaman login.';

        } catch (Exception $e) {
            $error_message = 'Terjadi kesalahan pada server. Silakan coba lagi nanti.';
        }
    }
    $is_token_valid = empty($success_message); 
    $token = $token_from_form;
} 
else {
    $token_validation = validateToken($conn, $token);
    if ($token_validation['valid']) {
        $is_token_valid = true;
    } else {
        $error_message = $token_validation['message'];
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Asoka Baby Store</title>
    <link rel="stylesheet" href="/src/output2.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <style>
        * { font-family: 'Poppins', sans-serif; }
    </style>
</head>

<body class="bg-gray-50 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md bg-white rounded-lg shadow-md p-6 space-y-6">

        <div class="text-center">
            <a href="/"><img src="/public/images/LOGO Asoka k.png" class="w-16 mx-auto mb-2" alt="Logo Asoka"></a>
            <h2 class="text-2xl font-bold text-center text-gray-800">Reset Password</h2>
        </div>

        <?php if (!empty($success_message)): ?>
            <div class="p-4 bg-green-100 text-green-800 border-l-4 border-green-500 rounded-lg" role="alert">
                <p class="font-bold">Berhasil!</p>
                <p><?php echo htmlspecialchars($success_message); ?></p>
            </div>
             <script>
                setTimeout(() => {
                    window.location.href = '/log_in.php';
                }, 3000);
            </script>
        <?php elseif (!$is_token_valid): ?>
            <div class="p-4 bg-red-100 text-red-800 border-l-4 border-red-500 rounded-lg" role="alert">
                <p class="font-bold">Link Tidak Valid</p>
                <p><?php echo htmlspecialchars($error_message); ?></p>
            </div>
             <div class="text-center">
                <a href="/log_in.php" class="text-sm font-medium text-blue-600 hover:underline">Kembali ke Halaman Login</a>
            </div>
        <?php else: ?>
            <?php if (!empty($error_message)): ?>
                <div class="p-3 text-center bg-red-100 text-red-700 rounded-lg text-sm" role="alert">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>
            
            <form id="resetPasswordForm" method="POST" action="" class="space-y-4">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">

                <div>
                    <label for="newPassword" class="block text-sm font-medium text-gray-700 mb-1">Password Baru</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 right-0 flex items-center px-3 cursor-pointer" onclick="togglePassword('newPassword', this)">
                            <i class="fas fa-eye text-gray-500"></i>
                        </span>
                        <input type="password" id="newPassword" name="new_password" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                    </div>
                    <p class="mt-1 text-xs text-gray-500">Minimal 8 karakter, dengan huruf besar & angka.</p>
                </div>

                <div>
                    <label for="confirmPassword" class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Password Baru</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 right-0 flex items-center px-3 cursor-pointer" onclick="togglePassword('confirmPassword', this)">
                            <i class="fas fa-eye text-gray-500"></i>
                        </span>
                        <input type="password" id="confirmPassword" name="confirm_password" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                    </div>
                </div>

                <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 transition-colors">
                    Ubah Password
                </button>
            </form>
        <?php endif; ?>
    </div>

    <script>
        function togglePassword(inputId, button) {
            const input = document.getElementById(inputId);
            const icon = button.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>