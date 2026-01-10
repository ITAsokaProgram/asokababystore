<?php
$is_dev = false;
$css_version = $is_dev ? time() : filemtime('css/style.css');
$error_code = http_response_code();
if ($error_code == 403) {
    $error_message = "Dilarang masuk sembarangan, takut nanti kesasar sendirian!";
} else {
    $error_code = 404;
    $error_message = "Salah lagi, salah lagi, alamat dicari tak kunjung jadi!";
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css?v=<?php echo htmlspecialchars($css_version, ENT_QUOTES, 'UTF-8'); ?>">
    <link rel="icon" type="image/png" href="/images/logo1.png">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--light-bg);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            color: var(--black);
        }

        .error-container {
            background: var(--light-color);
            padding: 2rem;
            border-radius: 10px;
            box-shadow: var(--box-shadow);
            text-align: center;
            max-width: 500px;
            animation: fadeIn 0.8s ease-in-out;
            color: var(--white);
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: scale(0.9);
            }

            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        .error-icon {
            font-size: 100px;
            margin-bottom: 1rem;
            color: var(--orange);
        }

        .error-message {
            font-size: 22px;
            font-weight: bold;
            margin-bottom: 2rem;
            line-height: 1.5;
        }

        .back-button {
            background-color: var(--white);
            color: var(--pink);
            padding: 12px 25px;
            border: none;
            border-radius: 50px;
            text-decoration: none;
            font-weight: bold;
            display: inline-block;
            transition: all 0.3s ease-in-out;
            font-size: 15px;
        }

        .back-button:hover {
            background-color: var(--pink);
            color: var(--white);
        }

        @media (max-width: 768px) {
            .error-container {
                padding: 1.5rem;
                max-width: 90%;
            }

            .error-icon {
                font-size: 80px;
            }

            .error-message {
                font-size: 18px;
            }
        }

        @media (max-width: 450px) {
            .error-container {
                padding: 1rem;
                max-width: 95%;
            }

            .error-icon {
                font-size: 60px;
            }

            .error-message {
                font-size: 16px;
            }

            .back-button {
                padding: 10px 20px;
            }
        }
    </style>
</head>

<body>
    <div class="error-container">
        <div class="error-icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
        <h1 style="font-size: 36px; font-weight: 700;">Oops! Error</h1>
        <div class="error-message">
            <?php echo $error_message; ?>
        </div>
        <a href="/index.php" class="back-button">Kembali ke Beranda</a>
    </div>
</body>

</html>