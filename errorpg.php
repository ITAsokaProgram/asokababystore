<?php
session_start();
require_once 'aa_kon_sett.php';

// Jika tidak ada error di session, redirect ke halaman utama
if (!isset($_SESSION['error'])) {
    header('Location: index.php');
    exit;
}

$error = $_SESSION['error'];
unset($_SESSION['error']); // Hapus error setelah ditampilkan
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Setting logo pada tab di website Anda / Favicon -->
    <link rel="icon" type="image/png" href="/images/logo1.png">
    <title>Error Page</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .error-container {
            background-color: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            text-align: center;
            max-width: 500px;
        }

        .error-icon {
            color: #dc3545;
            font-size: 48px;
            margin-bottom: 1rem;
        }

        .error-message {
            color: #721c24;
            margin-bottom: 1rem;
        }

        .back-button {
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            text-decoration: none;
            display: inline-block;
            margin-top: 1rem;
        }

        .back-button:hover {
            background-color: #0056b3;
        }
    </style>
</head>

<body>
    <div class="error-container">
        <div class="error-icon">⚠️</div>
        <h1>Oops! An Error Occurred</h1>
        <div class="error-message">
            <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
        </div>
        <a href="index.php" class="back-button">Back to Homepage</a>
    </div>

    <script>
        window.addEventListener('online', function () {
            setTimeout(function () {
                window.location.href = 'index.php';
            }, 15000); // Redirect setelah 15 detik
        });

        if (navigator.onLine) {
            setTimeout(function () {
                window.location.href = 'index.php';
            }, 15000);
        }

        window.addEventListener('load', function () {
            if (performance.navigation.type === 1) { // 1 = Reload (F5 atau tombol refresh)
                window.location.href = 'index.php';
            }
        });
    </script>
</body>

</html>