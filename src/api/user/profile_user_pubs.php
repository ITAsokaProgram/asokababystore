<?php

include "../../../aa_kon_sett.php";
include "../../auth/middleware_login.php";
header("Content-Type:application/json");
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$cookie = $_COOKIE['token'];
$result = verify_token($cookie);

if (!$result) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Token tidak valid']);
    exit;
}

$sqlCheckKode = "SELECT 
  ua.no_hp as phone_number,
  c.nama_cust as nama_lengkap,
  c.Jenis_kel as gender,
  c.alamat as alamat,
  c.Prov as provinsi,
  c.Kota as kota,
  c.Kec as kecamatan,
  c.Kel as kelurahan,
  c.juml_anak as anak,
  c.tgl_lahir as tanggal_lahir,
  c.email as email,
  c.alamat_ds as domisili_alamat,
  c.Prov_ds as domisili_prov,
  c.Kota_ds as domisili_kota,
  c.Kec_ds as domisili_kecamatan,
  c.Kel_ds as domisili_kelurahan,
  c.SnK as syarat_ketentuan,
  c.upd_from_web as updated,
  CASE 
    WHEN c.kd_cust IS NOT NULL THEN 'member'
    ELSE 'non-member'
  END AS status_member
FROM user_asoka ua
LEFT JOIN customers c ON ua.no_hp = c.kd_cust
WHERE ua.email = ?";

$stmt = $conn->prepare($sqlCheckKode);
$stmt->bind_param("s", $result->email);
$stmt->execute();

$fetch = $stmt->get_result();
$data = $fetch->fetch_assoc();

if ($data) {
    http_response_code(200);
    echo json_encode(['status' => 'success', 'message' => 'Get Profile Success', 'data' => $data]);
} else {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Failed Get Profile', 'data' => $data]);
}

$stmt->close();
$conn->close();
