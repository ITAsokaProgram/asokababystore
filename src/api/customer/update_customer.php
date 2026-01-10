<?php

include '../../../aa_kon_sett.php';
header("Content-Type: application/json");
// Baca file ENV
$envFile = "/var/www/endes.env";
$env = parse_ini_file($envFile);

// Simpan ke $_ENV agar lebih aman
$_ENV['ENCRYPTION_KEY'] = $env['ENCRYPTION_KEY'];
$_ENV['ENCRYPTION_IV'] = $env['ENCRYPTION_IV'];

define('ENCRYPTION_KEY', $_ENV['ENCRYPTION_KEY']);
define('ENCRYPTION_IV', $_ENV['ENCRYPTION_IV']);
define('ENCRYPTION_METHOD', 'AES-256-CBC');

// Fungsi enkripsi & dekripsi
function encryptData($data)
{
    return openssl_encrypt($data, ENCRYPTION_METHOD, ENCRYPTION_KEY, 0, ENCRYPTION_IV);
}


// Ambil data dari FormData
$kode_member = $_POST['kode_member'] ?? '';
$nama_lengkap = $_POST['nama_lengkap'] ?? '';
$alamat_ktp = substr($_POST['alamat_ktp'] ?? '', 0, 100);
$provinsi_ktp = $_POST['provinsi'] ?? '';
$kota_ktp = $_POST['kota'] ?? '';
$kec_ktp = $_POST['kec'] ?? '';
$kel_ktp = $_POST['kel'] ?? '';

$alamat_domisili = substr($_POST['alamat_domisili'] ?? '', 0, 100);
$prov_domisili = $_POST['provinsi_domisili'] ?? '';
$kota_domisili = $_POST['kota_domisili'] ?? '';
$kec_domisili = $_POST['kec_domisili'] ?? '';
$kel_domisili = $_POST['kel_domisili'] ?? '';

$nik = encryptData($_POST['nik']) ?? '';
$email = $_POST['email'] ?? '';
$tanggal_lahir = $_POST['tanggal_lahir'] ?? '';
$jenis_kelamin = $_POST['jenis_kelamin'] ?? '';
$jumlah_anak = (int) ($_POST['jumlah_anak'] ?? 0);
$SnK = "checked";
$sql = "UPDATE customers SET 
    nama_cust = ?,
    alamat = ?,
    Prov = ?,
    Kota = ?,
    Kec = ?,
    Kel = ?,
    alamat_ds = ?,
    prov_ds = ?,
    kota_ds = ?,
    kec_ds = ?,
    kel_ds = ?,
    no_ktp_sim = ?,
    no_hp = ?,
    email = ?,
    tgl_lahir = ?,
    jenis_kel = ?,
    juml_anak = ?,
    SnK = ?,
    upd_from_web = 0,                       
    tgl_edit = NOW()
    WHERE kd_cust = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param(
    "ssssssssssssssssiss",
    $nama_lengkap,
    $alamat_ktp,
    $provinsi_ktp,
    $kota_ktp,
    $kec_ktp,
    $kel_ktp,
    $alamat_domisili,
    $prov_domisili,
    $kota_domisili,
    $kec_domisili,
    $kel_domisili,
    $nik,
    $kode_member,
    $email,
    $tanggal_lahir,
    $jenis_kelamin,
    $jumlah_anak,
    $SnK,
    $kode_member
);
if ($stmt->execute()) {
    echo json_encode([
        'success' => $stmt->affected_rows > 0,
        'message' => $stmt->affected_rows > 0 ? 'Data berhasil diupdate' : 'Tidak ada data yang diubah (kd_cust mungkin tidak ditemukan)',
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Query gagal dijalankan: ' . $stmt->error
    ]);
}
$stmt->close();
$conn->close();

