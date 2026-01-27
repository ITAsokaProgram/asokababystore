<?php
session_start();
include '../../../aa_kon_sett.php';
header('Content-Type: application/json');

$id = $_GET['id'] ?? null;

if (!$id) {
    echo json_encode(['error' => 'ID tidak ditemukan']);
    exit;
}

$stmt = $conn->prepare("SELECT kode, nama, email, no_telpon, wilayah FROM user_supplier WHERE kode = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

if ($data) {
    echo json_encode($data);
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Data tidak ditemukan']);
}
$stmt->close();
$conn->close();
?>