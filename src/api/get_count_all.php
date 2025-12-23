<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../../aa_kon_sett.php';
header("Content-Type: application/json");

// Inisialisasi variabel
$total_customers = 0;
$total_cabang = 0;

// Query jumlah pelanggan
$stmt1 = $conn->prepare("SELECT COUNT(*) FROM customers");
if ($stmt1) {
    $stmt1->execute();
    $stmt1->bind_result($total_customers);
    $stmt1->fetch();
    $stmt1->close();
}

// Query jumlah cabang
$stmt2 = $conn->prepare("SELECT COUNT(*) FROM kode_store");
if ($stmt2) {
    $stmt2->execute();
    $stmt2->bind_result($total_cabang);
    $stmt2->fetch();
    $stmt2->close();
}

$conn->close();

// Kirim hasil dalam format JSON
echo json_encode([
    'message' => 'success',
    'data' => [
        'customers' => $total_customers,
        'cabang' => $total_cabang
    ]
]);
?>
