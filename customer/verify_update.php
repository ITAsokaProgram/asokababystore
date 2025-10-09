<?php
require_once __DIR__ . "/../aa_kon_sett.php";

$token = $_GET['token'] ?? '';

if (empty($token)) {
    die("Token tidak valid atau tidak ditemukan.");
}

try {
    $conn->begin_transaction();

    // Cari request verifikasi berdasarkan token konfirmasi
    $sql = "SELECT id, id_user, nomor_hp_baru FROM verifikasi_nomor_hp WHERE token_konfirmasi = ? AND status = 'menunggu_konfirmasi' AND kedaluwarsa_pada > NOW()";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $data = $result->fetch_assoc();
        $verifikasiId = $data['id'];
        $userId = $data['id_user'];
        $nomorHpBaru = $data['nomor_hp_baru'];

        // 1. Update nomor HP di tabel user_asoka
        $stmtUpdateUser = $conn->prepare("UPDATE user_asoka SET no_hp = ? WHERE id_user = ?");
        $stmtUpdateUser->bind_param("si", $nomorHpBaru, $userId);
        $stmtUpdateUser->execute();
        
        // 2. Update status verifikasi menjadi 'selesai'
        $stmtUpdateVerifikasi = $conn->prepare("UPDATE verifikasi_nomor_hp SET status = 'selesai' WHERE id = ?");
        $stmtUpdateVerifikasi->bind_param("i", $verifikasiId);
        $stmtUpdateVerifikasi->execute();

        $conn->commit();
        
        $pesanTampilan = "Selamat! Nomor HP Anda telah berhasil diperbarui menjadi <b>{$nomorHpBaru}</b>.";
        $statusTampilan = "sukses";

    } else {
        // Jika token tidak valid atau kadaluwarsa
        $conn->rollback();
        $pesanTampilan = "Link verifikasi tidak valid, sudah digunakan, atau telah kadaluwarsa. Silakan ulangi proses penggantian nomor HP dari awal.";
        $statusTampilan = "gagal";
    }

} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollback();
    }
    $pesanTampilan = "Terjadi kesalahan pada server. Silakan coba lagi nanti.";
    $statusTampilan = "gagal";
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Status Verifikasi Nomor HP</title>
    <link rel="stylesheet" href="/src/output2.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full p-8 text-center">
        <?php if ($statusTampilan === 'sukses'): ?>
            <div class="w-20 h-20 bg-green-100 text-green-500 rounded-full flex items-center justify-center mx-auto mb-6">
                <i class="fas fa-check-circle text-4xl"></i>
            </div>
            <h1 class="text-2xl font-bold text-gray-800 mb-2">Verifikasi Berhasil</h1>
        <?php else: ?>
            <div class="w-20 h-20 bg-red-100 text-red-500 rounded-full flex items-center justify-center mx-auto mb-6">
                <i class="fas fa-times-circle text-4xl"></i>
            </div>
            <h1 class="text-2xl font-bold text-gray-800 mb-2">Verifikasi Gagal</h1>
        <?php endif; ?>
        
        <p class="text-gray-600 mb-8"><?php echo $pesanTampilan; ?></p>
        <a href="/src/fitur/pubs/user/profile/view" class="px-8 py-3 bg-pink-500 text-white font-semibold rounded-lg shadow-md hover:bg-pink-600">
            Kembali ke Profil
        </a>
    </div>
</body>
</html>