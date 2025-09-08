<?php
include 'aa_kon_sett.php';

session_start();

// Jika pengguna belum login, alihkan ke in_login.php
if (!isset($_SESSION['username'])) {
    header("Location: in_login.php");
    exit;
}

// Mendapatkan data pengguna dari sesi
$nama = $_SESSION['nama'];
$hak = $_SESSION['hak'];
$safe_name = $_SESSION['username'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tentang</title>

    <!-- font awesome cdn link  -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css">

    <!-- Penjelasan: Link ke CSS eksternal dengan cache busting query string -->
    <link rel="stylesheet" href="css/style_in.css?v=<?php echo htmlspecialchars($css_version); ?>">

    <!-- Setting logo pada tab di website Anda / Favicon -->
    <link rel="icon" type="image/png" href="/images/logo1.png">

</head>

<body>

    <header class="header">
        <section class="flex">
            <a href="in_beranda.php" class="logo"><img src="images/logo.png"></a>
            <div class="icons">
                <div id="menu-btn" class="fas fa-bars"></div>
                <div id="search-btn" class="fas fa-search"></div>
                <div id="user-btn" class="fas fa-user"></div>
                <div id="toggle-btn" class="fas fa-sun"></div>
            </div>

            <div class="profile">
                <img src="images/pic-1.jpg" class="image" alt="">
                <h3 class="name"><?php echo htmlspecialchars($nama); ?></h3>
                <p class="role"><?php echo htmlspecialchars($hak); ?></p>
                <a href="in_beranda.php" class="btn">Lihat Profil</a>
                <div class="flex-btn">
                    <a href="in_logout.php" class="option-btn">Keluar</a>
                </div>
            </div>
        </section>
    </header>

    <div class="side-bar">
        <div id="close-btn">
            <i class="fas fa-times"></i>
        </div>

        <div class="profile">
            <img src="images/pic-1.jpg" class="image" alt="">
            <h3 class="name"><?php echo htmlspecialchars($nama); ?></h3>
            <p class="role"><?php echo htmlspecialchars($hak); ?></p>
            <a href="in_beranda.php" class="btn">Lihat Profil</a>
        </div>

        <nav class="navbar">
            <a href="in_beranda.php"><i class="fas fa-home"></i><span>Beranda</span></a>
            <a href="in_laporan.php"><i class="fa fa-book"></i><span>Laporan</span></a>
            <a href="in_new_user.php"><i class="fa fa-users"></i><span>Anggota</span></a>
            <a href="#"><i class="fas fa-headset"></i><span>Kontak</span></a>
            <a href="in_about.php"><i class="fas fa-question"></i><span> Tentang</span></a>
        </nav>
    </div>

    <section class="about">

        <div class="row">

            <div class="image">
                <img src="images/about-img.jpeg" alt="">
            </div>

            <div class="content">
                <h3>ASOKA BABY STORE</h3>
                <p>Raih Kebahagiaan dan Kenyamanan si Kecil dengan Perlengkapan Terbaik dari Asoka Baby Store, untuk
                    Usia 0-8 Tahun.</p>
                <a href="in_beranda" class="inline-btn">Beranda</a>
            </div>

        </div>

        <div class="box-container">
            <div class="box">
                <i class="fa fa-building"></i>
                <div>
                    <h3>+10k</h3>
                    <p>Total Cabang</p>
                </div>
            </div>

            <div class="box">
                <i class="fa fa-user-plus"></i>
                <div>
                    <h3>+40k</h3>
                    <p>Total Karyawan</p>
                </div>
            </div>

            <div class="box">
                <i class="fa fa-building"></i>
                <div>
                    <h3>+10k</h3>
                    <p>Total Cabang</p>
                </div>
            </div>

            <div class="box">
                <i class="fa fa-user-plus"></i>
                <div>
                    <h3>+40k</h3>
                    <p>Total Karyawan</p>
                </div>
            </div>
        </div>
    </section>


    <footer class="footer"> &copy; copyright @2023 by <span>asoka baby store</span> </footer>

    <!-- custom js file link  -->
    <script src="js/script_in.js"></script>


</body>

</html>