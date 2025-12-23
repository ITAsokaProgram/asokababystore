<?php
include '../../aa_kon_sett.php';
session_start();
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
$isLoggin = false;

if (isset($_SESSION['access_token'])) {
  $isLoggin = true;
  $sql = "SELECT nama,email,hak FROM user_account WHERE email = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("s", $_SESSION['email']);
} else if (isset($_SESSION['nama']) && isset($_SESSION['hak']) && isset($_SESSION['username'])) {
  $isLoggin = true;
  $sql = "SELECT nama,hak,inisial,email FROM user_account WHERE inisial = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("s", $_SESSION["username"]);
}

if (!$isLoggin) {
  http_response_code(401);
  echo json_encode(['error' => 'Silahkan login terlebih dahulu untuk mengakses halaman ini!!!']);
  header("Location: /in_login");
  exit;
}

try {
  $stmt->execute();
  $result = $stmt->get_result();
  $row = $result->fetch_assoc();
  $stmt->close();
  $conn->close();
} catch (Exception $e) {
  echo json_encode(['error' => 'message' . $e->getMessage()]);
}