<?php
include '../../../aa_kon_sett.php';

header('Content-Type: application/json');

$result = $conn->query("SELECT MAX(kode) AS max_kode FROM user_account WHERE kode LIKE '9990%'");
$row = $result->fetch_assoc();
$max_kode = $row['max_kode'];
$next_kode = $max_kode ? $max_kode + 1 : 9990001;

echo json_encode(['next_kode' => $next_kode]);
$conn->close();