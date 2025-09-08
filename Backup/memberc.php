<?php
@include 'aa_kon_sett.php';

session_start();

if(isset($_POST['ajax']) && $_POST['ajax'] == '1'){
    $response = ['status' => false, 'message' => ''];

    $filter_kd_cust = filter_var($_POST['memberNumber'], FILTER_SANITIZE_STRING);
    $kd_cust = mysqli_real_escape_string($conn, $filter_kd_cust);

    // Modified query to calculate total points
    $select_customers = mysqli_query($conn, "SELECT a.Update,a.kd_cust, a.nama_cust, 
        (SUM(IFNULL(b.total_point_1,0)) + SUM(IFNULL(c.total_point,0)) + SUM(IFNULL(d.total_jum_point,0))) - SUM(IFNULL(e.total_jum_point_minus,0)) AS total_point 
    FROM customers a
    LEFT JOIN (SELECT kd_cust, SUM(point_1) AS total_point_1 FROM point_kasir WHERE kd_cust='$kd_cust' GROUP BY kd_cust) b ON a.kd_cust = b.kd_cust
    LEFT JOIN (SELECT kd_cust, SUM(`point`) AS total_point FROM t_pembayaran WHERE kd_cust='$kd_cust' GROUP BY kd_cust) c ON a.kd_cust = c.kd_cust
    LEFT JOIN (SELECT kd_cust, SUM(jum_point) AS total_jum_point FROM point_manual WHERE kd_cust='$kd_cust' GROUP BY kd_cust) d ON a.kd_cust = d.kd_cust
    LEFT JOIN (SELECT kd_cust, SUM(jum_point) AS total_jum_point_minus FROM point_trans WHERE kd_cust='$kd_cust' GROUP BY kd_cust) e ON a.kd_cust = e.kd_cust 
    WHERE a.kd_cust ='$kd_cust'
    GROUP BY a.kd_cust, a.nama_cust");

    if($select_customers){
        if(mysqli_num_rows($select_customers) > 0){
            $row = mysqli_fetch_assoc($select_customers);
            $response['status'] = true;
            $response['data'] = [
                'kd_cust' => $row['kd_cust'],
                'nama_cust' => $row['nama_cust'],
                'total_point' => $row['total_point'] // Modified to reflect total points
            ];
        } else {
            $response['message'] = 'Kode Customer tidak ditemukan!';
        }
    } else {
        $response['message'] = 'Query gagal!';
    }

    echo json_encode($response);
    exit;
}

// Cek apakah ada data customer di sesi
if(isset($_SESSION['temp_customer_info'])){
    $temp_customer_info = $_SESSION['temp_customer_info'];
    unset($_SESSION['temp_customer_info']);
} else {
    $temp_customer_info = null;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Member</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css?v=<?php echo $css_version; ?>">

   <!-- Setting logo pada tab di website Anda / Favicon -->
   <link rel="icon" type="image/png" href="/images/logo1.png">
</head>
<body>

<?php
if(isset($message)){
   foreach($message as $message){
      echo '
      <div class="message">
         <span>'.$message.'</span>
         <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
      </div>
      ';
   }
}
?>
   
<section class="form-container">

<form action="" method="post" onsubmit="return submitForm();">
    <h3>Cek Poin Member</h3>
    <?php if(!$temp_customer_info): ?>
        <input type="text" name="memberNumber" class="box" placeholder="Masukkan kode customer Anda" required>
        <div id="errorMessage" style="color: red;"></div> <!-- Tempat untuk pesan error -->
    <?php endif; ?>
   <?php if($temp_customer_info): ?>
    <label><span>Nomor :</span> <input type="text" class="box" value="<?php echo $temp_customer_info['kd_cust']; ?>" disabled></label>
    <label><span>Nama :</span> <input type="text" class="box" value="<?php echo $temp_customer_info['nama_cust']; ?>" disabled></label>
    <label><span>Poin:</span> <input type="text" class="box" value="<?php echo $temp_customer_info['total_point']; ?>" disabled></label>
    <?php endif; ?>
    <input type="button" class="btn" value="Cek Member" onclick="submitForm();">
    <p>Belum memiliki member? <a href="register.php">Daftar</a></p>
</form>
</section>

<script src="js/memberc.js"></script>
</body>
</html>
