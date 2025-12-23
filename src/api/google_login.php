<?php
require_once "../../aa_kon_sett.php";
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
session_start();
// Ambil data JSON dari body request
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Jika tidak ada data yang dikirim, kirim error
if (!$data) {
    echo json_encode(['status' => 'error', 'message' => 'No data received']);
    exit();
}

// Pastikan semua parameter tersedia
if (!isset($data['access_token'], $data['email'], $data['gprovider'], $data['name'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid data']);
    exit();
}


// Simpan data ke SESSION
$_SESSION['access_token'] = $data['access_token'];
$_SESSION['email'] = $data['email'];
$_SESSION['gprovider'] = $data['gprovider'];
$_SESSION['nama'] = $data['name'];  // Pastikan nama juga disimpan

// Tutup session agar bisa digunakan di halaman lain
session_write_close();

$access_token = $data['access_token'];
$email = $data['email'];
$provider = $data['gprovider'];
$name = $data['name'];
$kode_toko = '9999';
$kode = random_int(90000, 100000);

// Cek apakah email sudah ada di database
$checkEmailQuery = "SELECT COUNT(*) FROM user_account WHERE email = ?";
$stmtCheck = $conn->prepare($checkEmailQuery);
$stmtCheck->bind_param("s", $email);
$stmtCheck->execute();
$stmtCheck->bind_result($emailCount);
$stmtCheck->fetch();
$stmtCheck->close();

if ($emailCount > 0) {
    echo json_encode(["message" => "Email already exists."]);
} else {
    // Masukkan user baru ke database
    $sql = "INSERT INTO user_account(nama, kode_toko, kode, email, provider_token) VALUES(?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssiss", $name, $kode_toko, $kode, $email, $provider);

    if ($stmt->execute()) {
        echo json_encode(["message" => "Data berhasil ditambahkan"]);
    } else {
        echo json_encode(["message" => "Error executing query.", "error" => $stmt->error]);
    }

    $stmt->close();
}

$conn->close();

// Kirim respons sukses
echo json_encode(['status' => 'success', 'message' => 'Session saved']);
?>